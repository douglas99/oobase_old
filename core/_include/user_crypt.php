<?php

/**
 * Encrypt/Decrypt Module
 * 加密/解密模块
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
 *
 * !!!Notice!!!
 * This is a demo encrypt/decrypt module only,
 * not for production use.
 * We strongly recommend you to rewrite this module
 * to provide higher security.
 * ！！！注意！！！
 * 这是一个演示加密/解密模块，
 * 不供生产使用。
 * 我们强烈建议您重写此模块
 * 提供更高的安全性。
 */
class user_crypt
{
    //Crypt Method
    //crypt加密方法
    const CRYPT_METHOD = 'aes-256-ctr';

    //Crypt KEY of public
    //
    const PUB_KEY = 'C268-AB06-06E3-2B84-80AC';

    //Crypt KEY of iv
    const IV_KEY = '62A5BD5AD8FD8B86';

    /**
     * Get the crypt key
     * @return string
     */
    public static function get_codes(): string
    {
        return self::PUB_KEY;
    }

    /**
     * Encrypt a string using crypt key codes, and return it in base64 encode
     * 使用密码密钥加密一个字符串，并返回base64编码
     *
     * @param string $string
     *
     * @return string
     */
    public static function encode(string $string): string
    {
        return openssl_encrypt($string, self::CRYPT_METHOD, self::PUB_KEY, 0, self::IV_KEY);
    }

    /**
     * Decrypt a string using crypt key codes, need to decode from base64 first
     * 使用crypt密钥码解密一个字符串，需要先从base64进行解码
     *
     * @param string $string
     *
     * @return string
     */
    public static function decode(string $string): string
    {
        return openssl_decrypt($string, self::CRYPT_METHOD, self::PUB_KEY, 0, self::IV_KEY);
    }

    /**
     * Get an encrypt key from an array
     * 从数组中获取加密密钥
     *
     * @param array $key_data
     *
     * @return string
     */
    public static function create_key(array $key_data): string
    {
        return (string) self::encode(json_encode($key_data));
    }

    /**
     * Check and extract all the data from a key
     * 检查并从密钥中提取所有数据
     *
     * @param string $access_key
     *
     * @return array
     */
    public static function validate_key(string $access_key): array
    {
        $access_key = self::decode($access_key);
        $key_data   = json_decode($access_key, true);
        unset($access_key);
        return $key_data ?? [];
    }
}
