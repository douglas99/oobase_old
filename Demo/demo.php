<?php

require __DIR__ . '/cfg_fn.php';

$access_key = 'NDY5NTQwNjA4NTQ0NjIxNjI2OTQ0NDE1MTE3NTAzNjE1NTMxNzM2NDIxMzY2NDgyMTAyMjM2NjE4NDc1ODA4MQ==-rTWiZWhhQuhVvf7NIjavPzI5NIuWvDXB0Zv2kK8ipDly7edCab4B5pC+5kEv9Yde8Q/mCBTSbaAz8lxJIiUfWvHu6Z1n39WyyZ93jh2WoSSjSnjlaqgIH1MQvd8YdCCgzkxEI6iDzx3dkhsnfovcRuMsX98qAskaeNrALrUtr4cvmiZkPWNSFUTOqVNfrLDgdAzRbWoVslRzgqG30qM6+LbWGcYIRD3oz2+UDEkEry9s3ckwrI5FMu32nT5y8/rJ/6D4hnP+bPgHjD1MJeUMVsv1aTkN2jc/xeshetF/sI6fGZ/X';

$request = curl_request('http://dev.oobase.com/core/api.php', [
    'cmd' => 'user/user_acc,resume_online,keep_online,key_detail,user/user_output,get_user_list,get_credits,get_user_detail,get_user_info,get_user_menu',
    'map' => 'user/user_output/get_user_list/list:user_list',
], $access_key);

echo '<pre>';
echo json_encode($request);
