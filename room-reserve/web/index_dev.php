<?php

require '../bootstrap.php';
require '../RoomReserveApplication.php';
// echo dirname(dirname(__DIR__)) . '/room-mng/conf/db_config.php';
$app = new RoomReserveApplication(true);
$app->run();

