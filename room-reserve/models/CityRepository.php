<?php

class CityRepository extends DbRepository
{
	public function fetchAllPrefGroup()
	{
		$sql = "SELECT 
					pref_id as id,
					pref_name as value
				FROM city
				GROUP BY
					pref_id,
					pref_name";

		return $this->fetchALL($sql, );
	}

	public function fetchAllRow()
	{
		$sql = "SELECT
					city_id as id,
					city_name as value
				FROM city ";

		return $this->fetchALL($sql, );
	}

}