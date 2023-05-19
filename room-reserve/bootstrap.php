<?php
require dirname(__DIR__) . '/room-mng/core/ClassLoader.php';
// require 'conf/config.php'; configはroom-mngサイトのものだけを読み込む
// echo(dirname(__DIR__));
require dirname(__DIR__) . '/room-mng/conf/config.php';
require dirname(__DIR__) . '/room-mng/conf/db_config.php';
require dirname(__DIR__) . '/room-mng/func/func.php';

$loader = new ClassLoader();
$loader->registerDir(dirname(__DIR__) . '/room-mng/core');
$loader->registerDir(dirname(__FILE__) . '/models');
$loader->registerDir(dirname(__DIR__) . '/room-mng/libs/smarty');
$loader->registerDir(dirname(__DIR__) . '/room-mng/models');

$loader->register();
