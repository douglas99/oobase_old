<?php

/**
 * API Script
 * API 脚本
 *
 * Author Jerry Shaw <jerry-shaw@live.com>
 * Author 秋水之冰 <27206617@qq.com>
 *
 * Copyright 2015-2017 Jerry Shaw
 * Copyright 2016-2017 秋水之冰
 *
 * This file is part of NervSys.
 *
 * NervSys is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NervSys is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NervSys. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * This script is an universal API script for NervSys.
 * Authentication is recommended for security before running "data_pool::start()".
 * 此脚本是NervSys的通用API脚本。
 * 在运行“data_pool::star()”之前，建议进行安全验证。
 */

declare (strict_types = 1);

//Load CFG file (basic function script is loaded in the cfg file as also).
//加载CFG文件（基本功能脚本也加载在cfg文件中）。
require __DIR__ . '/core/_include/cfg.php';

//Detect PHP SAPI
// 检测 PHP SAPI
if ('cli' !== PHP_SAPI) {
    //Code Block for CGI Mode CGI模式的代码块
    //Load data_key as an overall module and start it. 将data_key作为整体模块加载并启动它
    load_lib('core', 'data_key');
    //Start data_key process
    //启动data_key进程
    \data_key::start();
    //Load data_pool as an overall module and start it.
    //将data_pool作为整体模块加载并启动它。
    load_lib('core', 'data_pool');
    //Start data_pool process
    //启动data_pool进程
    \data_pool::start();
    //Valid values for "data_pool::$format" are "json" and "raw", which should be changed via GET or POST
    //All returned data will be output in JSON by default, or, kept in data pool for further use by setting to "raw"
    /*“data_pool::$format”的有效值为“json”和“raw”，应通过GET或POST进行更改
    默认情况下，所有返回的数据都将以JSON格式输出，或者通过设置为“raw”，保留在数据池中以供进一步使用*/
    if ('json' === \data_pool::$format) {
        //Force output content to UTF-8 formatted plain text
        //强制输出内容为UTF-8格式的纯文本
        header('Content-Type: text/plain; charset=UTF-8');
        //Output JSON formatted result
        //输出JSON格式的结果
        echo json_encode(\data_pool::$pool);
        exit;
    }
} else {
    //Code Block for CLI Mode
    //Force output content to UTF-8 formatted plain text
  /*  //CLI模式的代码块
    //强制输出内容为UTF-8格式的纯文本*/
    header('Content-Type: text/plain; charset=UTF-8');
    //Load CLI Controlling Module
    //加载CLI控制模块
    load_lib('core', 'ctrl_cli');
    //Pass options
    //通行证
    \ctrl_cli::$opt = getopt(CLI_RUN_OPTIONS, CLI_LONG_OPTIONS, $optind);
    //Pass variables
    //传递变量
    \ctrl_cli::$var = array_slice($argv, $optind);
    //Start CLI
    //启动CLI
    $result = \ctrl_cli::start();
    //Output Result
    //输出结果
    if (!empty($result)) {
        //Output JSON formatted result via STDOUT
        //通过STDOUT输出JSON格式的结果
        fwrite(STDOUT, json_encode($result));
        //Close STDOUT stream
        //关闭STDOUT流
        fclose(STDOUT);
        exit;
    }
}
