```bash
#!/bin/bash
TAG=$1
if [ -z "$TAG" ]; then
    echo "用法: ./rollback.sh v1.0.0-safe"
    exit 1
fi

git fetch --tags
git checkout $TAG
systemctl restart php-fpm
echo "已回滚到 $TAG"
```