<?php
/*require_once 'core/_include/cfg.php';
load_lib('core','db_redis');
$redis = \db_redis::connect();
var_dump($redis);*/
echo file_get_contents("http://www.oo.com/api.php?format=json&cmd=fruit_picker/picker&color=yellow&smell=sweet");
