<?php

class ReserveRepository extends DbRepository
{
	public function fetchAllRow()
	{
		$sql = "SELECT
					reserve.id,
					reserve.user_id,
					reserve.room_id,
					reserve.status,
					reserve.plan_start_date,
					reserve.plan_end_date,
					reserve.plan_price,
					reserve.start_date,
					reserve.end_date,
					reserve.price,
					reserve.note,
					reserve.create_date,
					reserve.create_user_id,
					reserve.update_date,
					reserve.update_user_id,
					room.name as room_name,
					c.name as customer_name
				FROM reserve
					INNER JOIN room 
						on room.id = reserve.room_id
					INNER JOIN customer_user as c
						on c.id = reserve.user_id
					ORDER BY reserve.plan_start_date, room.name , reserve.create_date";
					
		return $this->fetchAll($sql,);
	}


	public function fetchAllByUser_id($user_id)
	{
		$sql = "SELECT
					reserve.id,
					reserve.user_id,
					reserve.room_id,
					reserve.status,
					reserve.plan_start_date,
					reserve.plan_end_date,
					reserve.plan_price,
					reserve.start_date,
					reserve.end_date,
					reserve.price,
					reserve.note,
					reserve.create_date,
					reserve.create_user_id,
					reserve.update_date,
					reserve.update_user_id,
					room.name as room_name,
					c.name as customer_name
				FROM reserve
					INNER JOIN room 
						on room.id = reserve.room_id
					INNER JOIN customer_user as c
						on c.id = reserve.user_id
				WHERE reserve.user_id = :user_id
					ORDER BY reserve.start_date, room.name";
					
		return $this->fetchAll($sql, array(':user_id' => $user_id,));
	}


	public function fetchById($reserve_id)
	{
		$sql = "SELECT
					reserve.id,
					reserve.user_id,
					reserve.room_id,
					reserve.status,
					reserve.plan_start_date,
					reserve.plan_end_date,
					reserve.plan_price,
					reserve.start_date,
					reserve.end_date,
					reserve.price,
					reserve.note,
					reserve.create_date,
					reserve.create_user_id,
					reserve.update_date,
					reserve.update_user_id,
					room.name as room_name,
					c.name as customer_name
				FROM reserve
					INNER JOIN room 
						on room.id = reserve.room_id
					INNER JOIN customer_user as c
						on c.id = reserve.user_id
				WHERE reserve.id = :reserve_id
					ORDER BY reserve.start_date, room.name";
					
		return $this->fetch($sql, array(':reserve_id' => $reserve_id),);

	}


	public function fetchByStartDateEndDateRoomId($start_date, $end_date, $room_id)
	{
		$sql = "SELECT
					id,
					room_id
				FROM reserve
				WHERE (:start_date between plan_start_date and plan_end_date
				   OR :end_date between plan_start_date and plan_end_date)
				   AND room_id = :room_id
				   AND status = 1;
			   ";
					
		return $this->fetch($sql, array(':start_date' => $start_date,
										':end_date' => $end_date,
										':room_id' => $room_id,));

	}




	public function insert($user_id, $start_date, $end_date, $inputs = array(), $Price_type = array())
	{
		$now = new Datetime();

		$sql = "INSERT INTO reserve(
					user_id,
					room_id,
					status,
					smoking_allowed_flg,
					pet_allowed_flg,
					barrier_free_flg,
					plan_start_date,
					plan_end_date,
					plan_count,
					plan_price,
					start_date,
					end_date,
					count,
					price,
					note,
					create_date,
					create_user_id,
					update_date,
					update_user_id
				) 
				VALUES (
					:user_id,
					:room_id,
					:status,
					:smoking_allowed_flg,
					:pet_allowed_flg,
					:barrier_free_flg,
					:plan_start_date,
					:plan_end_date,
					:plan_count,
					:plan_price,
					:start_date,
					:end_date,
					:count,
					:price,
					:note,
					:create_date,
					:create_user_id,
					:update_date,
					:update_user_id
				)";

		$stmt = $this->execute($sql, array(
								':user_id' => $user_id,
								':room_id' => $inputs['room_id'],
								':status' => 1,
								':smoking_allowed_flg' => $inputs['smoking_allowed_flg'],
								':pet_allowed_flg' => $inputs['pet_allowed_flg'],
								':barrier_free_flg' => $inputs['barrier_free_flg'],
								':plan_start_date' => $start_date,
								':plan_end_date' => $end_date,
								':plan_count' => $Price_type['amount'],
								':plan_price' => $Price_type['price'] * 
												 $Price_type['amount'] *
												 (strtotime($end_date) - strtotime($start_date)) / 86400, // 宿泊日数
								':start_date' => null,
								':end_date' => null,
								':count' => null,
								':price' => null,
								':note' => null,
								':create_date' => $now->format('Y-m-d H:i:s'),
								':create_user_id' => $user_id,
								':update_date' => $now->format('Y-m-d H:i:s'),
								':update_user_id' => $user_id,
		));
	}



	/**
	*　ユーザによる予約キャンセル処理
	*/
	public function updateForCancel($reserve_id)
	{
		$sql = "UPDATE reserve SET status = 9
				WHERE id =" . $reserve_id;

		$stmt = $this->execute($sql,);
	}


	/**
	* ユーザ退会に伴う、「予約済」から「キャンセル済」への更新
	*/
	public function updateCauseWithdrawal($user_id)
	{
		$sql = "UPDATE reserve SET status = 9
				WHERE id =" . $user_id .
				" AND status = 1";

		$stmt = $this->execute($sql,);
	}

	
}
