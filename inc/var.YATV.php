<?php

$headline			= 'YATV Headline';
$file_prefix		= 'YATV_';
$label_download		= 'Tabelle downloaden';
$label_anzahl		= 'Anzahl Datensätze: %s';
$page_limit			= 50;
$paginationstyle	= 'select'; // 'select','ol'
$order				= '';
$sort				= '';
$action				= '';

$db = [
	'host'	=> 'localhost',
	'user'	=> 'root',
	'pw'	=> 'root',
	'db'	=> 'asd'
];

$global_permissions = [
	'delete'		=> false,
	'edit'			=> false,
	'edit_multi'	=> false,
	'custom_export'	=> false
];

// SHORTHAND (see below; edit = 0, delete = 0)
//$tables = ['bookings','contacts'];

/*
$tables = [
	'bookings' => [
		'cols_allow'	=> '',
		'edit_allow'	=> '',
		'edit_lock'		=> '',
		'order'			=> '',
		'sort'			=> '',
		'delete'		=> false,
		'edit_multi'	=> false,
		'edit_all'		=> false
	],
	'contacts' => [
		'cols_allow'	=> 'contact_id,first_name,last_name',
		'edit_allow'	=> 'first_name',
		'edit_lock'		=> '',
		'order'			=> '',
		'sort'			=> '',
		'delete'		=> false,
		'edit_multi'	=> false,
		'edit_all'		=> false
	]
];
*/

?>