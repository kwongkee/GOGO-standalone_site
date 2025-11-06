@echo off
chcp 65001 > nul
echo 开始部署到阿里云服务器...

set SERVER_IP=39.108.11.214
set SERVER_USER=root
set PROJECT_PATH=/www/wwwroot/website.gogo198.net
set SSH_KEY=C:\Users\gogo\Desktop\Gogo.pem

echo 1. 打包代码...
git archive --format=zip main > deploy_temp.zip

echo 2. 上传到服务器...
scp -i "%SSH_KEY%" deploy_temp.zip %SERVER_USER%@%SERVER_IP%:/tmp/

echo 3. 在服务器解压和重启...
ssh -i "%SSH_KEY%" %SERVER_USER%@%SERVER_IP% "cd %PROJECT_PATH% && unzip -o /tmp/deploy_temp.zip && rm /tmp/deploy_temp.zip && /etc/init.d/php-fpm-74 restart"

echo 4. 清理本地文件...
del deploy_temp.zip

echo 部署完成！
pause