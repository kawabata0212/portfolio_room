<?php
// ★本来はフレームワークを利用したいがうまくいかないのでひとまずこの方法で実装
// require dirname(dirname(__DIR__)) . '/room-mng/conf/db_config.php';

function get_city($pref_id)
{
		// ★★上記の通りフレームワークつかえなソースコード公開する際はDB接続情報空文字にする！！
		$dns = '';
		$user = '';
		$password = '';

		try {
			$pdo = new PDO($dns, $user, $password);
			// echo 'データベースの接続に成功しました。';
		} catch(PDOException $e) {
			// exit('データベースの接続に失敗しました。' .$e->getMessage());
		}


		$sql = 'select city_id, city_name from city where pref_id = :pref_id;';

		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':pref_id', $pref_id, PDO::PARAM_INT);

		$stmt->execute();

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// $results = $city_repository->fetchAllRow($pref_id);

		return $results;
}

header("Content-Type: application/json; charset=UTF-8");
$ary_sel_obj = [];
$opt = filter_input(INPUT_POST,"opt");
$ary_sel_obj = get_city($opt);
echo json_encode($ary_sel_obj);
exit;

?>
