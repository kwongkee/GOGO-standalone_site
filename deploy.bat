:: 原代码（基于标准bat）
git pull origin main
php think optimize:autoload

:: 优化后
@echo off
git pull origin main
IF %ERRORLEVEL% NEQ 0 (
    echo Pull failed! >> deploy.log
    exit /b 1
)
php think optimize:autoload
IF %ERRORLEVEL% NEQ 0 (
    echo Optimize failed! >> deploy.log
    exit /b 1
)
echo Deploy success %date% %time% >> deploy.log