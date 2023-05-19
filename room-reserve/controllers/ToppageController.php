<?php

class ToppageController extends Controller
{

	public function indexAction($params)
	{
		$this->smarty->assign('title', 'TOPページ');
		
		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');
	}

	public function overviewAction($params)
	{
		$this->smarty->assign('title', '概要');
		
		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');
	}

	public function contactusAction($params)
	{
		$this->smarty->assign('title', 'お問い合わせ');
		
		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');
	}

}
