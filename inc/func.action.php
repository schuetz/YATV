<?php

require_once('var.YATV.php');
require_once('func.xss.php');

$action = xss_clean($_POST['action']);
$id = xss_clean($_POST['id']);
$primary = xss_clean($_POST['primary']);
$field = xss_clean($_POST['field']);
$value = xss_clean($_POST['value']);
if ($_POST['tbl']) {
	$tbl = xss_clean($_POST['tbl']);
} elseif (isset($tables_allowed)) {
	$tbl = $tables_allowed[0];
} else {
	$tbl = '';
}

if (!empty($tbl) && isset($tables) && !array_key_exists($tbl,$tables)) {
	exit;
}

$mysqli = new mysqli($db['host'],$db['user'],$db['pw'],$db['db']);

if ($action=='delete' && (!empty($tables[$tbl]['delete']) || $global_permissions['delete'])) {
	$sql_cmd = "DELETE from {$tbl} WHERE {$primary}='{$id}'";
} elseif ($action=='edit') {
	$sql_cmd = "UPDATE {$tbl} SET {$field}='{$value}' WHERE {$primary}='{$id}'";
}

$mysqli->set_charset('utf8');
$result = $mysqli->query($sql_cmd);

echo $result;

?>