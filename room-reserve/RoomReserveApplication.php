<?php

class RoomReserveApplication extends Application
{

	protected function registerRoutes()
	{
		return array(
			'/'
				=> array('controller' => 'toppage', 'action' => 'index'),
				// => array('controller' => 'account', 'action' => 'index'),
				// => array('controller' => 'account', 'action' => 'signin'),
				// => array('controller' => 'account', 'action' => 'signup'),

			'/toppage/:action'
				=> array('controller' => 'toppage', ),

			'/account/:action'
				=> array('controller' => 'account', ),

			'/room/:action'
				=> array('controller' => 'room', ),
			'/room/detail/:room_name'
				=> array('controller' => 'room', 'action' => 'detail'),
			'/room/reserveCancel/:reserve_id'
				=> array('controller' => 'room', 'action' => 'reserveCancel'),

			'/withdrawal/:action'
				=> array('controller' => 'withdrawal', ),

			'/passwordReminder/:action'
				=> array('controller' => 'passwordReminder', ),
		);
	}

	protected function configure()
	{
		$this->db_manager->connect('room-reserve', array(
			'dsn' => DB_DSN,
			'user' => DB_USER,
			'password' => DB_PASSWORD,
		));
	}

	public function getRootDir()
	{
		return dirname(__FILE__);
	}

}

