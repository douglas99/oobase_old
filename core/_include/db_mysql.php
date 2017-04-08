<?php

/**
 * PDO MySQL Module
 * PDO MySQL 模块
 *
 * Author Jerry Shaw <jerry-shaw@live.com>
 * Author 秋水之冰 <27206617@qq.com>
 * Author 彼岸花开 <330931138@qq.com>
 *
 * Copyright 2015 Jerry Shaw
 * Copyright 2015 秋水之冰
 * Copyright 2015 彼岸花开
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
class db_mysql
{
    /**
     * Declare all the parameters for PDO instance on MySQl Database
     * All default to NULL, but can be changed by passing variables
     * Passing different parameters can produce different PDO instances
     * 在MySQl数据库上声明PDO实例的所有参数
     * 全部默认为NULL，但可以通过传递变量来更改
     * 传递不同的参数可以产生不同的PDO实例
     */
    public static $mysql_host;
    public static $mysql_port;
    public static $mysql_db;
    public static $mysql_user;
    public static $mysql_pwd;
    public static $mysql_charset;
    public static $mysql_persistent;

    /**
     * @return PDO
     */
    public static function connect(): PDO
    {
        //Parameters for PDO instance
        //PDO实例化的参数
        $mysql_host    = self::$mysql_host ?? MySQL_HOST;
        $mysql_port    = self::$mysql_port ?? MySQL_PORT;
        $mysql_db      = self::$mysql_db ?? MySQL_DB;
        $mysql_user    = self::$mysql_user ?? MySQL_USER;
        $mysql_pwd     = self::$mysql_pwd ?? MySQL_PWD;
        $mysql_charset = self::$mysql_charset ?? MySQL_CHARSET;
        //Try to connect MySQL Server
        //尝试连接MySQL服务器
        try {
            $dsn     = 'mysql:host=' . $mysql_host . ';port=' . $mysql_port . ';dbname=' . $mysql_db . ';charset=' . $mysql_charset;
            $options = [
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $mysql_charset,
                \PDO::ATTR_PERSISTENT => self::$mysql_persistent ?? MySQL_PERSISTENT,
            ];
            $db_mysql = new \PDO($dsn, $mysql_user, $mysql_pwd, $options);
            $db_mysql->exec('SET NAMES ' . $mysql_charset);
            unset($dsn, $options);
        } catch (Exception $error) {
            exit('Failed to connect MySQL Server! ' . $error->getMessage());
        }
        unset($mysql_host, $mysql_port, $mysql_db, $mysql_user, $mysql_pwd, $mysql_charset);
        //Return the PDO instance
        //返回PDO实例化对象
        return $db_mysql;
    }
}
