<?php
/**
 * @Author: Administrator
 * @Date:   2017-04-08 10:39:08
 * @Last Modified by:   Administrator
 * @Last Modified time: 2017-04-08 16:56:54
 */
require_once 'core/_include/cfg.php';
$arr = array();
foreach($_POST as $key => $value){
	if('' === $_POST[$key]){
		unset($_POST[$key]);
	}else{
		$arr[$key] = $value;
	}
}
$a = array('cmd'=>'fruit_picker/picker');
$arr = array_merge($a,$arr);

echo json_encode($arr);

