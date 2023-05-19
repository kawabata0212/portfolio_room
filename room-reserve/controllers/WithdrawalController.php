<?php

class WithdrawalController extends Controller
{

	public function confirmAction($params)
	{
		$this->smarty->assign('title', '退会フォーム');
		
		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');
	}


	public function fixedAction($params)
	{

        $userid = $this->session->get('login_user')['id'];
		$user_repository = $this->db_manager->get('CustomerUser');
        $user = $user_repository->fetchById($userid);

		// 退会完了をメールで通知
		$msg = "{$user['name']} 様\r\n\r\n退会処理が完了いたしました。\r\nこれまでご愛顧いただき、誠にありがとうございました。";
		// ★メアド。後で定数に置き換える。
		$headers = 'From: ●●●';

		// ★メアド。後で定数に置き換える。
		mb_send_mail($user['email'] . ',●●● ',
					 '退会処理完了',
					 $msg,
					 $headers);		

		// DBの内容をクリア
		$user_repository->updateForWithdrawal($userid);

		// 予約済データをキャンセルに更新
		$this->db_manager->get('Reserve')->updateCauseWithdrawal($userid);

		// サインアウト
		return $this->redirect('/account/signout');
	}


}
