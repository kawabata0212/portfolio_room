<?php

class Price_typeController extends Controller
{
	public function listAction()
	{
		// price_type情報を取得
		$price_type_repository = $this->db_manager->get('Price_type');		
		$price_type = $price_type_repository->fetchAllRow();
		array_multisort(
			array_column($price_type, 'code'), SORT_ASC, 
			array_column($price_type, 'amount'), SORT_ASC, 
			array_column($price_type, 'weekday_type'), SORT_DESC, 
			$price_type);

		$items['price_type_code']['ref_var_name'] = 'price_type_formatted';		

		$this->smarty->assign('title', '価格タイプ一覧');
		$this->smarty->assign('price_type', $price_type);

		// ビューファイル呼び出し
        $this->smarty->display('../views/' . $this->controller_name . '/' . $this->action_name . '.html');
    }

}
