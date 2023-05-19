<?php

class RoomController extends Controller
{

	public function indexAction($params)
	{
		if (!$this->session->isAuthenticated()) {
			return $this->redirect('/');
		}

		// このアクションの検索条件以外で使用する項目の情報を指定して取得
		$items = $this->getItemsInfo(array(
							'room_img',
							'name',
							), ROOM_ITEMS_PROPERTY);

		// このアクションの検索条件で使用する項目の情報を指定して取得
		$search_items = $this->getItemsInfo(array(
							'type',
							'status',
							'smoking_allowed_flg',
							'pet_allowed_flg',
							'barrier_free_flg',
							), ROOM_ITEMS_PROPERTY);

		// echo '<pre>';
		// var_dump($_GET);
		// echo '</pre>';

		$login_user = $this->session->get('login_user');
		$room_repository = $this->db_manager->get('RoomOptions');
		$page = $this->request->getGet('page', 1);


		// $search_conditions = array();
		// foreach($_GET as $key => $value) {
		// 	if (preg_match('/^search/', $key)) {
		// 		$search_conditions[$key] = $value;
		// 	}
		// }

		$search_conditions['type'] = $this->request->getGet('searchValue-type', '');
		$search_conditions['status'] = $this->request->getGet('searchValue-status', '');
		$search_conditions['smoking_allowed_flg'] = $this->request->getGet('searchValue-smoking_allowed_flg', '');
		$search_conditions['pet_allowed_flg'] = $this->request->getGet('searchValue-pet_allowed_flg', '');
		$search_conditions['barrier_free_flg'] = $this->request->getGet('searchValue-barrier_free_flg', '');
		$search_conditions['note'] = $this->request->getGet('searchValue-note', '');

		// echo '<pre>';
		// var_dump($search_conditions);
		// echo '</pre>';		

		$rooms = $room_repository->fetchAllGoupByName($search_conditions);

		// 画像ファイル一覧を取得
		$file_paths = glob('upload/*');

		// ★愛犬画像表示用。学習意欲維持の為だけ。
		if (!empty($this->request->getGet('dog'))) {
			$file_paths = glob('upload_fav/*');
		}

		// foreachの$cur_rowを参照渡しにしたら期待通りになった。なるほどね。
		foreach ($rooms as $row_no => &$cur_row) {

			// 画像パスを連想配列に新規追加
			$result = preg_grep('/' . $cur_row['name'] . '\..*/' , $file_paths);
			$cur_row['room_img'] = array_shift($result); 
		}

		$this->smarty->assign('login_user', $login_user);   // viewに渡す値。
		$this->smarty->assign('rooms', $rooms);
		$this->smarty->assign('items', $items);
		$this->smarty->assign('_token', $this->generateCsrfToken('room/index'));
		$this->smarty->assign('page', $page);
		$this->smarty->assign('search_conditions', $search_conditions);

		$this->smarty->assign('title', '部屋一覧');
		
		$this->smarty->assign('search_items', $search_items);

		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');
	}

	public function indexByRoomAction($params)
	{
		if (!$this->session->isAuthenticated()) {
			return $this->redirect('/');
		}

		// echo '<pre>';
		// var_dump($params);
		// echo '</pre>';

		// このアクションで使用する項目の情報を指定して取得
		$items = $this->getItemsInfo(array(
							'id',
							'room_id',
							'room_img',
							'name',
							'type',
							'price_type_code',
							'status',
							'smoking_allowed_flg',
							'pet_allowed_flg',
							'barrier_free_flg',
							// 'note',
							'apply_start_date',
							'apply_end_date',
							), ROOM_ITEMS_PROPERTY);

		foreach ($items as $col_name => $propertys) {
			if ($col_name !== 'apply_start_date' 
				&& $col_name !== 'apply_end_date'
				&& $propertys['edit_type'] !== 'invisible') {
				$items[$col_name]['edit_type'] = 'readonly';	
			}
		}
		$items['name']['edit_type'] = 'invisible';
		$items['room_img']['edit_type'] = 'invisible';

		$_button = $this->request->getPost('button');
		$keyword = $this->request->getGet('keyword', null);

		// $msg = array();
		$msg['room_img'] = '';
		$msg['apply_date'] = '';

		$login_user = $this->session->get('login_user');
		$room_options_repository = $this->db_manager->get('RoomOptions');

		// $statuses = array(1); ★多分不要。

		// $rooms = $room_options_repository->fetchAllByName($params['name'], $keyword, $statuses); ★多分不要
		$rooms = $room_options_repository->fetchAllByName($params['name']);

		for ($i = 0; $i < count($rooms); $i++) {
			$inputs[$i]['id'] = $this->request->getPost('id' . strval($i));
			$inputs[$i]['apply_start_date'] = $this->request->getPost('apply_start_date' . strval($i));
			$inputs[$i]['apply_end_date'] = $this->request->getPost('apply_end_date' . strval($i));
		}

		// 画像ファイル一覧を取得
		$file_paths = glob('upload/*');

		// 画像パスを連想配列に新規追加
		$result = preg_grep('/' . $params['name'] . '\..*/' , $file_paths);
		$img_path = array_shift($result); 

		foreach ($rooms as $row_no => &$cur_row) {
			// 日付データの表示形式を変更
			$cur_row = $this->convertDateDisplayType($cur_row, ROOM_ITEMS_PROPERTY);

			// コード値から表示値に変換
			$cur_row = $this->convertDispValue($cur_row, ROOM_ITEMS_PROPERTY);

		}

		// echo '<pre>';
		// var_dump($inputs);
		// echo '</pre>';

		switch ($_button) {
			case 'img-fixed':

				// ファイルが選択されている場合はファイル情報取得
				if (0 !== strlen($_FILES['room_img']['name'])) {
					// ファイルデータ取得
					$room_img_data = base64_encode(file_get_contents($_FILES['room_img']['tmp_name']));
					// ファイル種類取得
					$room_img_type = $_FILES['room_img']['type'];
					// ファイルの拡張子取得
					$room_img_extension = pathinfo($_FILES['room_img']['name'], PATHINFO_EXTENSION);
				// 					echo '<pre>';
				// var_dump($_FILES);echo '</pre>';
				} else { // ファイル未選択の場合は登録済み画像データを取得
					// ファイルデータ取得
					$room_img_data = base64_encode(file_get_contents($img_path));
					// ファイル種類取得
					$room_img_type = mime_content_type($img_path);
					// ファイルの拡張子取得
					$room_img_extension = pathinfo($img_path, PATHINFO_EXTENSION);
				}

				// 更新前後のファイルデータが異なる場合は画像差し替える
				if ($room_img_data !== base64_encode(file_get_contents($img_path))) {
					// 更新前ファイルの削除
					$result = preg_grep('/' . $params['name'] . '\..*/' , $file_paths);
					$img_path = array_shift($result);
					unlink($img_path);

					// 画像ファイルを新規登録
					$upload_dir = 'upload/';
					$upload_file = $upload_dir . $params['name'] . '.' . $room_img_extension;
					file_put_contents($upload_file, base64_decode($room_img_data), LOCK_EX);

					$msg['room_img'] = $items['room_img']['alias'] . 'を更新しました。';
				} else {
					// $_button = null;
					$items['room_img']['err_msg'] = $items['room_img']['alias'] . 'を選択してから確定ボタンをクリックしてください。';
				}

				break;

			case 'fixed':
				$template = 'register_confirm';

				// バリデーション
				for ($i = 1; $i < count($inputs); $i++) {
					if (strtotime($inputs[$i - 1]['apply_start_date']) >= strtotime($inputs[$i]['apply_start_date'])) {
						$items['apply_start_date']['err_msg'] 
							= $items['apply_start_date']['alias'] 
							  . 'は前の行よりも後ろの日付を指定してください';
						break;
					}
					
					if ($inputs[$i - 1]['apply_end_date'] >= $inputs[$i]['apply_start_date']) {
						$items['apply_end_date']['err_msg'] 
							= $items['apply_end_date']['alias'] 
							  . 'は前の行よりも後ろの日付を指定してください';
						break;
					}

				}

				if (0 === count(array_column($items, 'err_msg'))) {
					// 操作ユーザ情報取得
					$login_user = $this->session->get('login_user');
		
					// 適用開始日、適用終了日について、更新前後で値が異なる行数をカウント
					$cnt = 0;
					for ($i = 0; $i < count($rooms); $i++) {
						if (strtotime($rooms[$i]['apply_start_date']) !== strtotime($inputs[$i]['apply_start_date'])
							|| strtotime($rooms[$i]['apply_end_date']) !== strtotime($inputs[$i]['apply_end_date'])) {

							$room_options_repository->updateApplydate($inputs[$i]['id'], $login_user['id'], $inputs[$i]['apply_start_date'], $inputs[$i]['apply_end_date']);
							$cnt++;
						}
					}					

					if ($cnt === 0) {
						// $_button = null;
						$items['apply_start_date']['err_msg'] = $items['apply_start_date']['alias'] . 'または' . $items['apply_end_date']['alias'] . 'を指定してから確定ボタンをクリックしてください。';

					} else {
						$msg['apply_date'] = $items['apply_start_date']['alias'] . '、' . $items['apply_end_date']['alias'] . 'を更新しました。';
					}

					$rooms = $room_options_repository->fetchAllByName($params['name']);
					foreach ($rooms as $row_no => &$cur_row) {
						// 日付データの表示形式を変更
						$cur_row = $this->convertDateDisplayType($cur_row, ROOM_ITEMS_PROPERTY);

						// コード値から表示値に変換
						$cur_row = $this->convertDispValue($cur_row, ROOM_ITEMS_PROPERTY);

					}
				}
				
				break;
			default:

				break;
		}
	
		$this->smarty->assign('login_user', $login_user);
		$this->smarty->assign('rooms', $rooms);
		$this->smarty->assign('maxid', array_pop($rooms)['id']);
		$this->smarty->assign('items', $items);
		$this->smarty->assign('_button', $_button);		
		$this->smarty->assign('errors', array_column($items, 'err_msg'));		
		$this->smarty->assign('_token', $this->generateCsrfToken('room/indexByName'));
		// $this->smarty->assign('title', "部屋別情報一覧({$items['name']['alias']}：{$params['name']})" );
		$this->smarty->assign('title', '部屋別情報一覧');
		$this->smarty->assign('name', $params['name']);
		$this->smarty->assign('img_path', $img_path);
		$this->smarty->assign('msg', $msg);
		$this->smarty->assign('keyword', $keyword);

		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');
	}


	public function registerAction()
	{
		if (!$this->session->isAuthenticated()) {
			return $this->redirect('/');
		}

		// このアクションで使用する項目の情報を指定して取得
		$items = $this->getItemsInfo(array(
							'room_img',
							'name',
							'type',
							'price_type_code',
							'status',
							'smoking_allowed_flg',
							'pet_allowed_flg',
							'barrier_free_flg',
							'note',
							'apply_start_date',
							'apply_end_date',
							), ROOM_ITEMS_PROPERTY);

		$items['room_img']['edit_type'] = 'editable';
		$items['name']['edit_type'] = 'editable';
		$items['apply_end_date']['edit_type'] = 'readonly';
		
		$inputs = $this->getInputs(array_keys($items), ROOM_ITEMS_PROPERTY);
		$inputs['apply_end_date'] = ROOM_APPLY_END_DATE;

		$_button = $this->request->getPost('button');

		// price_type情報を取得して成型
		$price_type_repository = $this->db_manager->get('Price_type');		
		$price_type = $price_type_repository->fetchAllRow();
		foreach ($price_type as $row => $col_name) {
			$price_type_formatted[$col_name['code']] = $col_name['code'];
		}
		$items['price_type_code']['ref_var_name'] = 'price_type_formatted';

		// var_dump($price_type_formatted);

		// 適用開始日の初期値を本日日付にする
		if (empty($inputs['apply_start_date'])) {
			$now = new Datetime();
			$inputs['apply_start_date'] = $now->format('Y-m-d H:i:s');
		}

		$room_repository = $this->db_manager->get('Room');
		$room_options_repository = $this->db_manager->get('RoomOptions');
		
		$template = 'register';

		$upload_file = '';
		$files = array();

		$room_img_data = $this->request->getPost('room_img_data', '');
		$room_img_type = $this->request->getPost('room_img_type', '');
		$room_img_extension = $this->request->getPost('room_img_extension', '');

		switch ($_button) {
			case 'register':
				$template = 'register_confirm';

				// バリデーション
				$items = $this->generateErrorMessage($items, $inputs);
				// バリデーション追加
				if ($inputs['apply_start_date'] > $inputs['apply_end_date']) {
					$items['apply_end_date']['err_msg'] 
						= $items['apply_start_date']['alias'] 
						  . 'には' 
						  . $items['apply_end_date']['alias'] 
						  . '以前の日付を指定してください';	
				}

				// nameの重複チェックを追加
				if (!$room_repository->isUniquename($inputs['name'])) {
					$items['name']['err_msg'] = "{$items['name']['alias']}は既に使用されています";

				}

		        // 画像の登録チェック
		        if (0 === strlen($_FILES['room_img']['name'])) {
		            $items['room_img']['err_msg'] = "{$items['room_img']['alias']}を選択してください";
		        }

				// ファイル情報を取得
				if (0 === count(array_column($items, 'err_msg'))) {
					// ファイルデータ取得
					$room_img_data = base64_encode(file_get_contents($_FILES['room_img']['tmp_name']));
					// ファイル種類取得
					$room_img_type = $_FILES['room_img']['type'];
					// ファイルの拡張子取得
					$room_img_extension = pathinfo($_FILES['room_img']['name'], PATHINFO_EXTENSION);
				}


				break;
			case 'back':

				break;
			case 'fixed':
				$template = 'register_confirm';

				// 操作ユーザ情報取得
				$login_user = $this->session->get('login_user');
				
				// データ登録-roomテーブル
				$room_repository->insert($login_user['id'], $inputs);

				// roomテーブルに登録したidを取得
				$rooms = $room_repository->fetchByName($inputs['name']);
				$inputs['room_id'] = $rooms['id'];

				// データ登録-room_optionsテーブル
				$room_options_repository->insert($login_user['id'], $inputs);

				// 画像ファイル登録処理
				$upload_dir = 'upload/';
				$upload_file = $upload_dir . $inputs['name'] . '.' . $room_img_extension;
				file_put_contents($upload_file, base64_decode($room_img_data), LOCK_EX);

				break;
			default:
		}

		if (0 !== count(array_column($items, 'err_msg'))) {
			$template = 'register';
			$_button = null;
		}

		// 日付データの表示形式を変更
		$inputs = $this->convertDateDisplayType($inputs, ROOM_ITEMS_PROPERTY);

		// コード値を基に表示値を追加
		$inputs = $this->addDisplayValue($inputs, ROOM_ITEMS_PROPERTY);

		$this->smarty->assign('items', $items);
		$this->smarty->assign('inputs', $inputs);
		$this->smarty->assign('_button', $_button);
		$this->smarty->assign('errors', array_column($items, 'err_msg'));		
		$this->smarty->assign('_token', $this->generateCsrfToken('room/' . $template));
		$this->smarty->assign('title', '部屋情報登録');
		$this->smarty->assign('price_type_formatted', $price_type_formatted);

		$this->smarty->assign('upload_file', $upload_file);
		$this->smarty->assign('room_img_data', $room_img_data);
		$this->smarty->assign('room_img_type', $room_img_type);
		$this->smarty->assign('room_img_extension', $room_img_extension);

        // ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $template . '.html');
	}


	public function addRegisterAction($params)
	{
		if (!$this->session->isAuthenticated()) {
			return $this->redirect('/');
		}

	    // このアクションで使用する項目の情報を取得
	    $items = $this->getItemsInfo(array(
							// 'id',
							// 'room_img',
	    					'room_id',
							'name',
							'type',
							'price_type_code',
							'status',
							'smoking_allowed_flg',
							'pet_allowed_flg',
							'barrier_free_flg',
							'note',
							'apply_start_date',
							'apply_end_date',
	                        ), ROOM_ITEMS_PROPERTY);
	    $items['apply_end_date']['edit_type'] = 'readonly';

		$inputs = $this->getInputs(array_keys($items), ROOM_ITEMS_PROPERTY);
		$inputs['room_img'] = '';
		$_button = $this->request->getPost('button');
		// $maxid = $this->request->getPost('maxid');

		// price_type情報を取得して成型
		$price_type_repository = $this->db_manager->get('Price_type');		
		$price_type = $price_type_repository->fetchAllRow();
		foreach ($price_type as $row => $col_name) {
			$price_type_formatted[$col_name['code']] = $col_name['code'];
		}
		$items['price_type_code']['ref_var_name'] = 'price_type_formatted';

		$room_options_repository = $this->db_manager->get('RoomOptions');
		$template = 'addRegister';

		// DBから部屋情報取得
		$rooms = $room_options_repository->fetchAllByName($params['name']);
		$room = array_pop($rooms);

		// echo $params['name'];
		// echo '<pre>';
		// var_dump($rooms);
		// var_dump($room);
		// echo '</pre>';


		switch ($_button) {
			case 'addRegister':
		
				$template = 'addRegister_confirm';

		        // バリデーション
		        $items = $this->generateErrorMessage($items, $inputs);
				// バリデーション追加

				if (strtotime($inputs['apply_start_date']) <= strtotime($room['apply_start_date'])) {
					$items['apply_start_date']['err_msg'] 
						= $items['apply_start_date']['alias'] 
						  . 'は前の部屋情報(' 
						  . date($items['apply_start_date']['date_format'], strtotime($room['apply_start_date']))
						  . ')よりも後の日付を指定してください';	
				}

				if (strtotime($inputs['apply_start_date']) > strtotime($inputs['apply_end_date'])) {
					$items['apply_start_date']['err_msg'] 
						= $items['apply_start_date']['alias'] 
						  . 'は' 
						  . $items['apply_end_date']['alias'] 
						  . '以前の日付を指定してください';	
				}				

				break;
			case 'back':

				break;
			case 'fixed':
				$template = 'addRegister_confirm';

				// 操作ユーザ情報取得
				$login_user = $this->session->get('login_user');				
				
				$room_repository = $this->db_manager->get('Room');
				$rooms = $room_repository->fetchByName($inputs['name']);
				$inputs['room_id'] = $rooms['id'];

				$room_options_repository->insert($login_user['id'], $inputs);

				// 前のレコードの適用終了日を更新
				$room_options_repository->updatePrevApplyEndDate($room['id'], date('Y-m-d', strtotime('-1 day', strtotime($inputs['apply_start_date']))));

				break;
			default:
				// 初期表示時はDBから取得した情報を基に初期表示値とする。
				$template = 'addRegister';
				$inputs = $room;

				// 適用開始日の初期表示値は、前のレコードの適用開始日の次の日をセット。
				$inputs['apply_start_date'] = date('Y-m-d', strtotime('+1 day', strtotime($inputs['apply_start_date'])));
				// 適用終了日は定数の値をセット。
				$inputs['apply_end_date'] = ROOM_APPLY_END_DATE;
		}

		if (count(array_column($items, 'err_msg')) !== 0) {
			$template = 'addRegister';
			$_button = null;
		}
		
		// 日付データの表示形式を変更
		$inputs = $this->convertDateDisplayType($inputs, ROOM_ITEMS_PROPERTY);	

		// コード値を基に表示値を追加
		$inputs = $this->addDisplayValue($inputs, ROOM_ITEMS_PROPERTY);	

		// echo '<pre>';
		// var_dump($inputs);
		// var_dump($room);
		// echo '</pre>';

		// $this->smarty->assign('maxid', $maxid);
		$this->smarty->assign('items', $items);
		$this->smarty->assign('inputs', $inputs);
		$this->smarty->assign('_button', $_button);
		$this->smarty->assign('errors', array_column($items, 'err_msg'));		
		$this->smarty->assign('_token', $this->generateCsrfToken('room/' . $template));
		
		$this->smarty->assign('title', '部屋情報追加登録');
		$this->smarty->assign('price_type_formatted', $price_type_formatted);

        // ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $template . '.html');
	}


	public function updateAction($params)
	{
		if (!$this->session->isAuthenticated()) {
			return $this->redirect('/');
		}

	    // このアクションで使用する項目の情報を取得
	    $items = $this->getItemsInfo(array(
	    					'id',
	    					// 'room_id',
							// 'room_img',
							'name',
							'type',
							'price_type_code',
							'status',
							'smoking_allowed_flg',
							'pet_allowed_flg',
							'barrier_free_flg',
							'note',
							'apply_start_date',
							'apply_end_date',
	                        ), ROOM_ITEMS_PROPERTY);

		$inputs = $this->getInputs(array_keys($items), ROOM_ITEMS_PROPERTY);
		// $inputs['room_img'] = '';

		// echo '<pre>';
		// var_dump($inputs);
		// echo '</pre>';

		$items['apply_start_date']['edit_type'] = 'readonly';
		$items['apply_end_date']['edit_type'] = 'readonly';

		$_button = $this->request->getPost('button');

		// price_type情報を取得して成型
		$price_type_repository = $this->db_manager->get('Price_type');		
		$price_type = $price_type_repository->fetchAllRow();
		foreach ($price_type as $row => $col_name) {
			$price_type_formatted[$col_name['code']] = $col_name['code'];
		}
		$items['price_type_code']['ref_var_name'] = 'price_type_formatted';

		$room_options_repository = $this->db_manager->get('RoomOptions');
		$template = 'update';

		// DBから部屋情報取得
		$room = $room_options_repository->fetchById($params['id']);
		
		// 画像ファイルパス取得
		$file_paths = glob('upload/*');
		$result = preg_grep('/' . $room["name"] . '\..*/' , $file_paths);
		$before_update_file = array_shift($result);

		switch ($_button) {
			case 'update':
				$template = 'update_confirm';

		        // バリデーション
		        $items = $this->generateErrorMessage($items, $inputs);

				break;
			case 'back':

				break;
			case 'fixed':
				$template = 'update_confirm';

				// 操作ユーザ情報取得
				$login_user = $this->session->get('login_user');				
				$room_options_repository->update($params['id'], $login_user['id'], $inputs);

				break;
			default:
				// 初期表示時はDBからの情報を取得する。
				$template = 'update';
				$inputs = $room;
		}

		// 画像パス情報を取得して配列に追加
		$result = preg_grep('/' . $inputs["name"] . '\..*/' , $file_paths);
		$upload_file = array_shift($result);

		if (count(array_column($items, 'err_msg')) !== 0) {
			$template = 'update';
			$_button = null;
		}
		
		// 日付データの表示形式を変更
		$inputs = $this->convertDateDisplayType($inputs, ROOM_ITEMS_PROPERTY);	

		// コード値を基に表示値を追加
		$inputs = $this->addDisplayValue($inputs, ROOM_ITEMS_PROPERTY);	

		$this->smarty->assign('id', $params['id']);
		$this->smarty->assign('items', $items);
		$this->smarty->assign('inputs', $inputs);
		$this->smarty->assign('_button', $_button);
		$this->smarty->assign('errors', array_column($items, 'err_msg'));		
		$this->smarty->assign('_token', $this->generateCsrfToken('room/' . $template));
		
		$this->smarty->assign('title', '部屋情報編集');
		$this->smarty->assign('price_type_formatted', $price_type_formatted);

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
	        // if ('' !== $const_name[$col_name]['ref_const_name']
	        //     && !empty($value)) {// あれ？「!emtpy(0)」はfalseに倒れてるぽいな。
	    	if ('' !== $const_name[$col_name]['ref_const_name']
	            && 0 !== strlen($value)) {
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
	        // if ('' !== $const_name[$col_name]['ref_const_name']
	        //     && !empty($value)) {
	    	//         ↑emptyじゃコード値「0」の時に期待通りにならない。issetにしてみる。うまく行くならAdmin_user側も直すか？→うまく行かなかった。。。

	        if ('' !== $const_name[$col_name]['ref_const_name']
	            && strlen($value) > 0) {
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

		// name固有のバリデーション
		$col_name = 'name';
		if (!empty($items[$col_name])) {
			$property = ROOM_ITEMS_PROPERTY[$col_name];
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

