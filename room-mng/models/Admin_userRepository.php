<?php

class Admin_userRepository extends DbRepository
{
	public function insert($operate_user_id, $inputs = array())
	{
		$password = $this->hashPassword($inputs['password']);
		$now = new Datetime();

		$sql = "INSERT INTO admin_user(
					name,
					login_id,
					password,
					auth_type,
					note,
					create_date,
					create_user_id,
					update_date,
					update_user_id
				) 
				VALUES (
					:name,
					:login_id,
					:password,
					:auth_type,
					:note,
					:create_date,
					:create_user_id,
					:update_date,
					:update_user_id
				)
		";

		$stmt = $this->execute($sql, array(
			':name' => $inputs['name'],
			':login_id' => $inputs['login_id'],
			':password' => $password,
			':auth_type' => $inputs['auth_type'],
			':note' => $inputs['note'],
			':create_date' => $now->format('Y-m-d H:i:s'),
			':create_user_id' => $operate_user_id,
			':update_date' => $now->format('Y-m-d H:i:s'),
			':update_user_id' => $operate_user_id,
		));
	}

	public function hashPassword($password)
	{
		// return crypt($password, 'Samura1');
		return password_hash($password, PASSWORD_DEFAULT);
	}

	public function isUniqueLogin_id($login_id)
	{
		$sql = "SELECT COUNT(id) as count FROM admin_user WHERE login_id = :login_id";

		$row = $this->fetch($sql, array(':login_id' => $login_id));

		if ($row['count'] == 0) {
			return true;
		}

		return false;
	}

	public function fetchByLogin_id($login_id)
	{
		$sql = "SELECT 
					id,
					name,
					login_id,
					password,
					auth_type,
					note,
					create_date,
					create_user_id,
					update_date,
					update_user_id
				FROM admin_user WHERE login_id = :login_id";

		return $this->fetch($sql, array(':login_id' => $login_id,));
	}

	public function fetchById($id)
	{
		$sql = "SELECT
					id,
					name,
					login_id,
					password,
					auth_type,
					note,
					create_date,
					create_user_id,
					update_date,
					update_user_id 
				FROM admin_user WHERE id = :id";

		return $this->fetch($sql, array(':id' => $id,));
	}

	public function fetchAllRow()
	{
		$sql = "SELECT 
					id,
					name,
					login_id,
					password,
					auth_type,
					note,
					create_date,
					create_user_id,
					update_date,
					update_user_id
				FROM admin_user";

		return $this->fetchAll($sql,);
	}

	public function update($id, $operate_user_id, $inputs = array())
	{
		$now = new Datetime();

		$sql = "UPDATE admin_user SET
					id = :id,
					name = :name,
					login_id = :login_id,
					auth_type = :auth_type,
					note = :note,
					update_date = :update_date,
					update_user_id = :update_user_id
				WHERE id = :id
		";

		$stmt = $this->execute($sql, array(
			':id' => $id,
			':name' => $inputs['name'],
			':login_id' => $inputs['login_id'],
			':auth_type' => $inputs['auth_type'],
			':note' => $inputs['note'],
			':update_date' => $now->format('Y-m-d H:i:s'),
			':update_user_id' => $operate_user_id
		));
	}

	public function delete($id)
	{
		$sql = "DELETE FROM admin_user 
				WHERE id = :id
		";
		$stmt = $this->execute($sql, array(
			':id' => $id,
		));
	}

}