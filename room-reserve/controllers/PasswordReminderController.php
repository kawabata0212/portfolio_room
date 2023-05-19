<?php

class PasswordReminderController extends Controller
{

	public function inquiryAction()
	{
	
		// このアクションで使用する項目の情報を指定して取得
		$items = $this->getItemsInfo(array(
							'email',
							), CUSTOMER_USER_ITEMS_PROPERTY);
		$items['email']['placeholder'] = '';

		$inputs = $this->getInputs(array_keys($items));
		$_button = $this->request->getPost('button');

		$template = 'passwordChange';
		$errors = array();


		// 送信ボタン押下された場合
		if ('submit' === $_button) {

			// バリデーション
			$items = $this->generateErrorMessage($items, $inputs);

			// echo '<pre>';
			// var_dump($items);
			// echo '</pre>';

			$user_repository = $this->db_manager->get('CustomerUser');
			$user = $user_repository->fetchByEmail($inputs['email']);

			if (false === $user) {
				$items['email']['err_msg'] = 
				"該当する{$items['email']['alias']}は登録されていません";
			}

			// 入力されたemailがカスタマーテーブルに登録されている場合の処理
			if (0 === count(array_column($items, 'err_msg'))) {

				// auth_key生成
				$auth_key = bin2hex(random_bytes(32));

				// auth_key管理用テーブルにinsert
				$password_reissur_repository = $this->db_manager->get('PasswordReset');
				$password_reissur_repository->insert($user['id'], $auth_key);

				// 有効期限取得
				$password_reissur = $password_reissur_repository->fetchByAuthKey($auth_key);
				$expire_date = date('Y年m月d日　H時i分', strtotime($password_reissur['expire_date']));

				// メール送信　★★「24時間」の部分定数使いたい
				$msg = "{$user['name']} 様


				パスワード再設定リクエストを受け付けました。

				以下のURLから新しいパスワードをご設定ください。
				(このURLはパスワード再設定を完了するか、 {$expire_date} を過ぎると無効になります)

				https://{$this->request->getHost()}{$this->base_url}/passwordReminder/reset?auth_key={$auth_key}

				---------------------------------------------------------
				万が一、上記内容のお心当たりが無い場合は、
				他の方が誤ったメールアドレスを入力した可能性がございます。
				その場合、お手数ではございますが、本メールを削除していただけますと幸いです。
				---------------------------------------------------------
				";

				// echo dirname(__DIR__);
				// echo $this->request->getHost();
				// echo $this->base_url;

				$msg = str_replace("\t", '', $msg);

				mb_send_mail($user['email'],
							 'パスワード再設定リクエスト受付',
							 $msg,
							 $this->mail_headers);	

				return $this->redirect('/passwordReminder/sent');
			}
		}

		$this->smarty->assign('items', $items);
		$this->smarty->assign('inputs', $inputs);
		$this->smarty->assign('_button', $_button);
		$this->smarty->assign('errors', array_column($items, 'err_msg'));
		$this->smarty->assign('_token', $this->generateCsrfToken('account/' . $this->action_name));
		$this->smarty->assign('title', 'パスワードをお忘れの方');

        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');        
	}


	public function sentAction()
	{
	
		$this->smarty->assign('title', 'メールを送信しました');

        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');        
	}




	public function resetAction()
	{

		$auth_key = $this->request->getGet('auth_key');
		
		// トークンをキーにレコード取得
		$password_reset_repository = $this->db_manager->get('PasswordReset');
		$password_reset = $password_reset_repository->fetchByAuthKey($auth_key);

		$msg = '';

		// トークンの有効性チェック
		if (false === $password_reset) {
			$msg = '無効なパラメータです';
		} elseif (strtotime($password_reset['expire_date']) < strtotime(date("Y-m-d H:i:s"))) {
			$msg = 'ページ有効期限が切れています';
 		} elseif (1 === $password_reset['is_proccessd']) {
			$msg = '既にパスワード再設定済みです';
		}

		// トークンが無効の場合はその旨表示して処理終了
		if ('' !== $msg) {
			$this->smarty->assign('title', 'ページエラー');
			$this->smarty->assign('msg', $msg);

	        $this->smarty->display('../views/' . $this->controller_name . '/error.html');
	        exit();
		}

		
		// このアクションで使用する項目の情報を指定して取得
		$items = array('new_password' => '', 
					   'new_password_conf' => '');

		$inputs = $this->getInputs(array_keys($items));
		$_button = $this->request->getPost('button');
	
		$errors = array();

		switch ($_button) {
			// パスワード再設定ボタン押下時の処理
			case 'fixed':
				// バリデーション
				// 新しいパスワードをチェック
				$min_length = CUSTOMER_USER_ITEMS_PROPERTY['password']['min_length'];
				$max_length = CUSTOMER_USER_ITEMS_PROPERTY['password']['max_length'];

				if (0 === strlen($inputs['new_password'])) {
					$errors[] = '新しいパスワードを入力してください';
				} elseif ($inputs['new_password'] !== $inputs['new_password_conf']) {
					$errors[] = '新しいパスワードの入力内容が一致していません';
				} elseif ($min_length > strlen($inputs['new_password']) 
						  || $max_length < strlen($inputs['new_password']) ){
					$errors[] = "新しいパスワードは{$min_length}文字以上、{$max_length}文字以下で入力してください";
				}

				// バリデートで問題無ければ更新処理実行
				if (0 === count($errors)) {

					// パスワード再設定
					$user_repository = $this->db_manager->get('CustomerUser');
					$user_repository->updatePassword(
						$password_reset['customer_id'],
						$inputs['new_password']);

					// auth_key管理用テーブル更新
					$password_reset_repository->updateIsProccessd($auth_key);

				} else {
					$_button = null;
				}

				break;
			default:

		}

		if (count(array_column($items, 'err_msg')) !== 0) {
			$_button = null;
		}

		$this->smarty->assign('items', $items);
		$this->smarty->assign('inputs', $inputs);
		$this->smarty->assign('_button', $_button);
		$this->smarty->assign('errors', $errors);
		$this->smarty->assign('_token', $this->generateCsrfToken('account/' . $this->action_name));
		$this->smarty->assign('title', 'パスワード再設定');
		$this->smarty->assign('auth_key', $auth_key);

        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');        	
	}






    /**
     * 項目名をキーとして、定数の配列から多重連想配列を作る
     */
	public function getItemsInfo($col_names, $const_name)
	{

		$items = array();
		
		foreach ($col_names as $col_name) {
			// 配列型の定数から、各項目のプロパティを取得
			$items[$col_name] = $const_name[$col_name];


			// placeholderに入力可能文字数を記載する※既にplaceholderが設定されている場合は何もしない
			if (0 === strlen($items[$col_name]['placeholder'])) {
				if (!empty($items[$col_name]['min_length'])) {
					$items[$col_name]['placeholder'] = $items[$col_name]['min_length'] . '文字以上';
				}
				$items[$col_name]['placeholder'] .= $items[$col_name]['max_length'] . '文字以内';		
			}

		}

		// 'sort_order'の昇順でソート
		array_multisort(array_column($items, 'sort_order'), SORT_ASC, $items);

		return $items;
	}

    // /**
    //  * 指定したpostデータを取得
    //  */
	public function getInputs($col_names)
	{
		$inputs = array();

		foreach ($col_names as $col_name) {
			$inputs[$col_name] = $this->request->getPost($col_name, '');

		}
		
		return $inputs;
	}

    /**
     * バリデーションとエラーメッセージ生成処理。
     * 全画面で共通のバリデーションのみ記載する。
     * 画面固有のバリデーションは各アクション内に記述する
     */
	// 
	public function generateErrorMessage($items, $inputs)
	{
		// $itemsで受け取った項目のバリデーション
		foreach ($items as $col_name => $propertys) {
			if (true === $propertys['is_required']
				&& 0 === strlen($inputs[$col_name])) {
				$items[$col_name]['err_msg'] = "{$propertys['alias']}を入力してください";

			} elseif (true === $propertys['is_inputtable']
					  && ('text' === $propertys['item_type']	// 入力可能項目、且つ、入力項目(=選択項目ではない)の場合は文字数チェックする
					  	 || 'password' === $propertys['item_type']
					  	 || 'textarea' === $propertys['item_type'])) {
				if ('' !== $propertys['min_length']) {
					if (strlen($inputs[$col_name]) < $propertys['min_length']
						  || strlen($inputs[$col_name]) > $propertys['max_length']) {
						$items[$col_name]['err_msg'] = "{$propertys['alias']}は{$propertys['min_length']}文字以上、{$propertys['max_length']}文字以下で入力してください";

					}
				} elseif (strlen($inputs[$col_name]) > $propertys['max_length']) {
					$items[$col_name]['err_msg'] = "{$propertys['alias']}は{$propertys['max_length']}文字以下で入力してください";

				}

			}

		}

		// email固有のバリデーション
		$col_name = 'email';
		if (!empty($items[$col_name]) && !empty($inputs[$col_name])) {
			$property = CUSTOMER_USER_ITEMS_PROPERTY[$col_name];
			// 自己流
			// if (!preg_match('/[!-~]+[@][!-~]+[.][^.]$/', $inputs[$col_name])) {

			// // https://magazine.techacademy.jp/magazine/29451
			// バリデーションに使う正規表現
			// $pattern = "/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:.[a-zA-Z0-9-]+)*$/";

			// if (preg_match($pattern, $inputs[$col_name])) {			
			// 	$items[$col_name]['err_msg'] = "{$property['alias']}は規定の形式で入力してください";
			// }

			if (!filter_var($inputs[$col_name], FILTER_VALIDATE_EMAIL)){
				$items[$col_name]['err_msg'] = "{$property['alias']}の形式が正しくありません";
			} // else {
			// 	$items[$col_name]['err_msg'] = "{$property['alias']}正しい形式です☻";
			// }

		}

		// フリガナ固有のバリデーション
		$col_name = 'furigana';
		if (!empty($items[$col_name]) && !empty($inputs[$col_name])) {
			$property = CUSTOMER_USER_ITEMS_PROPERTY[$col_name];
				// スペースも許容したいので↓のスペースは削除しないこと！
			if (50 < strlen($inputs[$col_name])
				|| !preg_match('/^[ァ-ヴ 　]+$/u', $inputs[$col_name])) {
				$items[$col_name]['err_msg'] = "{$property['alias']}は全角カタカナ50文字以内で入力してください";
			}
		}

		// 電話番号固有のバリデーション
		$col_name = 'tel';
		if (!empty($items[$col_name]) && !empty($inputs[$col_name])) {
			$property = CUSTOMER_USER_ITEMS_PROPERTY[$col_name];
			if (15 < strlen($inputs[$col_name])
				|| !preg_match('/^[0-9]+$/', $inputs[$col_name])) {
				$items[$col_name]['err_msg'] = "{$property['alias']}は半角数字15文字以内で入力してください";
			}
		}

		// パスワード固有のバリデーション
		// $col_name = 'password';
		// if (!empty($items[$col_name]) && !empty($inputs[$col_name])) {
		// 	$property = CUSTOMER_USER_ITEMS_PROPERTY[$col_name];
		// 	if (15 < strlen($inputs[$col_name])
		// 		|| !preg_match('/^[0-9]+$/', $inputs[$col_name])) {
		// 		$items[$col_name]['err_msg'] = "{$property['alias']}はハイフン無し15文字以内で入力してください";
		// 	}
		// }

		// 郵便番号固有のバリデーション
		$col_name = 'post_number';
		if (!empty($items[$col_name]) && !empty($inputs[$col_name])) {
			$property = CUSTOMER_USER_ITEMS_PROPERTY[$col_name];
			if (7 !== strlen($inputs[$col_name])
				|| !preg_match('/^[0-9]+$/', $inputs[$col_name])) {

				$items[$col_name]['err_msg'] = "{$property['alias']}は半角数字7文字で入力してください";
			}
		}

		// バリデーションを追加したい場合はここに記述する

		return $items;
	}

}
