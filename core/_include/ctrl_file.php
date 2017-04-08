<?php

/**
 * File I/O Controlling Module
 * 文件I/O流控制模块
 * Author Jerry Shaw <jerry-shaw@live.com>
 * Author 秋水之冰 <27206617@qq.com>
 * Author 彼岸花开 <330931138@qq.com>
 *
 * Copyright 2015-2017 Jerry Shaw
 * Copyright 2016-2017 秋水之冰
 * Copyright 2016 彼岸花开
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
class ctrl_file
{
    /**
     * Get the content from a file
     * 从文件获取内容
     * @param string $file
     *
     * @return string
     */
    public static function get_content(string $file): string
    {
        $handle = fopen($file, 'rb');
        if (false !== $handle) {
            $file_size = filesize($file);
            if (false !== $file_size && 0 < $file_size) {
                $content = fread($handle, $file_size);
                if (false === $content) {
                    $content = file_get_contents($file);
                }

            } else {
                $content = '';
            }

            fclose($handle);
            unset($file_size);
        } else {
            $content = '';
        }

        unset($file, $handle);
        return (string) $content;
    }

    /**
     * Put the content to a file
     * 将内容放入文件
     *
     * @param string $file
     * @param string $data
     *
     * @return int
     */
    public static function put_content(string $file, string $data): int
    {
        $handle = fopen($file, 'wb');
        $result = false !== $handle ? fwrite($handle, $data) : false;
        fclose($handle);
        if (false === $result) {
            $result = file_put_contents($file, $data);
        }

        unset($file, $data, $handle);
        return (int) $result;
    }

    /**
     * Append the content to a file
     * 将内容附加到文件
     *
     * @param string $file
     * @param string $data
     *
     * @return int
     */
    public static function append_content(string $file, string $data): int
    {
        $handle = fopen($file, 'ab');
        $result = false !== $handle ? fwrite($handle, $data) : false;
        fclose($handle);
        if (false === $result) {
            $result = file_put_contents($file, $data, FILE_APPEND);
        }

        unset($file, $data, $handle);
        return (int) $result;
    }

    /**
     * Get the extension of a file
     * 获取文件的扩展名
     *
     * @param string $path
     *
     * @return string
     */
    public static function get_ext(string $path): string
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if ('' !== $ext && 1 === preg_match('/[A-Z]/', $ext)) {
            $ext = strtolower($ext);
        }

        unset($path);
        return $ext;
    }

    /**
     * Check and create the directory if not exists, return a relative path
     * 检查并创建不存在的目录，返回相对路径
     * @param string $path
     *
     * @return string
     */
    public static function get_path(string $path): string
    {
        $real_path = FILE_PATH;
        if ('' !== $path) {
            if (false !== strpos($path, '..')) {
                $path = str_replace('..', '.', $path);
            }
//Parent directory is not allowed 不允许父目录
            if (false !== strpos($path, '\\')) {
                $path = str_replace('\\', '/', $path);
            }
//Get a formatted url path with '/'
            //使用'/'获取格式化的URL路径
            $real_path .= $path;
            if (!is_dir($real_path)) {
                mkdir($real_path, 0664, true);
            }

        }
        $file_path = is_readable($real_path) ? $path . '/' : ':';
        unset($path, $real_path);
        return $file_path;
    }

    /**
     * Get a list of files in a directory or recursively
     * Target extension can be passed by $pattern parameter
     *
     * 获取目录中的文件列表或递归 目标扩展可以通过$ pattern参数传递
         *
     * @param string $path
     * @param string $pattern
     * @param bool $recursive
     *
     * @return array
     */
    public static function get_list(string $path, string $pattern = '*', bool $recursive = false): array
    {
        $list = [];
        $path = realpath($path);
        if (false !== $path) {
            $path .= '/';
            $list = glob($path . $pattern);
            if ($recursive) {
                $dir_list = glob($path . '*');
                foreach ($dir_list as $dir) {
                    if (is_dir($dir)) {
                        $list = array_merge($list, self::get_list($dir, $pattern, true));
                    } else {
                        continue;
                    }

                }
                unset($dir);
            }
        }
        unset($path, $pattern, $recursive);
        return $list;
    }
}
