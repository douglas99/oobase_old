<?php

// $redis = new Redis();

// $redis->connect('127.0.0.1', '6379');

// $redis->set('say', 'hello world');

// echo $redis->get('say');

// $array = array(
//     'first_key' => 'first_val',
//     'second_key' => 'second_val',
//     'third_key' => 'third_val');

// $array_get = array('first_key', 'second_key', 'third_key');
// $redis->mset($array);
// $dump = $redis->mget($array_get);
// print_r($dump);
declare (ticks = 1);
header('content-type:text/html;charset=utf-8');
$start_time = time();
function check_timeout()
{
    global $start_time;
    $timeout = 5;
    if (time() - $start_time > $timeout) {
        exit("超时{$timeout}秒\n");
    }
}

register_tick_function('check_timeout');
while (1) {
    $num = 1;
}
while (1) {
    $num = 1;
}
$a = new Reflection;
