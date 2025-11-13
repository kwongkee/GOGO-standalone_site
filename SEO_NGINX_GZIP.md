# Nginx GZIP 全局启用

**方式**：主配置 `/www/server/nginx/conf/nginx.conf`  
**时间**：$(date)  
**验证**：
```bash
$(curl -s -I -H "Accept-Encoding: gzip" https://dtc.gogo198.net/ | grep -i content-encoding)
备份：/www/server/nginx/conf/nginx.conf.bak.*
