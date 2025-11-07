#!/bin/bash
SERVER="root@39.108.11.214"
PATH="/www/wwwroot/website.gogo198.net"

echo "上传 SEO 文件..."
scp -r public/sitemap.xml public/robots.txt public/manifest.json $SERVER:$PATH/public/

echo "上传命令与模板..."
scp -r application/command $SERVER:$PATH/application/
scp -r view/layout/head.html $SERVER:$PATH/view/layout/

echo "生成 sitemap..."
ssh $SERVER << 'EOF'
cd /www/wwwroot/website.gogo198.net
php think sitemap
systemctl restart php-fpm
EOF

echo "SEO 部署完成！"