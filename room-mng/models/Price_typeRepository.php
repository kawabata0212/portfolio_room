<?php

class Price_typeRepository extends DbRepository
{
	public function fetchAllByCode($code)
	{
		$sql = "SELECT 
					id,
					code,
					amount,
					weekday_type,
					price,
					note,
					create_date,
					create_user_id,
					update_date,
					update_user_id
				FROM price_type WHERE code = :code";

		return $this->fetchAll($sql, array(':code' => $code,));
	}

	public function fetchByCode_Amount($code, $amount)
	{
		$sql = "SELECT 
					id,
					code,
					amount,
					weekday_type,
					price,
					note,
					create_date,
					create_user_id,
					update_date,
					update_user_id
				FROM price_type 
				WHERE code = :code
					AND  amount = :amount";

		return $this->fetch($sql, array(':code' => $code,
											':amount' => $amount,
											));
	}

	public function fetchById($id)
	{
		$sql = "SELECT 
					id,
					code,
					amount,
					weekday_type,
					price,
					note,
					create_date,
					create_user_id,
					update_date,
					update_user_id
				FROM price_type WHERE id = :id";

		return $this->fetch($sql, array(':id' => $id,));
	}

	public function fetchAllRow()
	{
		$sql = "SELECT
					id,
					code,
					amount,
					weekday_type,
					price,
					note,
					create_date,
					create_user_id,
					update_date,
					update_user_id
				FROM price_type";

		return $this->fetchAll($sql,);
	}
}
