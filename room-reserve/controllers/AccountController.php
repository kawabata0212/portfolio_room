<?php

class AccountController extends Controller
{

	public function passwordChangeAction()
	{

		if (!$this->session->isAuthenticated()) {
			return $this->redirect('/');
		}
		
		// このアクションで使用する項目の情報を指定して取得
		// $items = $this->getItemsInfo(array(
		// 					'password',
		// 					), CUSTOMER_USER_ITEMS_PROPERTY);

		// cur_password, new_password, new_password_confを作る
		$items = array('cur_password' => '',
					   'new_password' => '', 
					   'new_password_conf' => '');

		$inputs = $this->getInputs(array_keys($items));
		$_button = $this->request->getPost('button');

		$user_repository = $this->db_manager->get('CustomerUser');
	
		$template = 'passwordChange';
		$errors = array();

		switch ($_button) {
			case 'back':

				break;
			case 'fixed':
				// バリデーション
				$id = $this->session->get('login_user')['id'];

				// 現在のパスワードをチェック
				$user = $user_repository->fetchById($id);
				if (!password_verify($inputs['cur_password'], $user['password'])) {
					$errors[] = '現在のパスワードが正しくありません';
				}
				
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

				if (0 === count($errors)) {
					$user_repository->updatePassword(
						$id,
						$inputs['new_password']);
				} else {
					$_button = null;
				}

				break;
			default:
				// 初期表示時はDBからの情報を取得する。
				// $template = 'passwordChange';
				// $inputs = $this->session->get('login_user');
		}

		if (count(array_column($items, 'err_msg')) !== 0) {
			$template = 'passwordChange';
			$_button = null;
		}

		$this->smarty->assign('items', $items);
		$this->smarty->assign('inputs', $inputs);
		$this->smarty->assign('_button', $_button);
		$this->smarty->assign('errors', $errors);
		$this->smarty->assign('_token', $this->generateCsrfToken('account/' . $this->action_name));
		$this->smarty->assign('title', 'パスワード変更');

		// echo '<pre>';
		// var_dump($inputs);
		// echo '</pre>';

        // ビューファイル呼び出し
        // $this->smarty->display('../views/' . $this->controller_name . '/' . $template . '.html');

        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');        
	}


	public function signupAction()
	{

		if ($this->session->isAuthenticated()) {
			return $this->redirect('/');
		}
		
		// このアクションで使用する項目の情報を指定して取得
		$items = $this->getItemsInfo(array(
							// 'id',
							'name',
							'furigana',
							'tel',
							'email',
							'password',
							'post_number',
							'pref',
							'city',
							'address1',
							'address2',
							// 'note',
							// 'create_date',
							// 'create_user_id',
							// 'update_date',
							// 'update_user_id'
							), CUSTOMER_USER_ITEMS_PROPERTY);

		$inputs = $this->getInputs(array_keys($items));
		$_button = $this->request->getPost('button');

		$user_repository = $this->db_manager->get('CustomerUser');
		$city_repository = $this->db_manager->get('City');

		$prefs_tmp = $city_repository->fetchAllPrefGroup();
		foreach ($prefs_tmp as $pref_tmp) {
			$prefs[$pref_tmp['id']] = $pref_tmp['value'];
		}
		$items['pref']['ref_array'] = $prefs;

		// $citys_temp = $city_repository->fetchAllRow();
		// $items['city']['ref_array'] = $citys;
		// echo '<pre>';
		// var_dump($prefs);
		// echo '</pre>';
		
		$template = 'signup';

		switch ($_button) {
			case 'register':
				$template = 'signup_confirm';
				// バリデーション
				$items = $this->generateErrorMessage($items, $inputs);

				// emailの重複チェックを追加
				if (!$user_repository->isUniqueEmail($inputs['email'])) {
					$items['email']['err_msg'] = "{$items['email']['alias']}は既に使用されています";

				}
		
				break;
			case 'back':

				break;
			case 'fixed':
				$template = 'signup_confirm';
				$user_repository->insert($inputs);

				// 登録完了メールを送信
				$msg = "{$inputs['name']} 様\r\n\r\nアカウント登録が完了いたしました。";
				// ★メアド。後で定数に置き換える。
				$headers = 'From: ●●●';

				mb_send_mail($inputs['email'],
							 'アカウント登録完了',
							 $msg,
							 $this->mail_headers);

				// id列の値をセッションに格納するためにDBから値取得
				$inputs = $user_repository->fetchByEmail($inputs['email']);;

				$this->session->setAuthenticated(true);
				$this->session->set('login_user', $inputs);

				return $this->redirect('/');				
				// break;
			default:
		}

		if (count(array_column($items, 'err_msg')) !== 0) {
			$template = 'signup';
			$_button = null;

			$citys_tmp = $city_repository->fetchAllRow();
			foreach ($citys_tmp as $city_tmp) {
				$citys[$city_tmp['id']] = $city_tmp['value'];
			}
			$items['city']['ref_array'] = $citys;			
		}

		$this->smarty->assign('items', $items);
		$this->smarty->assign('inputs', $inputs);
		$this->smarty->assign('_button', $_button);
		$this->smarty->assign('errors', array_column($items, 'err_msg'));
		$this->smarty->assign('_token', $this->generateCsrfToken('account/' . $template));
		$this->smarty->assign('title', 'アカウント登録');

        // ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $template . '.html');
	}




	public function signinAction()
	{
		// if ($this->session->isAuthenticated()) {
		// 	return $this->redirect('/room/index');
		// }

		// このアクションで使用する項目の情報を指定して取得
	    $items = $this->getItemsInfo(array(
	                        'email',
	                        'password',
	                        ), CUSTOMER_USER_ITEMS_PROPERTY);

	    // $items['email']['placeholder'] ='example@mail.com';
	    $items['email']['placeholder'] ='';
	    $items['password']['placeholder'] ='';

    	$inputs = $this->getInputs(array_keys($items));

		$this->smarty->assign('items', $items);
		$this->smarty->assign('inputs', $inputs);
		$this->smarty->assign('_token', $this->generateCsrfToken('account/signin'));
		$this->smarty->assign('title', 'ログイン');

		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');

	}

	public function authenticateAction()
	{
		// if ($this->session->isAuthenticated()) {
		// 	return $this->redirect('/');
		// }

		$token = $this->request->getPost('_token');
		if (!$this->checkCsrfToken('account/signin', $token)) {
			return $this->redirect('/account/signin');
		}

		// このアクションで使用する項目の情報を指定して取得
	    $items = $this->getItemsInfo(array(
	                        'email',
	                        'password',
	                        ), CUSTOMER_USER_ITEMS_PROPERTY);
	    $items['email']['placeholder'] ='';
	    $items['password']['placeholder'] ='';

    	$inputs = $this->getInputs(array_keys($items));

		$user_repository = $this->db_manager->get('CustomerUser');

		// バリデーション
		$items = $this->generateErrorMessage($items, $inputs);

		if (count(array_column($items, 'err_msg')) === 0) {
			$user = $user_repository->fetchByEmail($inputs['email']);

			if (!$user || !password_verify($inputs['password'], $user['password'])) {
				// ★↓の1階層目のキーはemailとpasswordどちらでも構わない
				$items['password']['err_msg'] = 'メールアドレスかパスワードが正しくありません';
			} else {
				$this->session->setAuthenticated(true);
				$this->session->set('login_user', $user);

				return $this->redirect('/');		
			}
		}
		
		$this->smarty->assign('items', $items);
		$this->smarty->assign('inputs', $inputs);
		$this->smarty->assign('errors', array_column($items, 'err_msg'));
		$this->smarty->assign('_token', $this->generateCsrfToken('account/signin'));
		$this->smarty->assign('title', 'ログイン');

		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/signin.html');
	}


	public function signoutAction()
	{
		$this->session->clear();
		$this->session->setAuthenticated(false);

		return $this->redirect('/');
	}


	public function updateAction()
	{

		if (!$this->session->isAuthenticated()) {
			return $this->redirect('/');
		}
		
		// このアクションで使用する項目の情報を指定して取得
		$items = $this->getItemsInfo(array(
							'id',
							'name',
							'furigana',
							'tel',
							'email',
							// 'password',
							'post_number',
							'pref',
							'city',
							'address1',
							'address2',
							// 'note',
							// 'create_date',
							// 'create_user_id',
							// 'update_date',
							// 'update_user_id'
							), CUSTOMER_USER_ITEMS_PROPERTY);
		$items['id']['edit_type'] = 'invisible';

		$user_id = $this->session->get('login_user')['id'];

		$inputs = $this->getInputs(array_keys($items));
		$_button = $this->request->getPost('button');

		$user_repository = $this->db_manager->get('CustomerUser');
		$city_repository = $this->db_manager->get('City');

		$prefs_tmp = $city_repository->fetchAllPrefGroup();
		foreach ($prefs_tmp as $pref_tmp) {
			$prefs[$pref_tmp['id']] = $pref_tmp['value'];
		}
		$items['pref']['ref_array'] = $prefs;

		$citys_tmp = $city_repository->fetchAllRow();
		foreach ($citys_tmp as $city_tmp) {
			$citys[$city_tmp['id']] = $city_tmp['value'];
		}
		$items['city']['ref_array'] = $citys;	
		
		$template = 'update';

		switch ($_button) {
			case 'update':
				$template = 'update_confirm';
				// バリデーション
				$items = $this->generateErrorMessage($items, $inputs);

				// emailの重複チェックを追加
				if (!$user_repository->isUniqueEmail($inputs['email'])
					&& $inputs['email'] !== $this->session->get('login_user')['email']){
					$items['email']['err_msg'] = "{$items['email']['alias']}は既に使用されています";

				}
		
				break;
			case 'back':

				break;
			case 'fixed':
				$template = 'update_confirm';
				$user_repository->update($inputs);

				// $this->session->setAuthenticated(true);
				$this->session->set('login_user', $inputs);

				break;
			default:
				// 初期表示時はDBからの情報を取得する。
				$template = 'update';
				$inputs = $user_repository->fetchById($user_id);
		}

		// echo '<pre>';
		// var_dump($inputs);
		// var_dump($items);
		// echo '</pre>';


		if (count(array_column($items, 'err_msg')) !== 0) {
			$template = 'update';
			$_button = null;
		}

		$this->smarty->assign('items', $items);
		$this->smarty->assign('inputs', $inputs);
		$this->smarty->assign('_button', $_button);
		$this->smarty->assign('errors', array_column($items, 'err_msg'));
		$this->smarty->assign('_token', $this->generateCsrfToken('account/' . $template));
		$this->smarty->assign('title', 'ユーザ情報編集');

        // ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $template . '.html');
	}











    // /**
    //  * 日付データの表示形式を変換する
    //  */
	public function convertDateDisplayType ($cur_row, $const_name)
	{
		foreach ($cur_row as $col_name => $value) {
			if ('' !== $const_name[$col_name]['date_format']
				&& !empty($value)) {
				$cur_row[$col_name] = date($const_name[$col_name]['date_format'], strtotime($value));
			}
		}

		return $cur_row;
	}

    /**
     * セレクトボックスの値をコード値から表示値に変換する
     */
	public function convertDispValue($cur_row, $const_name)
	{
	    foreach ($cur_row as $col_name => $value) {
	        if ('' !== $const_name[$col_name]['ref_const_name']
	            && !empty($value)) {
	            $cur_row[$col_name] = constant($const_name[$col_name]['ref_const_name'])[$value];
	        }
	    }

	    return $cur_row;
	}

    /**
     * コード値を基に表示値を追加。※セレクトボックスやラジオボタン項目
     */
	public function addDisplayValue($cur_row, $const_name)
	{
	    foreach ($cur_row as $col_name => $value) {
	        if ('' !== $const_name[$col_name]['ref_const_name']
	            && !empty($value)) {
	            $cur_row[$col_name . '_display_value'] = constant($const_name[$col_name]['ref_const_name'])[$value];
	        }
	    }

	    return $cur_row;
	}

    // /**
    //  * 項目名をキーとして、定数の配列から多重連想配列を作る
    //  */
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
		if (!empty($items[$col_name])) {
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
