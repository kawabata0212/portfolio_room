<?php

class CustomerController extends Controller
{

	public function indexAction($params)
	{
		if (!$this->session->isAuthenticated()) {
			return $this->redirect('/');
		}

		// このアクションの検索条件以外で使用する項目の情報を指定して取得
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
							'note',
							'create_date',
							'create_user_id',
							'update_date',
							'update_user_id',
							), CUSTOMER_USER_ITEMS_PROPERTY);

		// // このアクションの検索条件で使用する項目の情報を指定して取得
		// $search_items = $this->getItemsInfo(array(
		// 					'type',
		// 					'status',
		// 					'smoking_allowed_flg',
		// 					'pet_allowed_flg',
		// 					'barrier_free_flg',
		// 					), ROOM_INFO_ITEMS_PROPERTY);

		$login_user = $this->session->get('login_user');
		$customer_repository = $this->db_manager->get('CustomerUser');
		$page = $this->request->getGet('page', 1);

		// $search_conditions['type'] = $this->request->getGet('searchValue-type', '');
		// $search_conditions['status'] = $this->request->getGet('searchValue-status', '');
		// $search_conditions['smoking_allowed_flg'] = $this->request->getGet('searchValue-smoking_allowed_flg', '');
		// $search_conditions['pet_allowed_flg'] = $this->request->getGet('searchValue-pet_allowed_flg', '');
		// $search_conditions['barrier_free_flg'] = $this->request->getGet('searchValue-barrier_free_flg', '');
		// $search_conditions['note'] = $this->request->getGet('searchValue-note', '');

		// echo '<pre>';
		// var_dump($search_conditions);
		// echo '</pre>';		

		$customers = $customer_repository->fetchAllRow();
		foreach ($customers as $row_no => &$cur_row) {
			// 日付データの表示形式を変更
			$cur_row = $this->convertDateDisplayType($cur_row, CUSTOMER_USER_ITEMS_PROPERTY);

			// コード値から表示値に変換
			$cur_row = $this->convertDispValue($cur_row, CUSTOMER_USER_ITEMS_PROPERTY);

		}

		$this->smarty->assign('login_user', $login_user);   // viewに渡す値。
		$this->smarty->assign('customers', $customers);
		$this->smarty->assign('items', $items);
		$this->smarty->assign('_token', $this->generateCsrfToken('customer/index'));
		$this->smarty->assign('page', $page);
		// $this->smarty->assign('search_conditions', $search_conditions);

		$this->smarty->assign('title', '顧客一覧');
		
		// $this->smarty->assign('search_items', $search_items);

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

}
