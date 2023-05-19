<?php

class RoomManageApplication extends Application
{

	// protected $login_action = array('controll_name', 'action_name');

	protected function registerRoutes()
	{
		return array(
			'/'
				=> array('controller' => 'account', 'action' => 'signin'),
			// '/account/signup'
			// 	=> array('controller' => 'account', 'action' => 'signup'),
			'/account/:action'	// このキーと値は少なくともsignin、signoutで使っとるみたい。
				=> array('controller' => 'account'),
			// '/account' 
			// 	=> array('controller' => 'account', 'action' => 'index'),
			// '/account/index' 
			// 	=> array('controller' => 'account', 'action' => 'index'),
			'/account/update/:id'
				=> array('controller' => 'account', 'action' => 'update'),
			'/account/delete/:id'
				=> array('controller' => 'account', 'action' => 'delete'),


			'/room' 
				=> array('controller' => 'room'),
			'/room/:action'
				=> array('controller' => 'room'),
			'/room/addRegister/:name'
				=> array('controller' => 'room', 'action' => 'addRegister'),
			'/room/indexByRoom/:name'
				=> array('controller' => 'room', 'action' => 'indexByRoom'),
			'/room/update/:id'
				=> array('controller' => 'room', 'action' => 'update'),

			'/price_type/list'
				=> array('controller' => 'price_type', 'action' => 'list'),

			'/reservelist/index'
				=> array('controller' => 'reservelist', 'action' => 'index'),

			'/customer/:action'
				=> array('controller' => 'customer'),
			'/customer/index/:page'
				=> array('controller' => 'customer', 'action' => 'index'),
		);
	}

	protected function configure()
	{
		$this->db_manager->connect('room-manage', array(
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