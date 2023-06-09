<?php

// DBで管理しない管理者アカウント情報(緊急時やシステム稼働前に利用する)
const ROOT_USER_ID = -999;
// ★↓2行も工夫しよう
const ROOT_USER_LOGIN_ID = '';
const ROOT_PASSWORD = '';

const ROOT_USER_NAME = 'システム管理者';
const ROOT_USER_AUTH_TYPE = 1;

// アカウント一覧の1ページあたりの表示件数
const USER_CNT_PER_1PAGE = 3; 

// 部屋一覧・管理サイトの1ページあたりの表示件数
const ROOM_CNT_PER_1PAGE = 6;

// 部屋一覧・予約サイトの1ページあたりの表示件数
const ROOM_CNT_PER_1PAGE_RESERVESITE = 100; // ページング時に日付をpostできていないので暫定的にページング処理されないくらい大きな値にしておく

// 予約状況確認一覧の1ページあたりの表示件数
const RESERVELIST_CNT_PER_1PAGE = 30; 

// 顧客一覧の1ページあたりの表示件数
const CUSTOMER_USER_CNT_PER_1PAGE = 7; 

// 顧客ユーザのパスワード再設定時のauth_keyの有効期限を指定
// Datetimeクラスのmodifyメソッドの引数と同じ形式で指定する。
const VALID_PERIOD = '8 hour '; 



/**
 * アカウント(admin_userテーブル)の入力項目を定義。1階層目のキーはDBのカラム名と合わせる。
 * =======================================
 * ※★↓最新の内容にはなっていないかも。というか別でエクセルファイル作ってそこに記述したほうが良い。
 *   alias:画面上の項目名
 *   min_length：入力可能文字数の下限。バリデーションで使う。
 *   max_length：入力可能文字数の上限。バリデーションで使う。
 *   is_required：入力必須か否か。入力必須の場合はtrueとする。
 *   item_type：入力項目の種類。現在対応できるのは次の4つだけ。
 *              「text/passsword/textarea/selectbox/」
 *   is_inputtable：入力可能項目であるか否か。編集不可(参照のみ可能)の場合はfalseとする。
 *   ref_const_name：selectboxが参照する定数名。
 *   rows：textareaの高さ。
 *   cols：textareaの幅。
 *   size：selectboxの高さ。設定するとリストボックス表示になる。
 *   date_format：日付データの表示形式。phpのdate関数の第一引数と同じ形式で記述する。
 *   sort_order：項目同時の並び順制御用
 *  =======================================
 *  ※設定しないキーの場合は値を空文字にすること。
 */

const USER_ITEMS_PROPERTY = array (
	'id' =>
		array('alias' => '通番',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'date_format' => '',
			  'sort_order' => 10,
		),

	'name' =>
		array('alias' => '氏名',
			  'min_length' => '',
			  'max_length' => 50,
			  'is_required' => true,
			  'item_type' => 'text',
			  'is_inputtable' => true,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'date_format' => '',
			  'sort_order' => 20,
		),

	'login_id' =>
		array('alias' => 'ログインID',
			  'min_length' => '',
			  'max_length' => 50,
			  'is_required' => true,
			  'item_type' => 'text',
			  'is_inputtable' => true,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'date_format' => '',
			  'sort_order' => 30,
		),

	'password' =>
		array('alias' => 'パスワード',
			  'min_length' => '', //★←開発途中は面倒なので空文字にしておく。最終的には10にしようと思う。
			  'max_length' => 20,	
			  'is_required' => true,
			  'item_type' => 'password',
			  'is_inputtable' => true,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'date_format' => '',
			  'sort_order' => 40,
		),

	'auth_type' =>
		array('alias' => '権限種別',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => true,
			  'item_type' => 'selectbox',
			  'is_inputtable' => true,
			  'ref_const_name' => 'USER_AUTH_TYPES',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'date_format' => '',
			  'sort_order' => 50,
		),

	'note' =>
		array('alias' => '備考',
			  'min_length' => '',
			  'max_length' =>  255,
			  'is_required' => false,
			  'item_type' => 'textarea',
			  'is_inputtable' => true,
			  'ref_const_name' => '',
			  'rows' => 7,
			  'cols' => 60,
			  'placeholder' => '',
			  'size' => '',
			  'date_format' => '',
			  'sort_order' => 60,
		),

	'create_date' =>
		array('alias' => '作成日時',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
	 		  'date_format' => 'Y/m/d H:i:s',
	 		  'sort_order' => 910,
	),

	'create_user_id' =>
		array('alias' => '作成者ID',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
	 		  'date_format' => '',
	 		  'sort_order' => 920,
	),

	'update_date' =>
		array('alias' => '更新日時',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
	 		  'date_format' => 'Y/m/d H:i:s',
	 		  'sort_order' => 930,
	),

	'update_user_id' =>
		array('alias' => '更新者ID',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
	 		  'date_format' => '',
	 		  'sort_order' => 940,
	),

);

// admin_userテーブルから参照させる連想配列
const USER_AUTH_TYPES = array(
	2 => '一般',
	1 => '管理者',
);


/**
 * 部屋(roomテーブル)の入力項目を定義。1階層目のキーはDBのカラム名と合わせる。
 * ※img(画像)だけはDBでは管理しない列
 * edit_typeは「editable」「readonly」のいずれかを設定する。
 * 　　★悩ましいけど画面によって編集可/不可が変わる項目はここでは「readonly」にしておく。
 * 　　で、action側で「editable」にする＆viewファイル内で判定することにする。
 */
const ROOM_ITEMS_PROPERTY = array (
	'id' =>
		array('alias' => '部屋情報ID',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => '',
			  'sort_order' => 0,
			  'edit_type' => 'readonly',
		),

	'room_img' =>
		array('alias' => 'イメージ画像',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => '',
			  'item_type' => 'img',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '100px',
			  'height' => '100%',
			  'alt' => '部屋のロゴ',
			  'date_format' => '',
			  'sort_order' => 5,
			  'edit_type' => 'readonly',
		),

		'room_id' =>
		array('alias' => 'room_id',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',			  
			  'date_format' => '',
			  'sort_order' => 10,
			  'edit_type' => 'invisible',
		),

	'name' =>
		array('alias' => '部屋番号',
			  'min_length' => '',
			  'max_length' => '10',
			  'is_required' => true,
			  'item_type' => 'text',
			  'is_inputtable' => true,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => '',
			  'sort_order' => 20,
			  'edit_type' => 'readonly',
		),

	'type' =>
		array('alias' => '部屋タイプ',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => true,
			  'item_type' => 'selectbox',
			  'is_inputtable' => true,
			  'ref_const_name' => 'ROOM_TYPES',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => '',
			  'sort_order' => 30,
			  'edit_type' => 'editable',
		),

	'price_type_code' =>
		array('alias' => '価格タイプコード',
			  'min_length' => '',
			  'max_length' => '10',
			  'is_required' => false, // ホントはtrue
			  'item_type' => 'selectbox', // ホントはselectbox系
			  'is_inputtable' => true,
			  'ref_const_name' => '',
			  'ref_var_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => '',
			  'sort_order' => 40,
			  'edit_type' => 'editable',
		),

	'status' =>
		array('alias' => 'ステータス',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => true,
			  'item_type' => 'selectbox',
			  'is_inputtable' => true,
			  'ref_const_name' => 'ROOM_STATUSES',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => '',
			  'sort_order' => 50,
			  'edit_type' => 'editable',
		),

	'smoking_allowed_flg' =>
		array('alias' => '喫煙可否',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => true,
			  'item_type' => 'radio', //★要件等。まだradio作ってないし、項目を動的に表示される方法確立できてない。
			  'is_inputtable' => true,
			  'ref_const_name' => 'ROOM_SMOKING_ALLOWED_FLGS',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => '',
			  'sort_order' => 60,
			  'edit_type' => 'editable',
		),

	'pet_allowed_flg' =>
		array('alias' => 'ペット利用可否',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => true,
			  'item_type' => 'radio', //★要件等。まだradio作ってないし、項目を動的に表示される方法確立できてない。
			  'is_inputtable' => true,
			  'ref_const_name' => 'ROOM_PET_ALLOWED_FLGS',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => '',
			  'sort_order' => 70,
			  'edit_type' => 'editable',
		),

	'barrier_free_flg' =>
		array('alias' => 'バリアフリー有無',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => true,
			  'item_type' => 'radio', //★要件等。まだradio作ってないし、項目を動的に表示される方法確立できてない。
			  'is_inputtable' => true,
			  'ref_const_name' => 'ROOM_BARRIER_FREE_FLGS',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',			  
			  'date_format' => '',
			  'sort_order' => 80,
			  'edit_type' => 'editable',
		),

	'note' =>
		array('alias' => '備考',
			  'min_length' => '',
			  'max_length' =>  '255',
			  'is_required' => false,
			  'item_type' => 'textarea',
			  'is_inputtable' => true,
			  'ref_const_name' => '',
			  'rows' => 7,
			  'cols' => 60,
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => '',
			  'sort_order' => 90,
			  'edit_type' => 'editable',
		),

	'apply_start_date' =>
		array('alias' => '適用開始日',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => true,
			  'item_type' => 'calendar',
			  'is_inputtable' => true,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => 'Y/m/d',
			  'sort_order' => 100,
			  'edit_type' => 'editable',
		),

	'apply_end_date' =>
		array('alias' => '適用終了日',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => true,
			  'item_type' => 'calendar', //★要件等。まだradio作ってないし、項目を動的に表示される方法確立できてない。
			  'is_inputtable' => true,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => 'Y/m/d',
			  'sort_order' => 110,
			  'edit_type' => 'editable',
		),

	'create_date' =>
		array('alias' => '作成日時',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
	 		  'date_format' => 'Y/m/d H:i:s',
	 		  'sort_order' => 910,
	 		  'edit_type' => 'readonly',
	),

	'create_user_id' =>
		array('alias' => '作成者ID',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
	 		  'date_format' => '',
	 		  'sort_order' => 920,
	 		  'edit_type' => 'readonly',
	),

	'update_date' =>
		array('alias' => '更新日時',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
	 		  'date_format' => 'Y/m/d H:i:s',
	 		  'sort_order' => 930,
	 		  'edit_type' => 'readonly',
	),

	'update_user_id' =>
		array('alias' => '更新者ID',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
	 		  'date_format' => '',
	 		  'sort_order' => 940,
	 		  'edit_type' => 'readonly',
	),

);

// roomテーブルから参照させる連想配列
const ROOM_TYPES = array(
	1 => 'シングル',
	2 => 'ダブル',
	3 => 'ツイン',
);
const ROOM_STATUSES = array(
	1 => '利用可',
	2 => '利用不可',
);
const ROOM_SMOKING_ALLOWED_FLGS = array(
	0 => '不可',
	1 => '可',
);
const ROOM_PET_ALLOWED_FLGS = array(
	0 => '不可',
	1 => '可',
);
const ROOM_BARRIER_FREE_FLGS = array(
	0 => '無し',
	1 => '有り',
);

const ROOM_APPLY_END_DATE = '2199-12-31';


/**
 * 予約状況確認の項目を定義。1階層目のキーはDBのカラム名と合わせる。
 * 
 */
const RESERVELIST_ITEMS_PROPERTY = array (
	'id' =>
		array('alias' => '予約ID',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => '',
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => '',
			  'sort_order' => 0,
			  'edit_type' => 'invisible',
			  'kind' => '',
		),

		'plan_start_date' =>
		array('alias' => '予定宿泊開始日',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => '',
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',			  
			  'date_format' => 'Y/m/d',
			  'sort_order' => 10,
			  'edit_type' => 'readonly',
			  'kind' => '',
		),


		'plan_end_date' =>
		array('alias' => '予定宿泊終了日',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => '',
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',			  
			  'date_format' => 'Y/m/d',
			  'sort_order' => 20,
			  'edit_type' => 'readonly',
			  'kind' => '',
		),		


		'room_id' =>
		array('alias' => 'room_id',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => 'false',
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',			  
			  'date_format' => '',
			  'sort_order' => 25,
			  'edit_type' => 'invisible',
			  'kind' => '',
		),

		'room_name' =>
		array('alias' => '部屋番号',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => '',
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',			  
			  'date_format' => '',
			  'sort_order' => 30,
			  'edit_type' => 'readonly',
			  'kind' => '',
		),

		'user_id' =>
		array('alias' => 'user_id',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => 'false',
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',			  
			  'date_format' => '',
			  'sort_order' => 35,
			  'edit_type' => 'invisible',
			  'kind' => '',
		),

		'customer_name' =>
		array('alias' => '顧客氏名',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => '',
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',			  
			  'date_format' => '',
			  'sort_order' => 40,
			  'edit_type' => 'readonly',
			  'kind' => '',
		),

		'plan_count' =>
		array('alias' => '予定宿泊人数',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => '',
			  'item_type' => 'selectbox',
			  'is_inputtable' => '',
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',			  
			  'date_format' => '',
			  'sort_order' => 41,
			  'edit_type' => 'readonly',
			  'kind' => '',
		),	

		'plan_price' =>
		array('alias' => '予定宿泊料金(総額)',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => '',
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',			  
			  'date_format' => '',
			  'sort_order' => 50,
			  'edit_type' => 'readonly',
			  'kind' => 'money',	// ★表示形式変える為に使う
		),


		'status' =>
		array('alias' => 'ステータス',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => 'true',
			  'item_type' => 'selectbox',
			  'is_inputtable' => 'true',
			  'ref_const_name' => 'RESERVE_STATUSES',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',			  
			  'date_format' => '',
			  'sort_order' => 55,
			  'edit_type' => 'readonly',
			  'kind' => '',
		),	



		'start_date' =>
		array('alias' => '宿泊開始日',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => '',
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',			  
			  'date_format' => 'Y/m/d',
			  'sort_order' => 60,
			  'edit_type' => 'readonly',
			  'kind' => '',
		),		

		'end_date' =>
		array('alias' => '宿泊終了日',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => '',
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',			  
			  'date_format' => 'Y/m/d',
			  'sort_order' => 70,
			  'edit_type' => 'readonly',
			  'kind' => '',
		),		

		'count' =>
		array('alias' => '宿泊人数',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => '',
			  'item_type' => 'selectbox',
			  'is_inputtable' => '',
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',			  
			  'date_format' => '',
			  'sort_order' => 71,
			  'edit_type' => 'readonly',
			  'kind' => '',
		),	
				
		'price' =>
		array('alias' => '宿泊料金',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => '',
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',			  
			  'date_format' => '',
			  'sort_order' => 80,
			  'edit_type' => 'readonly',
			  'kind' => '',
		),		

		'note' =>
		array('alias' => '備考',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => '',
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',			  
			  'date_format' => '',
			  'sort_order' => 90,
			  'edit_type' => 'readonly',
			  'kind' => '',
		),

	'create_date' =>
		array('alias' => '作成日時',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
	 		  'date_format' => 'Y/m/d H:i:s',
	 		  'sort_order' => 910,
	 		  'edit_type' => 'readonly',
	 		  'kind' => '',
	),

	'create_user_id' =>
		array('alias' => '作成者ID',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
	 		  'date_format' => '',
	 		  'sort_order' => 920,
	 		  'edit_type' => 'readonly',
	 		  'kind' => '',
	),

	'update_date' =>
		array('alias' => '更新日時',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
	 		  'date_format' => 'Y/m/d H:i:s',
	 		  'sort_order' => 930,
	 		  'edit_type' => 'readonly',
	 		  'kind' => '',
	),

	'update_user_id' =>
		array('alias' => '更新者ID',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
	 		  'date_format' => '',
	 		  'sort_order' => 940,
	 		  'edit_type' => 'readonly',
	 		  'kind' => '',
	),			
);

const RESERVE_STATUSES = array(
	1 => '予約済',
	2 => 'チェックイン済',
	3 => 'チェックアウト済',
	9 => 'キャンセル済',
);




/**
 * 顧客(customer_userテーブル)の入力項目を定義。
 * 
 */
const CUSTOMER_USER_ITEMS_PROPERTY = array (
	'id' =>
		array('alias' => '通番',
			  'min_length' => '',
			  'max_length' => '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => '',
			  'sort_order' => 0,
			  'edit_type' => 'invisible',
		),

	'name' =>
		array('alias' => '氏名',
			  'min_length' => '',
			  'max_length' => '50',
			  'is_required' => true,
			  'item_type' => 'text',
			  'is_inputtable' => true,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => '',
			  'sort_order' => 5,
			  'edit_type' => 'editable',
		),

		'furigana' =>
		array('alias' => 'フリガナ',
			  'min_length' => '',
			  'max_length' => '50',
			  'is_required' => true,
			  'item_type' => 'text',
			  'is_inputtable' => true,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',			  
			  'date_format' => '',
			  'sort_order' => 10,
			  'edit_type' => 'editable',
		),

	'tel' =>
		array('alias' => '電話番号',
			  'min_length' => '',
			  'max_length' => '15',
			  'is_required' => true,
			  'item_type' => 'text',
			  'is_inputtable' => true,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => 'ハイフン無し15文字以内',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => '',
			  'sort_order' => 20,
			  'edit_type' => 'editable',
		),

	'email' =>
		array('alias' => 'メールアドレス',
			  'min_length' => '',
			  'max_length' => '100',
			  'is_required' => true,
			  'item_type' => 'text',
			  'is_inputtable' => true,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => '',
			  'sort_order' => 30,
			  'edit_type' => 'editable',
		),

	'password' =>
		array('alias' => 'パスワード',
			  'min_length' => '1', //★←開発途中は面倒なので1文字にしておく。最終的には10文字にしようと思う。
			  'max_length' => '20',
			  'is_required' => true,
			  'item_type' => 'password',
			  'is_inputtable' => true,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => '',
			  'sort_order' => 40,
			  'edit_type' => 'editable',
		),

	'post_number' =>
		array('alias' => '郵便番号',
			  'min_length' => '',
			  'max_length' => '7',
			  'is_required' => true,
			  'item_type' => 'text',
			  'is_inputtable' => true,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => 'ハイフン無し7文字',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => '',
			  'sort_order' => 50,
			  'edit_type' => 'editable',
		),

	'pref' =>
		array('alias' => '都道府県',
			  'min_length' => '',
			  'max_length' =>  '10',
			  'is_required' => true,
			  'item_type' => 'selectbox',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => '',
			  'sort_order' => 60,
			  'edit_type' => 'editable',
		),

	'city' =>
		array('alias' => '市区町村',
			  'min_length' => '',
			  'max_length' =>  '30',
			  'is_required' => true,
			  'item_type' => 'selectbox',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => '',
			  'sort_order' => 70,
			  'edit_type' => 'editable',
		),

	'address1' =>
		array('alias' => '住所',
			  'min_length' => '',
			  'max_length' =>  '100',
			  'is_required' => true,
			  'item_type' => 'text',
			  'is_inputtable' => true,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',			  
			  'date_format' => '',
			  'sort_order' => 80,
			  'edit_type' => 'editable',
		),

	'address2' =>
		array('alias' => '建物名・部屋番号等',
			  'min_length' => '',
			  'max_length' =>  '100',
			  'is_required' => false,
			  'item_type' => 'text',
			  'is_inputtable' => true,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => '',
			  'sort_order' => 90,
			  'edit_type' => 'editable',
		),

	'note' =>
		array('alias' => '備考',
			  'min_length' => '',
			  'max_length' =>  '255',
			  'is_required' => true,
			  'item_type' => 'textarea',
			  'is_inputtable' => true,
			  'ref_const_name' => '',
			  'rows' => 7,
			  'cols' => 60,
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
			  'date_format' => 'Y/m/d',
			  'sort_order' => 100,
			  'edit_type' => 'editable',
		),

	'create_date' =>
		array('alias' => '作成日時',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
	 		  'date_format' => 'Y/m/d H:i:s',
	 		  'sort_order' => 910,
	 		  'edit_type' => 'readonly',
	),

	'create_user_id' =>
		array('alias' => '作成者ID',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
	 		  'date_format' => '',
	 		  'sort_order' => 920,
	 		  'edit_type' => 'readonly',
	),

	'update_date' =>
		array('alias' => '更新日時',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
	 		  'date_format' => 'Y/m/d H:i:s',
	 		  'sort_order' => 930,
	 		  'edit_type' => 'readonly',
	),

	'update_user_id' =>
		array('alias' => '更新者ID',
			  'min_length' => '',
			  'max_length' =>  '',
			  'is_required' => '',
			  'item_type' => 'text',
			  'is_inputtable' => false,
			  'ref_const_name' => '',
			  'rows' => '',
			  'cols' => '',
			  'placeholder' => '',
			  'size' => '',
			  'width' => '',
			  'height' => '',
			  'alt' => '',
	 		  'date_format' => '',
	 		  'sort_order' => 940,
	 		  'edit_type' => 'readonly',
	),

);
