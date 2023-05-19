<?php

class CustomerUserRepository extends DbRepository
{

	public function fetchAllRow()
	{
		$sql = "SELECT
					id,
					name,
					furigana,
					tel,
					email,
					-- password,
					post_number,
					pref,
					city,
					address1,
					address2,
					note,
					create_date,
					create_user_id,
					update_date,
					update_user_id
				FROM customer_user";

		return $this->fetchAll($sql, array());
	}


	public function insert($inputs = array())
	{
		$password = $this->hashPassword($inputs['password']);
		$now = new Datetime();

		$sql = "INSERT INTO customer_user(
					name,
					furigana,
					tel,
					email,
					password,
					post_number,
					pref,
					city,
					address1,
					address2,
					-- note, ★物理列自体不要か
					create_date,
					create_user_id,
					update_date,
					update_user_id
				) 
				VALUES (
					:name,
					:furigana,
					:tel,
					:email,
					:password,
					:post_number,
					:pref,
					:city,
					:address1,
					:address2,
					-- :note,
					:create_date,
					:create_user_id,
					:update_date,
					:update_user_id
				)
		";

		$stmt = $this->execute($sql, array(
			':name' => $inputs['name'],
			':furigana' => $inputs['furigana'],
			':tel' => $inputs['tel'],
			':email' => $inputs['email'],
			':password' => $password,
			':post_number' => $inputs['post_number'],
			':pref' => $inputs['pref'],
			':city' => $inputs['city'],
			':address1' => $inputs['address1'],
			':address2' => $inputs['address2'],
			// ':note' => $inputs['note'],

			':create_date' => $now->format('Y-m-d H:i:s'),
			':create_user_id' => 999,
			':update_date' => $now->format('Y-m-d H:i:s'),
			':update_user_id' => 999,
		));
	}

	public function hashPassword($password)
	{
		return password_hash($password, PASSWORD_DEFAULT);
	}

	public function isUniqueEmail($email)
	{
		$sql = "SELECT COUNT(id) as count FROM customer_user WHERE email = :email";

		$row = $this->fetch($sql, array(':email' => $email));

		if ($row['count'] == 0) {
			return true;
		}

		return false;
	}

	public function fetchByEmail($email)
	{
		$sql = "SELECT 
					id,
					name,
					furigana,
					tel,
					email,
					password,
					post_number,
					pref,
					city,
					address1,
					address2,
					create_date,
					create_user_id,
					update_date,
					update_user_id
				FROM customer_user WHERE email = :email";

		return $this->fetch($sql, array(':email' => $email,));
	}


	public function fetchById($id)
	{
		$sql = "SELECT 
					id,
					name,
					furigana,
					tel,
					email,
					password,
					post_number,
					pref,
					city,
					address1,
					address2,
					create_date,
					create_user_id,
					update_date,
					update_user_id
				FROM customer_user WHERE id = :id";

		return $this->fetch($sql, array(':id' => $id,));
	}


	public function update($inputs = array())
	{
		$now = new Datetime();

		$sql = "UPDATE customer_user SET
					name = :name,
					furigana = :furigana,
					tel = :tel,
					email = :email,
					post_number= :post_number,
					pref = :pref,
					city = :city,
					address1= :address1,
					address2= :address2,
					update_date = :update_date
				WHERE id = :id";

		$stmt = $this->execute($sql, array(
			':id' => $inputs['id'],
			':name' => $inputs['name'],
			':furigana' => $inputs['furigana'],
			':tel' => $inputs['tel'],
			':email' => $inputs['email'],
			':post_number' => $inputs['post_number'],
			':pref' => $inputs['pref'],
			':city' => $inputs['city'],
			':address1' => $inputs['address1'],
			':address2' => $inputs['address2'],
			':update_date' => $now->format('Y-m-d H:i:s'),

		));
	}


	public function updatePassword($id, $password)
	{
		$password = $this->hashPassword($password);
		$now = new Datetime();

		$sql = "UPDATE customer_user SET
					password = :password,
					update_date = :update_date
				WHERE id = :id";

		$stmt = $this->execute($sql, array(
			':id' => $id,
			':password' => $password,
			':update_date' => $now->format('Y-m-d H:i:s'),
		));
	}

	public function updateForWithdrawal ($id)
	{
		$now = new Datetime();

		$sql = "UPDATE customer_user SET
					furigana = NULL,
					tel = NULL,
					email = NULL,
					password = NULL,
					post_number = NULL,
					pref = NULL,
					city = NULL,
					address1 = NULL,
					address2 = NULL,
					name = '(退会済みユーザ)',
					update_date = :update_date
				WHERE id = :id";

		$stmt = $this->execute($sql, array(
								':id' => $id,
								':update_date' => $now->format('Y-m-d H:i:s'),
		));
	}


}