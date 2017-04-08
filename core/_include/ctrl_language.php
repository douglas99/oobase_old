<?php

/**
 * Language Controlling Module
 * 语言控制模块
 * Author Jerry Shaw <jerry-shaw@live.com>
 * Author 秋水之冰 <27206617@qq.com>
 * Author 彼岸花开 <330931138@qq.com>
 *
 * Copyright 2015-2016 Jerry Shaw
 * Copyright 2015-2016 秋水之冰
 * Copyright 2015-2016 彼岸花开
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
class ctrl_language
{
    //Language
    //语言
    public static $lang = 'en-US';

    /**
     * Load language pack from module
     * 从模块加载语言包
     *
     * @param string $module
     * @param string $file
     */
    public static function load(string $module = '', string $file)
    {
        if (isset($_GET['lang'])) {
            $lang = &$_GET['lang'];
        } elseif (isset($_COOKIE['lang'])) {
            $lang = &$_COOKIE['lang'];
        } elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $lang = 'zh' === substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) ? 'zh-CN' : 'en-US';
            setcookie('lang', $lang, time() + 2592000, '/');
        } else {
            $lang = 'en-US';
        }

        if (!in_array($lang, LANGUAGE_LIST, true)) {
            $lang = 'en-US';
        }

        if ('en-US' !== $lang) {
            self::$lang = &$lang;
        }

        $path = '' === $module || '/' === $module ? ROOT . '/_language/' : ROOT . '/' . $module . '/_language/';
        putenv('LANG=' . $lang);
        setlocale(LC_ALL, $lang);
        bindtextdomain($file, $path);
        textdomain($file);
        unset($module, $file, $lang, $path);
    }

    /**
     * Get text by language from an array
     * 从数组中获取语言文本
     *
     * @param array $keys
     *
     * @return array
     */
    public static function get_text(array $keys): array
    {
        $data = [];
        //Go over every language key to get the text
        //浏览每个语言密钥以获取文本
        foreach ($keys as $key) {
            $data[$key] = gettext($key);
        }

        unset($keys, $key);
        return $data;
    }
}
