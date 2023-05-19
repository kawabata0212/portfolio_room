<?php

class RoomController extends Controller
{

	public function searchAction($params)
		{
			// if (!$this->session->isAuthenticated()) {
			// 	return $this->redirect('/');
			// }

			$now = new Datetime();
			$start_date = $now->format('Y-m-d');
			$end_date = $now->modify('+1 days')->format('Y-m-d');

			$this->smarty->assign('start_date', $start_date);
			$this->smarty->assign('end_date', $end_date);

			$this->smarty->assign('title', '宿泊日指定');
			
			// ビューファイル呼び出し
	        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');
		}




	public function listAction($params)
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
		$room_options_repository = $this->db_manager->get('RoomOptions');
		$page = $this->request->getGet('page', 1);
	    $start_date = $this->request->getGet('start_date');
	    $end_date = $this->request->getGet('end_date');

		$rooms = $room_options_repository->fetchAllByApplyDate($start_date, $end_date);

		$file_paths = glob('../../room-mng/web/upload/*');

		foreach ($rooms as $row_no => &$cur_row) {

			// 画像パスを連想配列に新規追加
			$result = preg_grep('/' . $cur_row['name'] . '\..*/' , $file_paths);

			$cur_row['room_img'] = 'https://room-mng.engineer-k-kawabata.net/upload/' . basename(array_shift($result)); 
		}

		$this->smarty->assign('login_user', $login_user);   // viewに渡す値。
		$this->smarty->assign('rooms', $rooms);
		$this->smarty->assign('items', $items);
		$this->smarty->assign('_token', $this->generateCsrfToken('room/index'));
		$this->smarty->assign('page', $page);
		// $this->smarty->assign('search_conditions', $search_conditions);

	    $this->smarty->assign('start_date', $start_date);
	    $this->smarty->assign('end_date', $end_date);

		$this->smarty->assign('title', '宿泊可能な部屋一覧');
		
		$this->smarty->assign('search_items', $search_items);

		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');
	}




	public function detailAction($params)
	{
		if (!$this->session->isAuthenticated()) {
			return $this->redirect('/');
		}

		// このアクションで使用する項目の情報を指定して取得
		$items = $this->getItemsInfo(array(
							// 'id',
							// 'room_id',
							'room_img',
							'name',
							// 'type',
							// 'price_type_code',
							// 'status',
							'smoking_allowed_flg',
							'pet_allowed_flg',
							'barrier_free_flg',
							// 'note',
							// 'apply_start_date',
							// 'apply_end_date',
							), ROOM_ITEMS_PROPERTY);

		$items['room_img']['width'] = '300px';



		$_button = $this->request->getPost('button');

		$login_user = $this->session->get('login_user');
		$room_options_repository = $this->db_manager->get('RoomOptions');

		$room_name = $params['room_name'];

		$start_date = $this->request->getGet('start_date');
	    $end_date = $this->request->getGet('end_date');
		

		$inputs = $room_options_repository->fetchByName_Date($room_name, $start_date, $end_date);

		// echo '<pre>';
		// var_dump($inputs);
		// echo '</pre>';

		// 画像ファイル一覧を取得
		$file_paths = glob('../../room-mng/web/upload/*');;

		// 画像パスを連想配列に新規追加
		$result = preg_grep('/' . $room_name . '\..*/' , $file_paths);
		$inputs['room_img'] = 'https://room-mng.engineer-k-kawabata.net/upload/' . basename(array_shift($result)); 


		$Price_type_repository = $this->db_manager->get('Price_type');
		$Price_types = $Price_type_repository->fetchAllByCode($inputs['price_type_code']);
		array_multisort(
			array_column($Price_types, 'code'), SORT_ASC, 
			array_column($Price_types, 'amount'), SORT_ASC, 
			array_column($Price_types, 'weekday_type'), SORT_DESC, 
			$Price_types);

		$price_etc = '';
		foreach ($Price_types as $price_type) {
			$price_etc .= $price_type['amount'] . '名様：';
			// $price_etc .= number_format($price_type['price']) . '円';
			$price_etc .= number_format($price_type['price'] * $price_type['amount']) . '円';
			$price_etc .= '<br>';
		}

		$this->smarty->assign('login_user', $login_user);
		$this->smarty->assign('inputs', $inputs);
		
		$this->smarty->assign('items', $items);
		$this->smarty->assign('_button', $_button);		

		$this->smarty->assign('_token', $this->generateCsrfToken('room/indexByName'));
		
		$this->smarty->assign('title', '部屋情報詳細');
		$this->smarty->assign('room_name', $room_name);
		$this->smarty->assign('start_date', $start_date);
		$this->smarty->assign('end_date', $end_date);
		$this->smarty->assign('price_etc', $price_etc);

		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');
	}



	public function reserveAction($params)
	{

	    $room_name = $this->request->getPost('name');
	    $start_date = $this->request->getPost('start_date');
	    $end_date = $this->request->getPost('end_date');

		// このアクションで使用する項目の情報を指定して取得
		$items = $this->getItemsInfo(array(
							// 'id',
							// 'room_id',
							'room_img',
							'name',
							// 'type',
							// 'price_type_code',
							// 'status',
							'smoking_allowed_flg',
							'pet_allowed_flg',
							'barrier_free_flg',
							// 'note',
							// 'apply_start_date',
							// 'apply_end_date',
							), ROOM_ITEMS_PROPERTY);

		$items['room_img']['width'] = '300px';


		$_button = $this->request->getPost('button');

		$login_user = $this->session->get('login_user');
		$room_options_repository = $this->db_manager->get('RoomOptions');

		$inputs = $room_options_repository->fetchByName_Date($room_name, $start_date, $end_date);

		// 画像ファイル一覧を取得
		$file_paths = glob('../../room-mng/web/upload/*');;

		// 画像パスを連想配列に新規追加
		$result = preg_grep('/' . $room_name . '\..*/' , $file_paths);
		$inputs['room_img'] = 'https://room-mng.engineer-k-kawabata.net/upload/' . basename(array_shift($result)); 

	    $Price_type_repository = $this->db_manager->get('Price_type');
	    $Price_types = $Price_type_repository->fetchAllByCode($inputs['price_type_code']);
	    array_multisort(
	      array_column($Price_types, 'code'), SORT_ASC, 
	      array_column($Price_types, 'amount'), SORT_ASC, 
	      array_column($Price_types, 'weekday_type'), SORT_DESC, 
	      $Price_types);

	    $price_etc = '';
	    foreach ($Price_types as $price_type) {
	      $price_etc .= $price_type['amount'] . '名様：';
	      // $price_etc .= number_format($price_type['price']) . '円';
	      $price_etc .= number_format($price_type['price'] * $price_type['amount']) . '円';
	      $price_etc .= '<br>';
	    }


		$this->smarty->assign('login_user', $login_user);
		$this->smarty->assign('inputs', $inputs);
		
		$this->smarty->assign('items', $items);
		$this->smarty->assign('_button', $_button);		

		$this->smarty->assign('_token', $this->generateCsrfToken('room/indexByName'));
		
		$this->smarty->assign('title', '予約フォーム');
		$this->smarty->assign('room_name', $room_name);
		$this->smarty->assign('start_date', $start_date);
		$this->smarty->assign('end_date', $end_date);
    	$this->smarty->assign('price_etc', $price_etc);

		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');
	}



	public function reserveConfirmAction($params)
	{
		if (!$this->session->isAuthenticated()) {
			return $this->redirect('/');
		}

		// echo '<pre>';
		// var_dump($_POST);
		// echo '</pre>';

	    $room_name = $this->request->getPost('name');
	    $start_date = $this->request->getPost('start_date');
	    $end_date = $this->request->getPost('end_date');
	    $plan_count = $this->request->getPost('plan_count');

		// このアクションで使用する項目の情報を指定して取得
		$items = $this->getItemsInfo(array(
							// 'id',
							// 'room_id',
							'room_img',
							'name',
							// 'type',
							// 'price_type_code',
							// 'status',
							'smoking_allowed_flg',
							'pet_allowed_flg',
							'barrier_free_flg',
							// 'note',
							// 'apply_start_date',
							// 'apply_end_date',
							), ROOM_ITEMS_PROPERTY);

		$items['room_img']['width'] = '300px';

		$_button = $this->request->getPost('button');

		$login_user = $this->session->get('login_user');
		$room_options_repository = $this->db_manager->get('RoomOptions');

		$inputs = $room_options_repository->fetchByName_Date($room_name, $start_date, $end_date);

		// echo '<pre>';
		// var_dump($inputs);
		// echo '</pre>';

		// 画像ファイル一覧を取得
		$file_paths = glob('../../room-mng/web/upload/*');;

		// 画像パスを連想配列に新規追加
		$result = preg_grep('/' . $room_name . '\..*/' , $file_paths);
		$inputs['room_img'] = 'https://room-mng.engineer-k-kawabata.net/upload/' . basename(array_shift($result)); 

	    $Price_type_repository = $this->db_manager->get('Price_type');
	    $Price_type = $Price_type_repository->fetchByCode_Amount($inputs['price_type_code'], $plan_count);

		// echo '<pre>';
		// var_dump($Price_type);
		// echo '</pre>';

	    $price_etc = '';
	    // $price_etc .= $Price_type['amount'] . '名様：';
		$price_etc .= number_format($Price_type['price'] * 
									$Price_type['amount'] *
									(strtotime($end_date) - strtotime($start_date)) / 86400) // 宿泊日数
									. '円';								
		$price_etc .= '<br>';

		$this->smarty->assign('login_user', $login_user);
		$this->smarty->assign('inputs', $inputs);
		
		$this->smarty->assign('items', $items);
		$this->smarty->assign('_button', $_button);		

		$this->smarty->assign('_token', $this->generateCsrfToken('room/indexByName'));
		
		$this->smarty->assign('title', '予約確認');
		$this->smarty->assign('room_name', $room_name);
		$this->smarty->assign('start_date', $start_date);
		$this->smarty->assign('end_date', $end_date);
    	$this->smarty->assign('price_etc', $price_etc);
    	$this->smarty->assign('plan_count', $plan_count);
    	$this->smarty->assign('Price_type', $Price_type);

		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');
	}



	public function reserveFixedAction($params)
	{
		if (!$this->session->isAuthenticated()) {
			return $this->redirect('/');
		}

		// echo '<pre>';
		// var_dump($_POST);
		// echo '</pre>';

	    $room_name = $this->request->getPost('name');
	    $start_date = $this->request->getPost('start_date');
	    $end_date = $this->request->getPost('end_date');
	    $price_type_code = $this->request->getPost('price_type_code');

		// このアクションで使用する項目の情報を指定して取得
		$items = $this->getItemsInfo(array(
							// 'id',
							// 'room_id',
							'room_img',
							'name',
							// 'type',
							// 'price_type_code',
							// 'status',
							'smoking_allowed_flg',
							'pet_allowed_flg',
							'barrier_free_flg',
							// 'note',
							// 'apply_start_date',
							// 'apply_end_date',
							), ROOM_ITEMS_PROPERTY);

		$items['room_img']['width'] = '300px';

		$_button = $this->request->getPost('button');

		$login_user = $this->session->get('login_user');
		$room_options_repository = $this->db_manager->get('RoomOptions');

		$inputs = $room_options_repository->fetchByName_Date($room_name, $start_date, $end_date);

		// echo '<pre>';
		// var_dump($inputs);
		// echo '</pre>';

		// 画像ファイル一覧を取得
		$file_paths = glob('../../room-mng/web/upload/*');;

		// 画像パスを連想配列に新規追加
		$result = preg_grep('/' . $room_name . '\..*/' , $file_paths);
		$inputs['room_img'] = 'https://room-mng.engineer-k-kawabata.net/upload/' . basename(array_shift($result)); 

	    $Price_type_repository = $this->db_manager->get('Price_type');
	    $Price_type = $Price_type_repository->fetchById($price_type_code);

		// echo '<pre>';
		// var_dump($Price_type);
		// echo '</pre>';

	    $price_etc = '';
	    // $price_etc .= $Price_type['amount'] . '名様：';
		$price_etc .= number_format($Price_type['price'] * 
									$Price_type['amount'] *
									(strtotime($end_date) - strtotime($start_date)) / 86400) // 宿泊日数
									. '円';
		$price_etc .= '<br>';

		$reserve_repository = $this->db_manager->get('Reserve');
		$room_repository = $this->db_manager->get('Room');

		// roomテーブルに行ロック
		$room_repository->fetchByIdWithRock($inputs['room_id']);

		//　★検証用sleep 検証後コメントアウトする
		// $room_options_repository->sleep(3);

		// 予約済データの存在確認
		$reserve = $reserve_repository->fetchByStartDateEndDateRoomId($start_date, $end_date, $inputs['room_id']);

		// echo '<pre>';
		// var_dump($reserve);
		// echo '</pre>';

		if (empty($reserve)) {
			// 予約済データが存在しなければ予約データ挿入
			$reserve_repository->insert($login_user['id'], $start_date, $end_date, $inputs, $Price_type);

		} else {
			// 予約済データが存在する場合は挿入せずに処理終了
			return $this->redirect('/room/reserveError');

		}

		// roomテーブルの行ロック解除
		$reserve_repository->commit();

		$this->smarty->assign('login_user', $login_user);
		$this->smarty->assign('inputs', $inputs);
		
		$this->smarty->assign('items', $items);
		$this->smarty->assign('_button', $_button);		

		$this->smarty->assign('_token', $this->generateCsrfToken('room/indexByName'));
		
		$this->smarty->assign('title', '予約完了');
		$this->smarty->assign('room_name', $room_name);
		$this->smarty->assign('start_date', $start_date);
		$this->smarty->assign('end_date', $end_date);
    	$this->smarty->assign('price_etc', $price_etc);
    	$this->smarty->assign('Price_type', $Price_type);

		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');
	}



	public function reserveErrorAction()
	{

		$this->smarty->assign('title', '予約操作をやり直してください');
		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');
	}



	public function reserveHistoryAction($params)
	{
		if (!$this->session->isAuthenticated()) {
			return $this->redirect('/');
		}

		// このアクションで使用する項目の情報を指定して取得
		$items = $this->getItemsInfo(array(
							'id',
							'plan_start_date',
							'room_name',
							// 'customer_name',
							'plan_end_date',
							'plan_price',
							'status',
							'start_date',
							'end_date',
							'price',
							// 'note',
							), RESERVELIST_ITEMS_PROPERTY);

		$items['room_name']['sort_order'] = '1';
		
		// 'sort_order'の昇順でソート
		array_multisort(array_column($items, 'sort_order'), SORT_ASC, $items);

		$login_user = $this->session->get('login_user');
		$reserve_repository = $this->db_manager->get('Reserve');

		$reserves = $reserve_repository->fetchAllByUser_id($login_user['id']);

		// echo '<pre>';
		// var_dump($reserves);
		// echo '</pre>';

		// 画像ファイル一覧を取得
		$file_paths = glob('../../room-mng/web/upload/*');;

		if (!isset($param['page'])) {
			$param['page'] = 1;
		}

		$this->smarty->assign('login_user', $login_user);
		$this->smarty->assign('reserves', $reserves);
		
		$this->smarty->assign('items', $items);
		// $this->smarty->assign('_button', $_button);		

		$this->smarty->assign('page', $param['page']);
		
		$this->smarty->assign('title', '予約履歴');

		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');
	}



	public function reserveCancelAction($params)
	{
		if (!$this->session->isAuthenticated()) {
			return $this->redirect('/');
		}

		// echo '<pre>';
		// var_dump($_POST);
		// echo '</pre>';

		// このアクションで使用する項目の情報を指定して取得
		$items = $this->getItemsInfo(array(
							'id',
							// 'room_img',
							'plan_start_date',
							'plan_end_date',
							'room_name',							
							'plan_price',
							// 'smoking_allowed_flg',
							// 'pet_allowed_flg',
							// 'barrier_free_flg',							
							// 'customer_name',							
							// 'status',
							// 'start_date',
							// 'end_date',
							// 'price',
							// 'note',
							), RESERVELIST_ITEMS_PROPERTY);

		// $items['room_img']['width'] = '300px';
		$items['id']['edit_type'] = 'invisible';
		$items['room_name']['sort_order'] = '1';

		// 'sort_order'の昇順でソート
		array_multisort(array_column($items, 'sort_order'), SORT_ASC, $items);


		// $_button = $this->request->getPost('button');

		$reserve_id = $params['reserve_id'];

		$login_user = $this->session->get('login_user');
    	$reserve_repository = $this->db_manager->get('Reserve');
		
		$inputs = $reserve_repository->fetchById($reserve_id);

		// echo '<pre>';
		// var_dump($inputs);
		// echo '</pre>';

		// 画像ファイル一覧を取得
		$file_paths = glob('../../room-mng/web/upload/*');;

		// 画像パスを連想配列に新規追加
		// $result = preg_grep('/' . $room_name . '\..*/' , $file_paths);
		// $inputs['room_img'] = 'https://room-mng.engineer-k-kawabata.net/upload/' . basename(array_shift($result)); 

	    // $Price_type_repository = $this->db_manager->get('Price_type');
	    // $Price_type = $Price_type_repository->fetchById($inputs['price_type_code']);
	
		// $reserve_repository->updateToCancel($reserve_id);

		$this->smarty->assign('login_user', $login_user);
		$this->smarty->assign('inputs', $inputs);
		
		$this->smarty->assign('items', $items);
		// $this->smarty->assign('_button', $_button);		

		$this->smarty->assign('_token', $this->generateCsrfToken('room/reserveHistory'));
		
		$this->smarty->assign('title', '予約キャンセル');

		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');
	}



	public function reserveCancelFixedAction($params)
	{
		if (!$this->session->isAuthenticated()) {
			return $this->redirect('/');
		}

		$token = $this->request->getPost('_token');

		// echo '<pre>';
		// var_dump($token);
		// echo '</pre>';

		if (!$this->checkCsrfToken('room/reserveHistory', $token)) {
			return $this->redirect('/');
		}

		// このアクションで使用する項目の情報を指定して取得
		$items = $this->getItemsInfo(array(
							'id',
							// 'room_img',
							'plan_start_date',
							'plan_end_date',
							'room_name',							
							'plan_price',
							// 'smoking_allowed_flg',
							// 'pet_allowed_flg',
							// 'barrier_free_flg',							
							// 'customer_name',							
							// 'status',
							// 'start_date',
							// 'end_date',
							// 'price',
							// 'note',
							), RESERVELIST_ITEMS_PROPERTY);

		// $items['room_img']['width'] = '300px';
		$items['id']['edit_type'] = 'invisible';
		$items['room_name']['sort_order'] = '1';

		// 'sort_order'の昇順でソート
		array_multisort(array_column($items, 'sort_order'), SORT_ASC, $items);
		$reserve_id = $this->request->getPost('reserve_id');


		$login_user = $this->session->get('login_user');
    	$reserve_repository = $this->db_manager->get('Reserve');

    	// 予約取り消し実行
		$reserve_repository->updateForCancel($reserve_id);		
		
		$inputs = $reserve_repository->fetchById($reserve_id);


		$this->smarty->assign('login_user', $login_user);
		$this->smarty->assign('inputs', $inputs);
		
		$this->smarty->assign('items', $items);
		// $this->smarty->assign('_button', $_button);		

		$this->smarty->assign('_token', $this->generateCsrfToken('room/indexByName'));
		
		$this->smarty->assign('title', '予約キャンセル完了');

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

