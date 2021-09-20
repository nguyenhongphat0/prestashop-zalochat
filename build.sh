# Build source code to installable zip file
zip zalochat.zip zalochat -r -x '*.git*'

# Update new code over FTP
. ./.env.sh
lftp -e "set ftp:ssl-allow no; mirror -R zalochat public_html/modules/zalochat; quit" -u $FTP_USER,$FTP_PASSWD $FTP_HOST
echo Deployed successfully to $FTP_HOST