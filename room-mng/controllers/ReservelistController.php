<?php

class ReservelistController extends Controller
{
	public function indexAction($param)
	{
		if (!$this->session->isAuthenticated()) {
			return $this->redirect('/');
		}

		// このアクションで使用する項目の情報を指定して取得
		$items = $this->getItemsInfo(array(
							'id',
							'plan_start_date',
							'room_name',
							'customer_name',
							'plan_end_date',
							'plan_price',
							'status',
							'start_date',
							'end_date',
							'price',
							// 'note',
							'create_date',
							'update_date',
							), RESERVELIST_ITEMS_PROPERTY);

		$login_user = $this->session->get('login_user');
		$reserve_repository = $this->db_manager->get('Reserve');

		$reserves = $reserve_repository->fetchAllRow();

		// echo '<pre>';
		// var_dump($items);
		// echo '</pre>';

		// foreachの$cur_rowを参照渡しにしたら期待通りになった。なるほどね。
		foreach ($reserves as $row_no => &$cur_row) {
			// 日付データの表示形式を変更
			$cur_row = $this->convertDateDisplayType($cur_row, RESERVELIST_ITEMS_PROPERTY);

			// コード値から表示値に変換
			$cur_row = $this->convertDispValue($cur_row, RESERVELIST_ITEMS_PROPERTY);

		}

		if (!isset($param['page'])) {
			$param['page'] = 1;
		}

		$this->smarty->assign('reserves', $reserves);
		$this->smarty->assign('items', $items);
		$this->smarty->assign('_token', $this->generateCsrfToken('reserve/index'));
		$this->smarty->assign('page', $param['page']);
		$this->smarty->assign('title', '予約状況確認');	

		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');
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


}
