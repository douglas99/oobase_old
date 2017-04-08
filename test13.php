<?php
/* *-* coding utf-8 *-*
 * @Author: Administrator
 * @Create_Date:   2017-04-06 10:38:16
 * @Last Modified by:   Administrator
 * @Last Modified time: 2017-04-08 19:15:12
 * @file_path: D:\phphuanjing\nginx\oobase\test13.php
 * @file_name_without_extension: test13
 */
require_once 'core/_include/cfg.php';
$class = load_lib('fruit_picker','picker');
$picker = new picker();
$result = $picker::init();
$result = $picker::picker_name();
$api_list = array('size','shape','taste','smell','guess','color');
$arr = get_class_methods($picker);
$arr1 = array_intersect($api_list,$arr);
$arr2 = array('cmd','color','shape');
$arr3 = array();
$arr4 = array();
// $arr2 = array('cmd','color','shape');
// $picker = array('size','shape','taste','smell','guess','color','');
foreach ($api_list as $value) {
	$intersect = array_intersect($arr2,$picker::$api[$value]);
	// $intersect = array('color',shape)
	if(!empty($intersect)) array_push($arr4,$intersect);
	$difference = array_diff($picker::$api[$value],$intersect);
	// $picker = array('size','shape','taste','smell','guess','color');
	// $intersect = array('color',shape)
	// $difference =
	if(empty($difference)){
		array_push($arr3, $value);
	}
}
var_dump(array_intersect(['cmd','color','shape'],['']));
if(empty((array_diff(array(''),array(''))))){
	echo "ok!<br/>" ;
}
// var_dump($intersect);
// var_dump($difference);
var_dump($arr4);
var_dump($arr3);
// var_dump($class);
// var_dump($result);
// var_dump(in_array('init', $arr1, true));
