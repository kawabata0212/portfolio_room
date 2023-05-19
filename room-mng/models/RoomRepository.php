<?php

class RoomRepository extends DbRepository
{
	public function insert($operate_user_id, $inputs = array())
	{
		// $password = $this->hashPassword($inputs['password']);
		$now = new Datetime();

		$sql = "INSERT INTO room(
					name,
					create_date,
					create_user_id,
					update_date,
					update_user_id
				) 
				VALUES (
					:name,
					:create_date,
					:create_user_id,
					:update_date,
					:update_user_id
				)
		";

		$stmt = $this->execute($sql, array(
			':name' => $inputs['name'],
			':create_date' => $now->format('Y-m-d H:i:s'),
			':create_user_id' => $operate_user_id,
			':update_date' => $now->format('Y-m-d H:i:s'),
			':update_user_id' => $operate_user_id,
		));
	}

	public function isUniqueName($name)
	{
		$sql = "SELECT COUNT(id) as count FROM room WHERE name = :name";

		$row = $this->fetch($sql, array(':name' => $name));

		if ($row['count'] == 0) {
			return true;
		}

		return false;
	}

	public function fetchAllRow()
	{
		$sql = "SELECT
					id,
					name,
					create_date,
					create_user_id,
					update_date,
					update_user_id
				FROM room
				ORDER BY lpad(name, 4 , '0')";

		return $this->fetchAll($sql, array());
	}

	public function fetchByName($name)
	{
		$sql = "SELECT
					id,
					name,
					create_date,
					create_user_id,
					update_date,
					update_user_id
				FROM room
				WHERE name = :name";

		return $this->fetch($sql, array('name' => $name));
	}


	public function fetchByIdWithRock($id)
	{
		$sql = "SELECT
					id,
					name,
					create_date,
					create_user_id,
					update_date,
					update_user_id
				FROM room
				WHERE id = :id FOR UPDATE";

		return $this->fetch($sql, array('id' => $id,));
	}

}