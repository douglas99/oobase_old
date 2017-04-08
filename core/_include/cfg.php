<?php

/**
 * Basic Configurations
 *
 * Author Jerry Shaw <jerry-shaw@live.com>
 * Author 秋水之冰 <27206617@qq.com>
 * Author 彼岸花开 <330931138@qq.com>
 *
 * Copyright 2015-2017 Jerry Shaw
 * Copyright 2016-2017 秋水之冰
 * Copyright 2016 彼岸花开
 *
 * This file is part of NervSys.
 * 此文件是NervSys的一部分。
 * NervSys is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NervSys是免费软件：您可以根据自由软件基金会发布的GNU通用公共许可证
 * （许可证版本3）或（在您的选择）任何更高版本的条款重新分发和/或修改它。
 * NervSys is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * NervSys是分发的，希望它是有用的，但没有任何保证;
 * 甚至没有适销性或适用于特定用途的默示保证。有关详细信息，请参阅GNU通用公共许可证。
 * You should have received a copy of the GNU General Public License
 * along with NervSys. If not, see <http://www.gnu.org/licenses/>.
 *
 * 您应该已经收到GNU通用公共许可证以及NervSys的副本。
 * 如果没有，请参阅<http://www.gnu.org/licenses/>。
 */

//Basic Settings
//基本设置
set_time_limit(0);
error_reporting(E_ALL);
ignore_user_abort(true);
date_default_timezone_set('PRC');
header('Content-Type:text/html; charset=utf-8');

//Document Root Definition
//文件根定义
define('ROOT', substr(__DIR__, 0, -14));

//Enable/Disable HTTP GET Method
//启用/禁用HTTP GET方法
define('ENABLE_GET', true);

//Enable/Disable API Safe Zone
//启用/禁用API安全区域
define('SECURE_API', true);

//Enable/Disable Language Module for Error Controlling Module
//启用/禁用用于错误控制模块的语言模块
define('ERROR_LANG', true);

//Define the path containing Encrypt/Decrypt module
//定义包含加密/解密模块的路径
define('CRYPT_PATH', 'core');

//Define Online State Tags
//定义在线状态标签
define('ONLINE_TAGS', ['uuid', 'char']);

//Define Available languages
//定义可用语言
define('LANGUAGE_LIST', ['en-US', 'zh-CN']);

//File Storage Server Settings
//文件存储服务器设置
define('FILE_PATH', 'D:/Sites/Files/');
define('FILE_DOMAIN', 'https://file.oobase.com/');

//CLI Settings
//CLI设置
define('CLI_LOG_PATH', ROOT . '/_cli/_log/'); //Log path 日志路径
define('CLI_WORK_PATH', ROOT . '/_cli/_temp/'); //Working path 工作路径
define('CLI_EXEC_PATH', 'D:/phphuanjing/PHP7.1/php.exe'); //PHP executable binary path PHP可执行二进制路径
define('CLI_RUN_OPTIONS', 'c:m:d:g:t:w:p:l'); //Short options (Equal to Long Options)
//短选择（等于长选项）
define('CLI_LONG_OPTIONS', ['cmd:', 'map:', 'data:', 'get:', 'try:', 'wait:', 'path:', 'log']); //Long options (Preferred) 长选项（首选项）

//MySQL Settings
//MySQL设置
define('MySQL_HOST', '127.0.0.1');
define('MySQL_PORT', 3306);
define('MySQL_DB', 'DB_NAME');
define('MySQL_USER', 'root');
define('MySQL_PWD', '11111');
define('MySQL_CHARSET', 'utf8mb4');
define('MySQL_PERSISTENT', true);

//Redis Settings
//Redis设置
define('Redis_HOST', '127.0.0.1');
define('Redis_PORT', 6379);
define('Redis_DB', 0);
define('Redis_AUTH', '');
define('Redis_PERSISTENT', true);
define('Redis_SESSION', true);

//SMTP Mail Settings
//SMTP邮件设置
define('SMTP_HOST', 'SMTP_HOST');
define('SMTP_PORT', 465);
define('SMTP_USER', 'SMTP_USER');
define('SMTP_PWD', 'SMTP_PWD');
define('SMTP_SENDER', 'SMTP_SENDER');
//设置默认API接口
define('API','http://dev.oobase.com/api.php');
//设置默认Access_key
define('A_KEY','NTg2NTgzMDMzMjI2ODI5MjIyMTQ3NTMxNDQyMTI2MzAwMzcxNTAxNTYxNTA3NDM2MTE0MDUwNjU3MDUzMzU1Mg==-uzxJpFFgX5z7EteIJt8y01+ZB6W59tT5h+WIjPl6n/G4fYtkAoHjoHZgtv/Um4W6dku1wxswZWvjoahLvNzWcno96ytlGbTkNfrnTRuOiPTgn15Xr269sc17ieCn7/SM4P2ZuFpZNqeWA6XTu2WJm8JTWKsxoO8W0vIQiGMc5yDdAUpi1rYNPvaFlkO6dtmHKyOm8Y0Ay9+Nza/clKsph04LQwvuSDM2MZYQ4n1g+ytwSB/8AdpZUBnmnyst4rX1OZGB0IeosTfTv8AvXipMbPwxXB0l0xlegzyrpVk557Y5RLpl');
//Load basic function script
//加载基本功能脚本
require __DIR__ . '/cfg_fn.php';
