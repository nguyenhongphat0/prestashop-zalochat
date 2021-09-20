<?php

/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2021 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Zalochat extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'zalochat';
        $this->tab = 'social_networks';
        $this->version = '1.0.0';
        $this->author = 'nguyenhongphat0';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Chat via Zalo');
        $this->description = $this->l('Floating Zalo chat button on the bottom right of the screen');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        return parent::install() &&
            $this->registerHook('displayFooter');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitZalochatModule')) == true) {
            $this->postProcess();
        }

        $output = $this->renderForm();

        return $output;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitZalochatModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-star"></i>',
                        'name' => 'ZALOCHAT_OAID',
                        'label' => $this->l('Zalo Official Account ID'),
                        'required' => true
                    ),
                    array(
                        'type' => 'textarea',
                        'name' => 'ZALOCHAT_WELCOME_MESSAGE',
                        'label' => $this->l('Câu chào'),
                        'desc' => $this->l('Tin nhắn chào mừng khi khách truy cập vào trang của bạn. Trong một ngày mỗi khách truy cập chỉ nhận được một tin nhắn chào.'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ZALOCHAT_AUTOPOPUP',
                        'label' => $this->l('Thời gian hiển thị cửa sổ chat (giây)'),
                        'desc' => $this->l('Thời gian chờ để tự động mở khung cửa sổ chat. Mặc định: 0 giây. Trong một ngày với mỗi khách truy cập chỉ tự động bật khung cửa sổ chat một lần.'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ZALOCHAT_WIDTH',
                        'label' => $this->l('Width'),
                        'desc' => $this->l('Đặt chiều rộng cho Widget Chat. Mặc định: 350px'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'ZALOCHAT_HEIGHT',
                        'label' => $this->l('Height'),
                        'desc' => $this->l('Đặt chiều cao cho Widget Chat. Mặc định: 420px'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'ZALOCHAT_OAID' => Configuration::get('ZALOCHAT_OAID', ''),
            'ZALOCHAT_WELCOME_MESSAGE' => Configuration::get('ZALOCHAT_WELCOME_MESSAGE', 'Rất vui khi được hỗ trợ bạn!'),
            'ZALOCHAT_AUTOPOPUP' => Configuration::get('ZALOCHAT_AUTOPOPUP', 0),
            'ZALOCHAT_WIDTH' => Configuration::get('ZALOCHAT_WIDTH', 350),
            'ZALOCHAT_HEIGHT' => Configuration::get('ZALOCHAT_HEIGHT', 420),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function hookDisplayFooter()
    {
        $form_values = $this->getConfigFormValues();
        $this->context->smarty->assign($form_values);
        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/front/zalochat.tpl');
        return $output;
    }
}
