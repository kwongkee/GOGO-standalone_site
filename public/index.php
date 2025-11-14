<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');

// ---------- 1. 强制 HTTPS ----------
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $url);
    exit;
}

// ---------- 2. 开启 GZIP ----------
if (extension_loaded('zlib') && 
    isset($_SERVER['HTTP_ACCEPT_ENCODING']) && 
    strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
    // 只调用一次
    ob_start('ob_gzhandler');
} else {
    ob_start();
}


// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
