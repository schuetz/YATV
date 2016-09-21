<?php

$headline			= 'YATV Headline';
$file_prefix		= 'YATV_';
$label_download		= 'Tabelle downloaden';
$label_anzahl		= 'Anzahl Datensätze: %s';
$page_limit			= 100;
$paginationstyle	= 'select'; // 'select','ol'
$order				= '';
$sort				= '';
$action				= '';

$db = [
	'host'	=> 'localhost',
	'user'	=> 'root',
	'pw'	=> 'root',
	'db'	=> 'colobit'
];

$global_permissions = [
	'delete'		=> false,
	'edit'			=> false,
	'edit_multi'	=> true,
	'custom_export'	=> false
];

// SHORTHAND (see below; edit = 0, delete = 0)
//$tables = ['bookings','categories'];

/*
$tables = [
	'highscore' => [
		'cols_allow'	=> '',
		'edit_allow'	=> 'score',
		'edit_lock'		=> '',
		'order'			=> '',
		'sort'			=> '',
		'delete'		=> 0,
		'edit_multi'	=> 0,
		'edit_all'		=> 0
	],
	'partner' => [
		'cols_allow'	=> '',
		'edit_allow'	=> '',
		'edit_lock'		=> 'hash',
		'order'			=> '',
		'sort'			=> '',
		'delete'		=> 0,
		'edit_multi'	=> 0,
		'edit_all'		=> 0
	]
];
*/

?>