<?php

require 'core/ClassLoader.php';
require 'conf/config.php';
require 'conf/db_config.php';

$loader = new ClassLoader();
$loader->registerDir(dirname(__FILE__) . '/core');
$loader->registerDir(dirname(__FILE__) . '/models');
$loader->registerDir(dirname(__FILE__) . '/libs/smarty');
$loader->registerDir(dirname(__FILE__) . '/libs/log4php');

$loader->register();
