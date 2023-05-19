<?php

class Func
{

	public static function getValue($code, $ref_list)
	{
		// $result = $ref_const_name[$code];
		// echo $ref_const_name;

		if (is_array($ref_list)) {
			$result = $ref_list[$code];
		} else {
			$result = constant($ref_list)[$code];
		}

		return $result;
	}

}

// public static function getValue($colname, $code, $code_table_name)
// {
// 	$result = $code_table_name[$code];
// 	return $result;
// }

// function test()
// {
// 	echo 'test';
// }
