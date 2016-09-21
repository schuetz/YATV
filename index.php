<?php

require_once('inc/var.YATV.php');

/*	========================================================================
		preparing
	======================================================================== */

$protocol = (!empty($_SERVER['HTTPS'])) ? 'https://' : 'http://';
$server = $protocol.$_SERVER['SERVER_NAME'];
$dir = dirname($_SERVER['PHP_SELF']);
$self = $server.$dir.'/';

if (isset($tables)) {
	if (is_array(current($tables))) {
		$tables_allowed = array_keys($tables);
		$tbl_cfg = true;
	} else {
		$tables_allowed = $tables;
		$tbl_cfg = false;
	}
}

/*	====================================
		get parameters
	==================================== */
	
require_once('inc/func.xss.php');
$parameter = [];

if (isset($_GET['tbl'])) {
	$tbl = xss_clean($_GET['tbl']);
	$parameter['tbl'] = $tbl;
} elseif (isset($tables_allowed)) {
	$tbl = $tables_allowed[0];
	$parameter['tbl'] = $tbl;
} else {
	$tbl = '';
}
if (isset($_GET['action'])) {
	$action = xss_clean($_GET['action']);
	$parameter['action'] = $action;
}
if (isset($_GET['order'])) {
	$order = xss_clean($_GET['order']);
	$parameter['order'] = $order;
} elseif (isset($tables[$tbl]['order']) && !empty($tables[$tbl]['order'])) {
	$order = $tables[$tbl]['order'];
	$parameter['order'] = $order;
}
if (isset($_GET['sort'])) {
	$sort = xss_clean($_GET['sort']);
	$parameter['sort'] = $sort;
} elseif (isset($tables[$tbl]['sort']) && !empty($tables[$tbl]['sort'])) {
	$sort = $tables[$tbl]['sort'];
	$parameter['sort'] = $sort;
}
if (isset($_GET['page'])) {
	$page = xss_clean($_GET['page'])-1;
	$parameter['page'] = $page+1;
} else {
	$page = 0;
}

if (isset($_GET['val'])) {
	$cond_val = xss_clean($_GET['val']);
}
if (isset($_GET['col'])) {
	$cond_col = xss_clean($_GET['col']);
}



if (!empty($tbl) && isset($tables)) {
	if (($tbl_cfg && !array_key_exists($tbl,$tables)) || (!$tbl_cfg && !in_array($tbl,$tables))) {
		exit;
	}
}

if (!empty($tables[$tbl])) {
	$edit_allowed = explode(',',$tables[$tbl]['edit_allow']);
	$edit_multi = $tables[$tbl]['edit_multi'];
	$edit_all = $tables[$tbl]['edit_all'];
	$edit_locked = explode(',',$tables[$tbl]['edit_lock']);
	$cols_allowed = (!empty($tables[$tbl]['cols_allow'])) ? $tables[$tbl]['cols_allow'] : '*';
} else {
	$edit_allowed = $global_permissions['edit'] ? '*' : '';
	$edit_multi = $global_permissions['edit_multi'];
	$edit_all = $global_permissions['edit'];
	$edit_locked = [];
	$cols_allowed = '*';
}

$export_filename = $file_prefix.$tbl.'.xls';


/*	====================================
		setup db-query
	==================================== */

$mysqli = new mysqli($db['host'],$db['user'],$db['pw'],$db['db']);

$sql = "SELECT $cols_allowed FROM $tbl";

if ($global_permissions['custom_export'] && !empty($cond_val) && !empty($cond_col)) {
	$sql .= " WHERE $cond_col='$cond_val'";
}

$sql .= ($order && $sort) ? " ORDER BY $order $sort" : "";


/*	========================================================================
		download
	======================================================================== */

if ($action=='download') {
	
	$result = $mysqli->query($sql);

	if ($result) {
	
		$num_fields = $result->field_count;
		$field = $result->fetch_fields();
		
		require('inc/class.export-xls.php');
		$xls = new ExportXLS($export_filename);
		
		$header = null;
		$xls->addHeader($header);
		
		foreach ($field as $val) {
			$header[] = $val->name;
		}
		
		$xls->addHeader($header);
		
		$header = null;
		$xls->addHeader($header);
		
		while ($row = $result->fetch_array()) {
			$reihe = [];
			for ($i=0;$i<$num_fields;$i++) {
				$reihe[] = $row[$i];
			}
			$xls->addRow($reihe);
		}
		
		$xls->sendFile();
	}
	
/*	========================================================================
		show table
	======================================================================== */
	
} else {

	echo (
	'<!DOCTYPE html>
		<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>'.$headline.'</title>
		<base href="'.$self.'">
		<link rel="stylesheet" href="css/normalize.min.css" type="text/css">
		<link rel="stylesheet" href="css/YATV.css" type="text/css">
		<script type="text/javascript" src="js/jquery-1.11.0.min.js"></script>
		<script type="text/javascript" src="js/YATV.js"></script>
		</head>
		
		<body>'
	);
	
	/*	====================================
			table-select
		==================================== */
	
	$result_tables = $mysqli->query('SHOW TABLES');
	$html_tblselect = '<select size="1" id="tblselect">';
	if (!isset($tables_allowed) || empty($tables_allowed)) {
		while ($row_tables = $result_tables->fetch_row()) {
			$html_tblselect .= ($row_tables[0]==$tbl) ? '<option selected>'.$row_tables[0].'</option>' : '<option>'.$row_tables[0].'</option>';
		}
	} else {
		foreach($tables_allowed as $table) {
			$html_tblselect .= ($table==$tbl) ? '<option selected>'.$table.'</option>' : '<option>'.$table.'</option>';
		}
	}
	$html_tblselect .= '</select>';
	
	if (empty($tbl)) {
		$result_tables->data_seek(0);
		$r = $result_tables->fetch_row();
		$tbl = $r[0];
		$sql = ($order && $sort) ? "SELECT $cols_allowed FROM $tbl ORDER BY $order $sort" : "SELECT $cols_allowed FROM $tbl";
	}
	
	/*	====================================
		pagination
	==================================== */
	
	if (!empty($page_limit) && $action!='download') {
		$result_all = $mysqli->query($sql);
		if ($result_all) {
			$num_rows = $result_all->num_rows;
			$pages = ceil($num_rows/$page_limit);
			$start = $page*$page_limit;
			if ($start>=$num_rows) {
				$start = $num_rows-($num_rows%$page_limit);
			}
			$sql .= ' LIMIT '.$start.','.$page_limit;
			$pagination = ($paginationstyle=='select') ? '<select id="pagination">' : '<ol class="pagination">';
			for ($i=1;$i<=$pages;$i++) {
				$parameter['page'] = $i;
				$query = '?'.http_build_query($parameter);
				if ($paginationstyle=='select') {
					$active = ($i==$page+1) ? ' selected' : '';
					$pagination .= '<option'.$active.' value="'.$query.'">'.$i.'</option>';
				} else {
					$active = ($i==$page+1) ? 'class="active"' : '';
					$pagination .= '<li '.$active.'><a href="'.$query.'">'.$i.'</a></li>';
				}
			}
			$parameter['page'] = $page+1;
			$pagination .= ($paginationstyle=='select') ? '</select>' : '</ol>';
		}
	}
	
	/*	====================================
			result
		==================================== */
	
	$mysqli->set_charset('utf8');
	$result = $mysqli->query($sql);
	
	if ($result) {
		
		if (!isset($num_rows)) {
			$num_rows = $result->num_rows();
		}
		$num_fields = $result->field_count;
		$field = $result->fetch_fields();
		$show_index = $mysqli->query("SHOW INDEX FROM $tbl WHERE Key_name = 'PRIMARY'");
		$primary_key = $show_index->fetch_assoc();
		$primary_key = $primary_key['Column_name'];
		
		// header
		echo ('<h1>'.$headline.'</h1><header>'.$html_tblselect);
		if ($num_rows>$page_limit) echo $pagination;
		printf ('<span>'.$label_anzahl.'</span>', '<span class="count">'.$num_rows.'</span>');
		echo ('<span><a href="'.$self.'?tbl='.$tbl.'&action=download" class="download" target="_blank">'.$label_download.'</a></span>');
		
		if ($global_permissions['custom_export']) {
			echo ('<form action="'.$self.'" method="get">
					<p>
					<select name="action">
						<option value="">Zeige</option>
						<option value="download">Exportiere</option>
					</select> alle Datensätze mit dem Wert:
					<input name="val" type="text" />
					in Spalte:
					<input name="col" type="text" />
					<input type="hidden" name="tbl" value="'.$tbl.'"/>
					<input type="submit" value="Los!"/>
					</p>
					</form>'
				);
		}
		echo '</header>';
		
		echo ('<table data-table="'.$tbl.'" data-multiedit="'.$edit_multi.'" data-primary="'.$primary_key.'">');
		
		// table-header
		echo ('<thead><tr class="titles">');
		if (!empty($tables[$tbl]['delete']) || $global_permissions['delete']) echo ('<th>&nbsp;</th>');
		
		foreach ($field as $val) {
			$field_name = $val->name;
			$parameter['order'] = $field_name;
			$query = '?'.http_build_query($parameter);
			if ($order==$field_name && $sort=='asc') {
				$parameter['sort'] = '';
				$query = '?'.http_build_query($parameter);
				echo ('<th>'.$field_name.' <a href="'.$query.'&sort=asc" class="active">&uarr;</a> <a href="'.$query.'&sort=desc">&darr;</a></th>');
			} elseif ($order==$field_name && $sort=='desc') {
				$parameter['sort'] = '';
				$query = '?'.http_build_query($parameter);
				echo ('<th>'.$field_name.' <a href="'.$query.'&sort=asc">&uarr;</a> <a href="'.$query.'&sort=desc" class="active">&darr;</a></th>');
			} else {
				echo ('<th>'.$field_name.' <a href="'.$query.'&sort=asc">&uarr;</a> <a href="'.$query.'&sort=desc">&darr;</a></th>');
			}
		}
		echo ('</tr></thead><tbody>');
		
		// table-body
		while ($row = $result->fetch_array(MYSQLI_BOTH)) {
			echo ('<tr data-id="'.$row[$primary_key].'">');
			if (!empty($tables[$tbl]['delete']) || $global_permissions['delete']) {
				echo ('<td><a href="#" class="ir button delete" data-action="delete">delete</a></td>');
			}
			$field = $result->fetch_fields();
			foreach ($field as $val) {
				$field_name = $val->name;
				$i = $field_name;
				//$edit_all = (!empty($tables[$tbl])) ? $tables[$tbl]['edit_all'] : $global_permissions['edit_all'];
				if (
					(isset($tables) && array_key_exists($tbl,$tables) ) && 
					in_array($field_name,$edit_allowed) && 
					($row[$i]!='' || $tables[$tbl]['edit_multi']==1) || 
					($global_permissions['edit'] && !in_array($field_name,$edit_locked)) || 
					($edit_all && !in_array($field_name,$edit_locked))
				) {
					printf ('<td><input type="text" value="%s"><a href="#" class="ir button edit" data-field="'.$field_name.'" data-action="edit">edit</a></td>', $row[$i]);
				} else {
					printf ('<td>%s</td>', htmlentities($row[$i]));
				}
			}
			echo ('</tr>');
		}
		echo ('</tbody></table>');
		
		$result->free();
		$mysqli->close();
		
	} else {
	
		echo ('<h1>Error</h1>');
		
	}
	
	echo (
		'</body>
		</html>'
	);
	
}
           
           
?>
            