<?php
/*declare(ticks=1);
pcntl_signal(SIGINT, function () {
    exit("Get signal SIGINT and exit\n");
});

echo "Ctl + c or run cmd : kill -SIGINT " . posix_getpid(). "\n" ;

while (1) {
    $num = 1;
}*/
echo "<pre>";
$doc = simplexml_load_file('test.xml');
print_r($doc);
$doc1 = json_encode($doc);
$doc2 = json_decode($doc1,true);
print_r($doc2);