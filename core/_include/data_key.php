<?php

/**
 * Access-Key Controlling Module
 * Access-Key控制模块
 *
 * Author Jerry Shaw <jerry-shaw@live.com>
 * Author 秋水之冰 <27206617@qq.com>
 *
 * Copyright 2015-2016 Jerry Shaw
 * Copyright 2016 秋水之冰
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
class data_key
{
    //Data pool
    //数据池
    public static $key = [];

    //Client type
    //客户端类型
    public static $client = '';

    //Online Status
    //在线状态
    public static $online = false;

    /**
     * Map all the SESSION/Access-Key data to a static variable
     * Get the online status by checking the online tags
     * Grant permission for Cross-Domain request
     * 将所有SESSION / Access-Key数据映射到静态变量
     * 通过检查在线标签获取在线状态
     * 授予跨域请求许可
     */
    public static function start()
    {
        //Get HTTP HOST and HTTP ORIGIN ready for Cross-Domain permission detection
        //获取HTTP HOST和HTTP ORIGIN准备进行跨域权限检测
        $Server_HOST = isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http' . '://' . $_SERVER['HTTP_HOST'];
        $Origin_HOST = $_SERVER['HTTP_ORIGIN'] ?? $Server_HOST;
        //Process HTTP requests
        //处理HTTP请求
        if ('OPTIONS' !== $_SERVER['REQUEST_METHOD']) {
            //Load user_crypt module from defined module path
            //从定义的模块路径加载user_crypt模块
            load_lib(CRYPT_PATH, 'user_crypt');
            //Start SESSION
            //开启 SESSION
            if (Redis_SESSION) {
                //Load Redis SESSION Controller
                //加载Redis SESSION控制器
                load_lib('core', 'ctrl_session');
                \ctrl_session::start();
            } else {
                session_start();
            }

            //Detect requests
            //检测请求
            switch (self::$client) {
                //For 127.0.0.1 requests
                //对于127.0.0.1请求
                case 'LOCAL':
                    if (!empty($_SESSION)) {
                        self::map_sess();
                    }

                    break;
                //For third party requests
                //对于第三方请求
                case 'REMOTE':
                    if (isset($_SERVER['HTTP_ACCESS_KEY'])) {
                        self::map_key();
                    }

                    break;
                //Auto Detect
                //自动检测
                default:
                    //Detect requested client type
                    //检测所请求的客户端类型
                    if (isset($_SERVER['HTTP_ORIGIN'])) {
                        self::$client = $Server_HOST === $_SERVER['HTTP_ORIGIN'] ? self::chk_cookie() : 'REMOTE';
                    } elseif (isset($_SERVER['HTTP_REFERER'])) {
                        $referer      = parse_url($_SERVER['HTTP_REFERER']);
                        self::$client = false !== $referer && isset($referer['scheme']) && isset($referer['host']) && $Server_HOST === $referer['scheme'] . '://' . $referer['host'] . (!isset($referer['port']) || 80 === $referer['port'] ? '' : ':' . $referer['port']) ? self::chk_cookie() : 'REMOTE';
                        unset($referer);
                    } else {
                        self::$client = 'REMOTE';
                    }

                    //Process Access-Key
                    //处理 Access-Key
                    if ('LOCAL' === self::$client) {
                        //Extract data from SESSION or Access-Key
                        //从SESSION或Access-Key提取数据
                        if (!empty($_SESSION)) {
                            self::map_sess();
                        } elseif (isset($_SERVER['HTTP_ACCESS_KEY'])) {
                            self::map_key();
                        }

                    } elseif ('REMOTE' === self::$client && isset($_SERVER['HTTP_ACCESS_KEY'])) {
                        //Extract data from Access-Key 从SESSION或Access-Key提取数据
                        self::map_key();
                        //Check Access-Key content
                        //检查Access-Key内容
                        if (!empty(self::$key)) {
                            //Check Cross-Domain request permission for Javascript
                            //检查Javascript的跨域请求权限
                            if (isset(self::$key['JS-DEV']) && 'on' === self::$key['JS-DEV']) {
                                //Provide Cross-Domain permission support for correct Access-Key request
                                //为正确的Access-Key请求提供跨域权限支持
                                header('Access-Control-Allow-Origin: ' . $Origin_HOST);
                                header('Access-Control-Allow-Methods: GET, POST');
                                header('Access-Control-Allow-Headers: Access-Key');
                            } else {
                                //Remove Cross-Domain permission support when the Access-Key is incorrect
                                //当Access-Key不正确时，删除跨域权限支持
                                header('Access-Control-Allow-Origin: ' . $Server_HOST);
                                header('Access-Control-Allow-Methods: GET, POST');
                                header('Access-Control-Allow-Headers: Accept');
                            }
                        } else {
                            exit;
                        }
                        //Exit running if Access-Key content is empty
                        //如果Access-Key内容为空，则退出运行
                    }
                    break;
            }
            //Get the online status
            //获取在线状态
            self::$online = self::chk_online();
        } else {
            //Grant basic Cross-Domain request permission for HTTP OPTIONS Request and allow HTTP Header "Access-Key"
            //授予HTTP OPTIONS请求的基本跨域请求权限，并允许HTTP头“Access-Key”
            header('Access-Control-Allow-Origin: ' . $Origin_HOST);
            header('Access-Control-Allow-Methods: OPTIONS');
            header('Access-Control-Allow-Headers: Access-Key');
            //Exit running after basic Cross-Domain request permission was granted
            //基本跨域请求权限授予后退出运行
            exit;
        }
        unset($Server_HOST, $Origin_HOST);
    }

    /**
     * Renew Access-Key to a new timestamp, or, make it expired by passing a passed timestamp
     * 将新访问密钥更新为新的时间戳，或者通过传递传递的时间戳使其过期
     * Return new Access-Key content after renewing
     * 更新后返回新的Access-Key内容
     *
     * @param int $ExpireAt
     *
     * @return string
     */
    public static function renew(int $ExpireAt): string
    {
        if (!empty(self::$key)) {
            if ($ExpireAt > time()) {
                self::$key['ExpireAt'] = &$ExpireAt;
                if ('LOCAL' === self::$client) {
                    $_SESSION['ExpireAt'] = &$ExpireAt;
                }

            } else {
                self::$key = [];
                if ('LOCAL' === self::$client) {
                    $_SESSION = [];
                    session_destroy();
                }
            }
        }
        unset($ExpireAt);
        return self::get_key();
    }

    /**
     * Add a key => value pair to Access-Key 将 key=>value 添加到Access-Key
     * Return new Access-Key content after adding 添加后返回新的Access-Key内容
     *
     * @param string $key
     * @param string $value
     * @param bool $is_int
     *
     * @return string
     */
    public static function add(string $key, string $value, bool $is_int = false): string
    {
        if ('' !== $key) {
            if ($is_int) {
                $value = (int) $value;
            }

            self::$key[$key] = &$value;
            if ('LOCAL' === self::$client) {
                $_SESSION[$key] = &$value;
            }

        }
        unset($key, $value, $is_int);
        return self::get_key();
    }

    /**
     * Remove a/all content value from Access-Key
     * 从Access-Key中删除一个或所有内容值
     * Return new Access-Key content after removing
     * 删除后返回新的Access-Key内容
     *
     * @param string $key
     *
     * @return string
     */
    public static function remove(string $key = ''): string
    {
        if ('' !== $key) {
            unset(self::$key[$key]);
            if ('LOCAL' === self::$client) {
                unset($_SESSION[$key]);
            }

        } else {
            self::$key = [];
            if ('LOCAL' === self::$client) {
                $_SESSION = [];
                session_destroy();
            }
        }
        unset($key);
        return self::get_key();
    }

    /**
     * Get Access-Key encrypted content
     * 获取Access-Key加密内容
     * @return string
     */
    private static function get_key(): string
    {
        return !empty(self::$key) ? \user_crypt::create_key(self::$key) : '';
    }

    /**
     * Map Access-Key content to key
     * 映射 Access-Key 的键值对 内容
     */
    private static function map_key()
    {
        self::$client = 'REMOTE';
        $content      = \user_crypt::validate_key($_SERVER['HTTP_ACCESS_KEY']);
        if (!empty($content) && isset($content['ExpireAt']) && time() < $content['ExpireAt']) {
            self::$key = &$content;
        }

        unset($content);
    }

    /**
     * Map SESSION content to key
     * 将SESSION内容映射到键中
     */
    private static function map_sess()
    {
        self::$client = 'LOCAL';
        if (!empty($_SESSION)) {
            self::$key = &$_SESSION;
        }

    }

    /**
     * Check COOKIE Key and Value with SESSION data
     * 使用SESSION数据检查COOKIE键和值
     * @return string
     */
    private static function chk_cookie(): string
    {
        $session_name = session_name();
        $client       = isset($_COOKIE[$session_name]) && session_id() === $_COOKIE[$session_name] ? 'LOCAL' : 'REMOTE';
        unset($session_name);
        return $client;
    }

    /**
     * Get the online status by checking the online tags in Access-Key
     * 通过检查Access-Key中的在线标签获取在线状态
     */
    private static function chk_online(): bool
    {
        $online = true;
        foreach (ONLINE_TAGS as $tag) {
            if (!isset(self::$key[$tag])) {
                $online = false;
                break;
            } else {
                continue;
            }

        }
        unset($tag);
        return $online;
    }
}
