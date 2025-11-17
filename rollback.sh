# 原代码（假设git reset --hard HEAD~1）
git reset --hard HEAD~1

# 优化后
#!/bin/bash
git reset --hard HEAD~1
if [ $? -ne 0 ]; then
    echo "Rollback failed!" >> rollback.log
    exit 1
fi
echo "Rollback success $(date)" >> rollback.log