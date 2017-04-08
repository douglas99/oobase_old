<?php

/**
 * File Upload Controlling Module
 * 文件上传控制模块
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
class ctrl_upload
{

    public static $file = []; //$_FILES['file']

    public static $base64 = ''; //BASE64 content

    public static $file_ext = []; //Allowed extensions 允许的扩展

    public static $file_name = ''; //File name without extension 文件名不带扩展名

    public static $file_size = 20971520; //Allowed File size: 20MB by default 允许文件大小：默认为20MB

    public static $save_path = ''; //Upload path 上传路径

    const IMG_TYPE = [1 => 'gif', 2 => 'jpeg', 3 => 'png', 6 => 'bmp']; //MINE Types of allowed images MINE允许图像的类型
    const IMG_EXT  = [1 => 'gif', 2 => 'jpg', 3 => 'png', 6 => 'bmp']; //Extensions of allowed images 允许图片的扩展

    /**
     * Upload a file 上传一个文件
     * @return array
     */
    public static function upload_file(): array
    {
        load_lib('core', 'ctrl_language');
        load_lib('core', 'ctrl_error');
        load_lib('core', 'ctrl_file');
        \ctrl_language::load('core', 'ctrl_upload');
        \ctrl_error::load('core', 'ctrl_upload');
        if (!empty(self::$file)) {
            if (0 === self::$file['error']) {
//Upload success
                //上传成功
                $file_size = self::chk_size(self::$file['size']); //Get the file size
                if (0 < $file_size) {
                    $file_ext = self::chk_ext(self::$file['name']); //Check the file extension
                    if ('' !== $file_ext) {
                        $save_path = \ctrl_file::get_path(self::$save_path); //Get the upload path
                        if (':' !== $save_path) {
                            $file_name = '' !== self::$file_name ? self::$file_name : hash('md5', uniqid(mt_rand(), true)); //Get the file name
                            $url       = self::save_file(self::$file['tmp_name'], $save_path, $file_name, $file_ext); //Save file
                            if ('' !== $url) {
//Done
                                //完成
                                $result              = \ctrl_error::get_error(10000); //Upload finished 上传完成
                                $result['file_url']  = &$url;
                                $result['file_size'] = &$file_size;
                            } else {
                                $result = \ctrl_error::get_error(10001);
                            }
//Failed to move/copy from the tmp file
                            //无法从tmp文件移动/复制
                            unset($file_name, $url);
                        } else {
                            $result = \ctrl_error::get_error(10002);
                        }
//Upload path Error
                        //上传路径错误
                        unset($save_path);
                    } else {
                        $result = \ctrl_error::get_error(10003);
                    }
//Extension not allowed
                    //扩展不允许
                    unset($file_ext);
                } else {
                    $result = \ctrl_error::get_error(10004);
                }
//File too large
                //文件过大
                unset($file_size);
            } else {
                $result = self::get_error(self::$file['error']);
            }
//Upload failed when uploading, returned from server
            //从服务器返回时，上传失败
        } else {
            $result = \ctrl_error::get_error(10007);
        }
//Empty $_FILES['file']
        //$_FILES['file']为空
        return $result;
    }

    /**
     * Upload an image in base64 format
     * 以base64格式上传图像
     * @return array
     */
    public static function upload_base64(): array
    {
        load_lib('core', 'ctrl_language');
        load_lib('core', 'ctrl_error');
        load_lib('core', 'ctrl_file');
        \ctrl_language::load('core', 'ctrl_upload');
        \ctrl_error::load('core', 'ctrl_upload');
        $base64_pos = strpos(self::$base64, 'base64,'); //Get the position
        if (0 === strpos(self::$base64, 'data:image/') && false !== $base64_pos) {
//Check the canvas data, must be an image
            //检查画布数据，必须是图像
            $data     = substr(self::$base64, $base64_pos + 7); //Get the base64 data of the image 获取图像的base64数据
            $img_data = base64_decode($data); //Get the binary data of the image 获取图像的二进制数据
            if (false !== $img_data) {
                $file_size = self::chk_size(strlen($img_data)); //Get the file size 获取文件大小
                if (0 < $file_size) {
                    $img_info = getimagesizefromstring($img_data); //Get the image information 获取图像信息
                    if (array_key_exists($img_info[2], self::img_ext)) {
                        $file_ext  = self::img_ext[$img_info[2]]; //Get the extension 获取扩展名
                        $save_path = \ctrl_file::get_path(self::$save_path); //Get the upload path 获取上传路径
                        if (':' !== $save_path) {
                            $file_name = '' !== self::$file_name ? self::$file_name : hash('md5', uniqid(mt_rand(), true)); //Get the file name 获取文件名
                            $url_path  = $save_path . $file_name . '.' . $file_ext; //Get URL path 获取URL路径
                            $file_path = FILE_PATH . $url_path; //Get real upload path 获取真正的上传路径
                            if (is_file($file_path)) {
                                unlink($file_path);
                            }
//Delete the file if existing
                            //删除文件（如果存在）
                            $save_file = \ctrl_file::put_content($file_path, $img_data); //Write to file 写入文件
                            if (0 < $save_file) {
//Done
                                //完成
                                $result              = \ctrl_error::get_error(10000); //Upload finished 上传完成
                                $result['file_url']  = &$url_path;
                                $result['file_size'] = &$file_size;
                            } else {
                                $result = \ctrl_error::get_error(10001);
                            }
//Failed to write
                            //无法写入
                            unset($file_name, $url_path, $file_path, $save_file);
                        } else {
                            $result = \ctrl_error::get_error(10002);
                        }
//Upload path Error
                        //上传路径错误
                        unset($file_ext, $save_path);
                    } else {
                        $result = \ctrl_error::get_error(10003);
                    }
//Extension not allowed
                    //文件扩展名不允许
                    unset($img_info);
                } else {
                    $result = \ctrl_error::get_error(10004);
                }
//File too large
                //文件过大
                unset($file_size);
            } else {
                $result = \ctrl_error::get_error(10006);
            }
//Image data error
            //图像数据错误
            unset($data, $img_data);
        } else {
            $result = \ctrl_error::get_error(10003);
        }
//Extension not allowed
        //文件扩展名不允许
        unset($base64_pos);
        return $result;
    }

    /**
     * Resize/Crop an image to a giving size
     * 调整大小/裁剪图像给的尺寸
     *
     * @param string $file
     * @param int $width
     * @param int $height
     * @param bool $crop
     */
    public static function image_resize(string $file, int $width, int $height, bool $crop = false)
    {
        $img_info = getimagesize($file);
        if (array_key_exists($img_info[2], self::img_type)) {
            $img_size = $crop ? self::new_img_crop($img_info[0], $img_info[1], $width, $height) : self::new_img_size($img_info[0], $img_info[1], $width, $height);
            if ($img_info[0] !== $img_size['img_w'] || $img_info[1] !== $img_size['img_h']) {
                $type       = self::img_type[$img_info[2]];
                $img_create = 'imagecreatefrom' . $type;
                $img_func   = 'image' . $type;
                $img_source = $img_create($file);
                $img_thumb  = imagecreatetruecolor($img_size['img_w'], $img_size['img_h']);
                switch ($img_info[2]) {
                    case 1: //Deal with the transparent color in a GIF 处理GIF中的透明颜色
                        $transparent = imagecolorallocate($img_thumb, 0, 0, 0);
                        imagefill($img_thumb, 0, 0, $transparent);
                        imagecolortransparent($img_thumb, $transparent);
                        break;
                    case 3: //Deal with the transparent color in a PNG 处理PNG中的透明颜色
                        $transparent = imagecolorallocatealpha($img_thumb, 0, 0, 0, 127);
                        imagealphablending($img_thumb, false);
                        imagefill($img_thumb, 0, 0, $transparent);
                        imagesavealpha($img_thumb, true);
                        break;
                }
                imagecopyresampled($img_thumb, $img_source, 0, 0, $img_size['img_x'], $img_size['img_y'], $img_size['img_w'], $img_size['img_h'], $img_size['src_w'], $img_size['src_h']);
                $img_func($img_thumb, $file);
                imagedestroy($img_source);
                imagedestroy($img_thumb);
                unset($type, $img_create, $img_func, $img_source, $img_thumb, $transparent);
            }
            unset($img_size);
        }
        unset($file, $width, $height, $crop, $img_info);
    }

    /**
     * Get and check the file size
     * 获取并检查文件大小
     *
     * @param int $file_size
     *
     * @return int
     */
    private static function chk_size(int $file_size): int
    {
        return $file_size <= self::$file_size ? $file_size : 0;
    }

    /**
     * Get and check the file extension
     * 获取并检查文件扩展名
     *
     * @param string $file_name
     *
     * @return string
     */
    private static function chk_ext(string $file_name): string
    {
        $ext = \ctrl_file::get_ext($file_name);
        if ('' !== $ext && !empty(self::$file_ext) && !in_array($ext, self::$file_ext, true)) {
            $ext = '';
        }
//File extension not allowed, set to empty string
        //文件扩展名不允许，设置为空字符串
        unset($file_name);
        return $ext;
    }

    /**
     * Save the file from the tmp file
     * 从tmp文件保存文件
     *
     * @param string $file
     * @param string $save_path
     * @param string $file_name
     * @param string $file_ext
     *
     * @return string
     */
    private static function save_file(string $file, string $save_path, string $file_name, string $file_ext): string
    {
        $url_path  = $save_path . $file_name . '.' . $file_ext; //Get URL path
        $file_path = FILE_PATH . $url_path; //Get real upload path
        if (is_file($file_path)) {
            unlink($file_path);
        }
//Delete the existing file
        //删除现有文件
        $move = move_uploaded_file($file, $file_path); //Move the tmp file to the right path 将tmp文件移动到正确的路径
        if (!$move) {
            $move = copy($file, $file_path); //Failed to move, copy it 无法移动，复制
            if (!$move) {
                $url_path = '';
            }
//Return empty path if failed to copy the file
            //如果无法复制文件，则返回空路径
        }
        unset($file, $save_path, $file_name, $file_ext, $file_path, $move);
        return $url_path;
    }

    /**
     * Get cropped image coordinates according to the giving size
     * 根据给的尺寸获得裁剪后的图像坐标
     *
     * @param int $img_width //Original width 原始宽度
     * @param int $img_height //Original height 原始高度
     * @param int $need_width //Needed width 想要的宽度
     * @param int $need_height //Needed height 想要的高度
     *
     * @return array
     */
    private static function new_img_crop(int $img_width, int $img_height, int $need_width, int $need_height): array
    {
        $img_x = $img_y = 0;
        $src_w = $img_width;
        $src_h = $img_height;
        if (0 < $img_width && 0 < $img_height) {
            $ratio_img  = $img_width / $img_height;
            $ratio_need = $need_width / $need_height;
            $ratio_diff = round($ratio_img - $ratio_need, 2);
            if (0 < $ratio_diff && $img_height > $need_height) {
                $crop_w = (int) ($img_width - $img_height * $ratio_need);
                $img_x  = (int) ($crop_w / 2);
                $src_w  = $img_width - $crop_w;
                unset($crop_w);
            } elseif (0 > $ratio_diff && $img_width > $need_width) {
                $crop_h = (int) ($img_height - $img_width / $ratio_need);
                $img_y  = (int) ($crop_h / 2);
                $src_h  = $img_height - $img_y * 2;
                unset($crop_h);
            }
            unset($ratio_img, $ratio_need, $ratio_diff);
        }
        $img_data = ['img_x' => &$img_x, 'img_y' => &$img_y, 'img_w' => &$need_width, 'img_h' => &$need_height, 'src_w' => &$src_w, 'src_h' => &$src_h];
        unset($img_width, $img_height, $need_width, $need_height, $img_x, $img_y, $src_w, $src_h);
        return $img_data;
    }

    /**
     * Get new image size according to the giving size
     * 根据给出的尺寸获取新的图像大小
     *
     * @param int $img_width //Original width 原始宽度
     * @param int $img_height //Original height 原始高度
     * @param int $need_width //Needed width 想要的宽度
     * @param int $need_height //Needed height 想要的高度
     *
     * @return array
     */
    private static function new_img_size(int $img_width, int $img_height, int $need_width, int $need_height): array
    {
        $src_w = $img_width;
        $src_h = $img_height;
        if (0 < $img_width && 0 < $img_height) {
            $ratio_img  = $img_width / $img_height;
            $ratio_need = $need_width / $need_height;
            $ratio_diff = round($ratio_img - $ratio_need, 2);
            if (0 < $ratio_diff && $img_width > $need_width) {
                $img_width  = &$need_width;
                $img_height = (int) ($need_width / $ratio_img);
            } elseif (0 > $ratio_diff && $img_height > $need_height) {
                $img_height = &$need_height;
                $img_width  = (int) ($need_height * $ratio_img);
            } elseif (0 === $ratio_diff && $img_width > $need_width && $img_height > $need_height) {
                $img_width  = &$need_width;
                $img_height = &$need_height;
            }
            unset($ratio_img, $ratio_need, $ratio_diff);
        } else {
            $img_width  = &$need_width;
            $img_height = &$need_height;
        }
        $img_data = ['img_x' => 0, 'img_y' => 0, 'img_w' => &$img_width, 'img_h' => &$img_height, 'src_w' => &$src_w, 'src_h' => &$src_h];
        unset($img_width, $img_height, $need_width, $need_height, $src_w, $src_h);
        return $img_data;
    }

    /**
     * Get the error code from the Server
     * 从服务器获取错误代码
     *
     * @param int $error_code
     *
     * @return array
     */
    private static function get_error(int $error_code): array
    {
        switch ($error_code) {
            case 1:
                $result = \ctrl_error::get_error(10004);
                break;
            case 2:
                $result = \ctrl_error::get_error(10004);
                break;
            case 3:
                $result = \ctrl_error::get_error(10006);
                break;
            case 4:
                $result = \ctrl_error::get_error(10007);
                break;
            case 6:
                $result = \ctrl_error::get_error(10005);
                break;
            case 7:
                $result = \ctrl_error::get_error(10008);
                break;
            default:
                $result = \ctrl_error::get_error(10001);
                break;
        }
        unset($error_code);
        return $result;
    }
}
