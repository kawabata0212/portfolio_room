<?php

class RoomOptionsRepository extends DbRepository
{

	public function fetchByName_Date($name, $start_date, $end_date)
	{
		$sql = "SELECT
					op.id,		
					op.room_id,
					r.name,
					op.type,
					op.price_type_code,
					op.status,
					op.smoking_allowed_flg,
					op.pet_allowed_flg,
					op.barrier_free_flg,
					op.note,
					op.apply_start_date,
					op.apply_end_date,
					op.create_date,
					op.create_user_id,
					op.update_date,
					op.update_user_id 
				FROM room as r 
					INNER JOIN room_options as op
						ON r.id = op.room_id
				WHERE r.name = :name
						AND op.apply_start_date <= :start_date
						AND op.apply_end_date >= :end_date";

		return $this->fetch($sql, array(':name' => $name,
										':start_date' => $start_date,
										':end_date' => $end_date,
										));
	}




	public function fetchAllGoupByName($search_conditions)
	{

		$sql_where = '';
		$bind_params = array();
		$i = 0;
		foreach ($search_conditions as $col_name => $value) {
			if ('' !== $value) {
				if (0 < $i) {
					$sql_where .= " AND ";
				}
				$sql_where .= " {$col_name} = :{$col_name}";
				$bind_params[":{$col_name}"] = $value;
				$i++;
			}
		}
		if (0 !== strlen($sql_where)) {
			$sql_where = ' WHERE ' . $sql_where;
		}


		// $sql_where = ' WHERE type = :type ';
		$sql = "SELECT
					r.name,
					op.room_id
				FROM room as r 
					INNER JOIN room_options as op
						ON r.id = op.room_id
				{$sql_where}
				GROUP BY r.name, op.room_id
				ORDER BY lpad(r.name, 4 , '0')";

		return $this->fetchAll($sql, $bind_params);
		// return $this->fetchAll($sql, array(
		// 			':type' => $search_conditions['type'],
		// 			':status' => $search_conditions['status'],
		// 			':smoking_allowed_flg' => $search_conditions['smoking_allowed_flg'],
		// 			':pet_allowed_flg' => $search_conditions['pet_allowed_flg'],
		// 			':barrier_free_flg' => $search_conditions['barrier_free_flg'],
		// 			':note' => "%{$search_conditions['note']}%",
		// 			':snote' => "%{$search_conditions['note']}%",
		// 		));
	}



	public function fetchAllByApplyDate($start_date, $end_date)
	{
		$sql = "SELECT
					r.name,
					op.room_id
				FROM room as r 
					INNER JOIN room_options as op
						ON r.id = op.room_id 
						WHERE apply_start_date <= :start_date
							AND apply_end_date >= :start_date
							AND NOT EXISTS 
									(SELECT id FROM reserve 
										WHERE room_id = r.id 
										AND
										(:start_date between plan_start_date and plan_end_date
										OR :end_date between plan_start_date and plan_end_date)
										AND status = 1
									)
						ORDER BY lpad(r.name, 4 , '0')";
		
		return $this->fetchAll($sql, array(':start_date' => $start_date,
										   ':end_date' => $end_date,));
	}


	// ★★ これ要らねぇのか
	// public function fetchAllByApplyDateRoomId($start_date, $end_date, $room_id)
	// {
	// 	$sql = "SELECT
	// 				r.name,
	// 				op.room_id
	// 			FROM room as r 
	// 				INNER JOIN room_options as op
	// 					ON r.id = op.room_id 
	// 					WHERE apply_start_date <= :start_date
	// 						AND apply_end_date >= :start_date
	// 						AND NOT EXISTS 
	// 								(SELECT id FROM reserve 
	// 									WHERE room_id = r.id 
	// 									AND
	// 									(:start_date between plan_start_date and plan_end_date
	// 									OR :end_date between plan_start_date and plan_end_date)
	// 									AND status = 1
	// 									AND id = :room_id
	// 								)
	// 					ORDER BY lpad(r.name, 4 , '0')";
		
	// 	return $this->fetchAll($sql, array(':start_date' => $start_date,
	// 									   ':end_date' => $end_date,
	// 									   ':room_id' => $room_id,));
	// }




	public function fetchAllByName($name) 
	{
		$sql = "SELECT
					op.id,		
					op.room_id,
					r.name,
					op.type,
					op.price_type_code,
					op.status,
					op.smoking_allowed_flg,
					op.pet_allowed_flg,
					op.barrier_free_flg,
					op.note,
					op.apply_start_date,
					op.apply_end_date,
					op.create_date,
					op.create_user_id,
					op.update_date,
					op.update_user_id 
				FROM room as r 
					INNER JOIN room_options as op
						ON r.id = op.room_id 
						WHERE name = :name";
		
		return $this->fetchAll($sql, array(':name' => $name,));
	}

	public function fetchById($id)
	{
		$sql = "SELECT
					op.id,		
					op.room_id,
					r.name,
					op.type,
					op.price_type_code,
					op.status,
					op.smoking_allowed_flg,
					op.pet_allowed_flg,
					op.barrier_free_flg,
					op.note,
					op.apply_start_date,
					op.apply_end_date,
					op.create_date,
					op.create_user_id,
					op.update_date,
					op.update_user_id 
				FROM room as r 
					INNER JOIN room_options as op
						ON r.id = op.room_id
				WHERE op.id = :id";

		return $this->fetch($sql, array(':id' => $id,));
	}




	public function insert($operate_user_id, $inputs = array())
	{
		$now = new Datetime();

		$sql = "INSERT INTO room_options(
					room_id,
					type,
					price_type_code,
					status,
					smoking_allowed_flg,
					pet_allowed_flg,
					barrier_free_flg,
					note,
					apply_start_date,
					apply_end_date,
					create_date,
					create_user_id,
					update_date,
					update_user_id
				) 
				VALUES (
					:room_id,
					:type,
					:price_type_code,
					:status,
					:smoking_allowed_flg,
					:pet_allowed_flg,
					:barrier_free_flg,
					:note,
					:apply_start_date,
					:apply_end_date,
					:create_date,
					:create_user_id,
					:update_date,
					:update_user_id)";

		$stmt = $this->execute($sql, array(
			':room_id' => $inputs['room_id'],
			':type' => $inputs['type'],
			':price_type_code' => $inputs['price_type_code'],
			':status' => $inputs['status'],
			':smoking_allowed_flg' => $inputs['smoking_allowed_flg'],
			':pet_allowed_flg' => $inputs['pet_allowed_flg'],
			':barrier_free_flg' => $inputs['barrier_free_flg'],
			':note' => $inputs['note'],
			':apply_start_date' => $inputs['apply_start_date'],
			':apply_end_date' => $inputs['apply_end_date'],
			':create_date' => $now->format('Y-m-d H:i:s'),
			':create_user_id' => $operate_user_id,
			':update_date' => $now->format('Y-m-d H:i:s'),
			':update_user_id' => $operate_user_id,
		));
	}


	public function update($id, $operate_user_id, $inputs = array())
	{
		$now = new Datetime();

		$sql = "UPDATE room_options SET
					type = :type,
					price_type_code = :price_type_code,
					status = :status,
					smoking_allowed_flg = :smoking_allowed_flg,
					pet_allowed_flg = :pet_allowed_flg,
					barrier_free_flg = :barrier_free_flg,
					note = :note,
					apply_start_date = :apply_start_date,
					apply_end_date = :apply_end_date,
					update_date = :update_date,
					update_user_id = :update_user_id
				WHERE id = :id
		";

		$stmt = $this->execute($sql, array(
			':id' => $id,
			':type' => $inputs['type'],
			':price_type_code' => $inputs['price_type_code'],
			':status' => $inputs['status'],
			':smoking_allowed_flg' => $inputs['smoking_allowed_flg'],
			':pet_allowed_flg' => $inputs['pet_allowed_flg'],
			':barrier_free_flg' => $inputs['barrier_free_flg'],
			':note' => $inputs['note'],
			':apply_start_date' => $inputs['apply_start_date'],
			':apply_end_date' => $inputs['apply_end_date'],
			':update_date' => $now->format('Y-m-d H:i:s'),
			':update_user_id' => $operate_user_id
		));
	}


	public function updatePrevApplyEndDate($id, $apply_end_date)
	{
		$now = new Datetime();

		$sql = "UPDATE room_options SET
					apply_end_date = :apply_end_date
				WHERE id = :id
		";

		$stmt = $this->execute($sql, array(
			':id' => $id,
			':apply_end_date' => $apply_end_date,
		));
	}

	public function updateApplydate($id, $operate_user_id, $apply_start_date, $apply_end_date)
	{
		$now = new Datetime();

		$sql = "UPDATE room_options SET
					apply_start_date = :apply_start_date,
					apply_end_date = :apply_end_date,
					update_date = :update_date,
					update_user_id = :update_user_id				
				WHERE id = :id
		";

		$stmt = $this->execute($sql, array(
			':id' => $id,
			':apply_start_date' => $apply_start_date,
			':apply_end_date' => $apply_end_date,
			':update_date' => $now->format('Y-m-d H:i:s'),
			':update_user_id' => $operate_user_id
		));
	}



}