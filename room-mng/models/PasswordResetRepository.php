<?php

class PasswordResetRepository extends DbRepository
{

    /**
     * パスワード再設定依頼受付時の挿入処理
     */

	public function insert($customer_id, $auth_key)
	{
		$now = new Datetime();

		$sql = "INSERT INTO password_reset(
					auth_key,
					expire_date,
					customer_id,
					is_proccessd,
					create_date,
					update_date
				) 
				VALUES (
					:auth_key,					
					:expire_date,
					:customer_id,
					:is_proccessd,
					:create_date,
					:update_date
				)
		";

		$stmt = $this->execute($sql, array(
					':customer_id' => $customer_id,
					':auth_key' => $auth_key,
					':is_proccessd' => 0,
					':create_date' => $now->format('Y-m-d H:i:s'),
					':update_date' => $now->format('Y-m-d H:i:s'),
					// ':expire_date' => $now->modify('+1 day')->format('Y-m-d H:i:s'),	
					// ★★検証用に色々弄ってみる用 ★定数使えるかな。
					':expire_date' => $now->modify(VALID_PERIOD)->format('Y-m-d H:i:s'),	
		));
	}

    /**
     * パスワード再設定画面に遷移するリンクがクリックされた際の抽出処理
     */
	public function fetchByAuthKey($auth_key)
	{
		$sql = "SELECT
					auth_key,
					expire_date,
					customer_id,
					is_proccessd,
					create_date,
					update_date
				FROM password_reset
				WHERE auth_key = :auth_key";

		return $this->fetch($sql, array('auth_key' => $auth_key));
	}


    /**
     * パスワード再設定時の更新処理
     */
	public function updateIsProccessd($auth_key)
	{
		$sql = "UPDATE password_reset SET is_proccessd = 1
				WHERE auth_key = :auth_key";

		return $this->fetch($sql, array('auth_key' => $auth_key));
	}

}