<?php
$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    die("<pre>错误：vendor/autoload.php 不存在！<br>请运行：composer install</pre>");
}
require $autoload;

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
    } catch (Exception $e) {
        echo "加载 .env 失败：" . $e->getMessage() . "<br>";
    }
}

if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }
}

echo "<pre>env() 函数测试结果：<br>";
echo "DB_HOST = " . env('DB_HOST', '默认值') . "<br>";
echo "当前时间 = " . date('Y-m-d H:i:s') . "<br>";
echo "env() 函数 <span style='color:green; font-weight:bold'>已启用</span></pre>";
