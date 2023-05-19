<?php

require '../func/func.php';

class AccountController extends Controller
{
	public function signinAction()
	{

		// test();
		if ($this->session->isAuthenticated()) {
			return $this->redirect('/room/index');
		}

		// このアクションで使用する項目の情報を指定して取得
	    $items = $this->getItemsInfo(array(
	                        'login_id',
	                        'password',
	                        ), USER_ITEMS_PROPERTY);

	    $items['login_id']['placeholder'] ='';
	    $items['password']['placeholder'] ='';

    	$inputs = $this->getInputs(array_keys($items), USER_ITEMS_PROPERTY);

		$this->smarty->assign('items', $items);
		$this->smarty->assign('inputs', $inputs);
		$this->smarty->assign('_token', $this->generateCsrfToken('account/signin'));
		$this->smarty->assign('title', 'ログイン');

		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');

	}

	public function authenticateAction()
	{
		if ($this->session->isAuthenticated()) {
			return $this->redirect('/');
		}

		$token = $this->request->getPost('_token');
		if (!$this->checkCsrfToken('account/signin', $token)) {
			return $this->redirect('/account/signin');
		}

		// このアクションで使用する項目の情報を指定して取得
	    $items = $this->getItemsInfo(array(
	                        'login_id',
	                        'password',
	                        ), USER_ITEMS_PROPERTY);
	    $items['login_id']['placeholder'] ='';
	    $items['password']['placeholder'] ='';

    	$inputs = $this->getInputs(array_keys($items), USER_ITEMS_PROPERTY);

		$user_repository = $this->db_manager->get('Admin_user');

		// バリデーション
		$items = $this->generateErrorMessage($items, $inputs);

		// 特権ユーザでのログイン成功時
		if (ROOT_USER_LOGIN_ID === $inputs['login_id']
			&& ROOT_PASSWORD === $inputs['password']) {
			
			$this->session->setAuthenticated(true);
			$this->session->set('login_user', array(
				'id' => ROOT_USER_ID,
				'login_id' => ROOT_USER_LOGIN_ID,
				'name' => ROOT_USER_NAME,
				'auth_type' => ROOT_USER_AUTH_TYPE,
			));

			return $this->redirect('/');				
		}

		if (count(array_column($items, 'err_msg')) === 0) {
			$user = $user_repository->fetchByLogin_id($inputs['login_id']);

			if (!$user || !password_verify($inputs['password'], $user['password'])) {
				// ★↓の1階層目のキーはlogin_idとpasswordどちらでも構わない
				$items['password']['err_msg'] = 'ログインIDかパスワードが不正です';
			} else {
				// DBで管理している管理者ユーザでのログイン成功時
				$this->session->setAuthenticated(true);
				$this->session->set('login_user', $user);

				return $this->redirect('/');		
			}
		}
		
		$this->smarty->assign('items', $items);
		$this->smarty->assign('inputs', $inputs);
		$this->smarty->assign('errors', array_column($items, 'err_msg'));
		$this->smarty->assign('_token', $this->generateCsrfToken('account/signin'));
		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/signin.html');
	}

	public function indexAction($param)
	{
		if (!$this->session->isAuthenticated()) {
			return $this->redirect('/');
		}

		// このアクションで使用する項目の情報を指定して取得
		$items = $this->getItemsInfo(array(
							'id',
							'name',
							'login_id',
							'auth_type',
							'create_date',
							'create_user_id',
							'update_date',
							'update_user_id',
							), USER_ITEMS_PROPERTY);

		$login_user = $this->session->get('login_user');
		$user_repository = $this->db_manager->get('Admin_user');
		$page = $this->request->getGet('page', 1);

		$users = $user_repository->fetchAllRow();

		// foreachの$cur_rowを参照渡しにしたら期待通りになった。なるほどね。
		foreach ($users as $row_no => &$cur_row) {
			// 日付データの表示形式を変更
			$cur_row = $this->convertDateDisplayType($cur_row, USER_ITEMS_PROPERTY);

			// コード値から表示値に変換
			$cur_row = $this->convertDispValue($cur_row, USER_ITEMS_PROPERTY);
			
		}

		if (!isset($param['page'])) {
			$param['page'] = 1;
		}

		$this->smarty->assign('login_user', $login_user);
		$this->smarty->assign('users', $users);
		$this->smarty->assign('items', $items);
		$this->smarty->assign('_token', $this->generateCsrfToken('account/index'));
		$this->smarty->assign('page', $page);
		$this->smarty->assign('title', 'アカウント一覧');		

		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');
	}

	public function signoutAction()
	{
		$this->session->clear();
		$this->session->setAuthenticated(false);

		return $this->redirect('/account/signin');
	}


	public function signupAction()
	{
		if (!$this->session->isAuthenticated()) {
			return $this->redirect('/');
		}

		// このアクションで使用する項目の情報を指定して取得
		$items = $this->getItemsInfo(array(
							'name',
							'login_id',
							'password',
							'auth_type',
							'note',
							), USER_ITEMS_PROPERTY);

		$inputs = $this->getInputs(array_keys($items), USER_ITEMS_PROPERTY);
		$_button = $this->request->getPost('button');

		$user_repository = $this->db_manager->get('Admin_user');
		
		$template = 'signup';

		switch ($_button) {
			case 'register':
				$template = 'signup_confirm';
				// バリデーション
				$items = $this->generateErrorMessage($items, $inputs);

				// login_idの重複チェックを追加
				if (!$user_repository->isUniqueLogin_id($inputs['login_id'])) {
					$items['login_id']['err_msg'] = "{$items['login_id']['alias']}は既に使用されています";

				}
		
				break;
			case 'back':

				break;
			case 'fixed':
				$template = 'signup_confirm';
				$login_user = $this->session->get('login_user');
				$user_repository->insert($login_user['id'], $inputs);
				break;
			default:
		}

		if (count(array_column($items, 'err_msg')) !== 0) {
			$template = 'signup';
			$_button = null;
		}

		// コード値を基に表示値を追加
		$inputs = $this->addDisplayValue($inputs, USER_ITEMS_PROPERTY);

		$this->smarty->assign('items', $items);
		$this->smarty->assign('inputs', $inputs);
		$this->smarty->assign('_button', $_button);
		$this->smarty->assign('errors', array_column($items, 'err_msg'));		
		$this->smarty->assign('_token', $this->generateCsrfToken('account/' . $template));
		$this->smarty->assign('title', 'アカウント登録');

        // ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $template . '.html');
	}


	public function updateAction($current)
	{
		if (!$this->session->isAuthenticated()) {
			return $this->redirect('/');
		}

		// 操作ユーザ情報取得
		$login_user = $this->session->get('login_user');
		
	    // このアクションで使用する項目の情報を取得
	    $items = $this->getItemsInfo(array(
							'name',
							'login_id',
							'auth_type',
	                        'note',
	                        ), USER_ITEMS_PROPERTY);

		$inputs = $this->getInputs(array_keys($items), USER_ITEMS_PROPERTY);

		$_button = $this->request->getPost('button');
		
		// ログイン中ユーザと操作対象ユーザが同じかを管理するflg。
		$_sameflg = false;
		if (intval($login_user['id']) === intval($current['id'])) {
			$_sameflg = true;
		}

		$user_repository = $this->db_manager->get('Admin_user');
		$template = 'update';

		switch ($_button) {
			case 'update':
		
				$template = 'update_confirm';

		        // バリデーション
		        $items = $this->generateErrorMessage($items, $inputs);

		        // login_idの重複チェックを追加
		        if (!$user_repository->isUniqueLogin_id($inputs['login_id'])
		    		&& $inputs['login_id'] !== $user_repository->fetchById($current['id'])['login_id']) {
		            $items['login_id']['err_msg'] = "{$items['login_id']['alias']}は既に使用されています";
		        }				
				break;
			case 'back':

				break;
			case 'fixed':
				$template = 'update_confirm';
				$user_repository->update($current['id'], $login_user['id'], $inputs);

				// ログインユーザと操作対象ユーザが同じ場合はセッション更新する
				if ($_sameflg) {
					$inputs = $user_repository->fetchById($current['id']);
					$this->session->set('login_user', $inputs);
				}
				break;
			default:
				// 初期表示時はDBからの情報を取得する。
				$template = 'update';
				$inputs = $user_repository->fetchById($current['id']);
		}

		if (count(array_column($items, 'err_msg')) !== 0) {
			$template = 'update';
			$_button = null;
		}
		
		// 日付データの表示形式を変更
		$inputs = $this->convertDateDisplayType($inputs, USER_ITEMS_PROPERTY);	

		// コード値を基に表示値を追加
		$inputs = $this->addDisplayValue($inputs, USER_ITEMS_PROPERTY);	

		$this->smarty->assign('id', $current['id']);
		$this->smarty->assign('items', $items);
		$this->smarty->assign('inputs', $inputs);
		$this->smarty->assign('_button', $_button);
		$this->smarty->assign('errors', array_column($items, 'err_msg'));		
		$this->smarty->assign('_token', $this->generateCsrfToken('account/' . $template));
		$this->smarty->assign('title', 'アカウント編集');

        // ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $template . '.html');
	}

	public function deleteAction($current)
	{
		if (!$this->session->isAuthenticated()) {
			return $this->redirect('/');
		}

		// 操作ユーザ情報取得
		$login_user = $this->session->get('login_user');

		// このアクションで使用する項目の情報を指定して取得
	    $items = $this->getItemsInfo(array(
							'name',
							'login_id',
							'auth_type',
	                        'note',
	                        ), USER_ITEMS_PROPERTY);

		$user_repository = $this->db_manager->get('Admin_user');
		$inputs = $user_repository->fetchById($current['id']);

		$_button = $this->request->getPost('button');
	
		// ログイン中ユーザと操作対象ユーザが同じかを管理するflg。
		$_sameflg = false;
		if (intval($login_user['id']) === intval($current['id'])) {
			$_sameflg = true;
		}

		switch ($_button) {
			case 'delete':
				break;
			case 'back':
				return $this->redirect('/');
				break;
			case 'fixed':
				$user_repository->delete($current['id']);

				// ログインユーザと操作対象ユーザが同じ場合は削除実行後にサインアウトさせる。
				if ($_sameflg) {
					return $this->redirect('/account/signout');
				}
				break;
			default:
		}

		// 日付データの表示形式を変更
		$inputs = $this->convertDateDisplayType($inputs, USER_ITEMS_PROPERTY);	

		// コード値を基に表示値を追加
		$inputs = $this->addDisplayValue($inputs, USER_ITEMS_PROPERTY);	

		$this->smarty->assign('items', $items);
		$this->smarty->assign('inputs', $inputs);
		$this->smarty->assign('_button', $_button);
		$this->smarty->assign('_sameflg', $_sameflg);
		$this->smarty->assign('_token', $this->generateCsrfToken('account/delete'));
		$this->smarty->assign('title', 'アカウント削除');

		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');
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


			// placeholderに入力可能文字数を記載する※予め設定されているplaceholderに上書きしてしまうことに留意。
			if (!empty($items[$col_name]['min_length'])) {
				$items[$col_name]['placeholder'] = $items[$col_name]['min_length'] . '文字以上';
			}
			$items[$col_name]['placeholder'] .= $items[$col_name]['max_length'] . '文字以内';		
		}

		// 'sort_order'の昇順でソート
		array_multisort(array_column($items, 'sort_order'), SORT_ASC, $items);

		return $items;
	}

    // /**
    //  * 指定したpostデータを取得
    //  */
	public function getInputs($col_names, $const_name)
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

		// login_id固有のバリデーション
		$col_name = 'login_id';
		if (!empty($items[$col_name])) {
			$property = USER_ITEMS_PROPERTY[$col_name];
			if ($property['max_length'] < strlen($inputs[$col_name])
					// 「!-~」の範囲の文字で始まり、
					// 「!-~」の範囲の文字を1回以上繰り返し、
					// 「!-~」の範囲の文字で終わる。
					|| !preg_match('/^[!-~]*$/', $inputs[$col_name])) {
				$items[$col_name]['err_msg'] = "{$property['alias']}は半角英数字{$property['max_length']}文字以下で入力してください";
			}
		}

		// バリデーションを追加したい場合はここに記述する

		return $items;
	}

}

