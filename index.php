<?php
/* *-* coding utf-8 *-*
 * @Author: Administrator
 * @Date:   2016-12-23 08:59:03
 * @Last Modified by:   Administrator
 * @Last Modified time: 2017-04-07 18:48:57
 * @file_path: D:\phphuanjing\nginx\oobase\index.php
 * @file_name_without_extension: index
 */
//要运行bindtextdomain得开启php.ini里面的php_gettext.dll模块
include_once 'core/_include/cfg.php';
$data       = array(
    'cmd' => 'user/user_acc,resume_online,keep_online,key_detail,user/user_output,get_user_list,get_credits,get_user_detail,get_user_info,get_user_menu',
    'map' => 'user/user_output/get_user_list/list:user_list');

include_once '_html/header.php';
echo '<pre>';
$request = curl_request(API, $data, A_KEY);
print_r(json_decode($request,true));
include_once '_html/footer.php';
