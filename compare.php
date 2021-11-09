<?php

$hacked_db = mysqli_connect('10.0.1.13', 'oakwoodescrow', '1XUdtCIOuObY2cQuDlyu', 'wp_oakwoodescrow');
$original_db = mysqli_connect('10.0.1.13', 'oakwoodescrow', '1XUdtCIOuObY2cQuDlyu', 'wp_oakwoodescrow2');

if (!$hacked_db) die('Failed to connect to db 1' . PHP_EOL);
if (!$original_db) die('Failed to connect to db 2' . PHP_EOL);

$command = isset($argv[1]) ? $argv[1] : null;

$usage = $argv[0] . ' command [options]' . PHP_EOL;
$usage .= PHP_EOL;
$usage .= 'COMMANDS: ' . PHP_EOL;
$usage .= '  compare' . PHP_EOL;
$usage .= '  struct table-name  - Show table properties in both databases' . PHP_EOL;
$usage .= '  tables             - Show missing tables in both databases' . PHP_EOL;

$tables_info = [
//	| attachment         |     1160 |
//	| custom_css         |        2 |
//	| elementor_library  |       78 |
//	| envato_kits        |        6 |
//	| frm_form_actions   |        2 |
//	| frm_styles         |        1 |
//	| happyform          |        7 |
//	| happyforms-message |      109 |
//	| nav_menu_item      |       32 |
//	| oembed_cache       |       32 |
//	| page               |      143 |
//	| post               |      396 |
//	| revision           |        4 |
//	| team               |        1 |
//	| tf_stats           |        1 |
//	| wpdmpro            |        1 |

	'wp_posts' => ['primary_key' => ['ID'], 'where' => 'post_type != \'revision\' AND post_type = \'page\'']
];

switch($command) {
	case 'compare':
		$tables = array_intersect(get_tables($hacked_db), get_tables($original_db));
		$tables = ['wp_options'];

		echo 'Comparing: ' . PHP_EOL;

		foreach($tables as $table) {
			$hacked_table_data = get_rows($hacked_db, $table, $tables_info);
			$original_table_data = get_rows($original_db, $table, $tables_info);

			$row_keys = array_unique(array_merge(array_keys($hacked_table_data), array_keys($original_table_data)));

			$missing_original = [];
			$missing_hacked = [];

			$table_header = ['#', 'Key', 'Diffs'];
			$table_data = [];
			$row_count = 0;

			echo str_repeat('-', 50) . PHP_EOL;
			echo 'TABLE: ' . $table . PHP_EOL;
			echo str_repeat('-', 50) . PHP_EOL;
			echo str_repeat(' ', 50) . PHP_EOL;

			foreach($row_keys as $row_key) {

				$diffs = [];

				$original_data = isset($original_table_data[$row_key]) ? $original_table_data[$row_key] : [];
				$hacked_data = isset($hacked_table_data[$row_key]) ? $hacked_table_data[$row_key] : [];

				render_data_diffs($row_key, 'Original', $original_data, 'Hacked', $hacked_data);


//				if (!array_key_exists($row_key, $hacked_table_data)) $missing_hacked[] = $row_key;
//				else if (!array_key_exists($row_key, $original_table_data)) $missing_original[] = $row_key;
//				else {
//					$columns = array_unique(array_merge(array_keys($data1), array_keys($data2)));
//
//					foreach ($columns as $column) {
//						#if (!array_key_exists($column, $data1) && array_key_exists($column, $data2)) $missing_hacked[] = $row_key . ' column ' . $column . ' missing in hacked';
//						#else if (array_key_exists($column, $data1) && !array_key_exists($column, $data2)) $missing_original[] = $row_key . ' column ' . $column . ' missing in original';
//						#else if ($data1[$column] != $data2[$column]) $diffs[] = $column;
//						if (array_key_exists($column, $data1) && array_key_exists($column, $data2) && $data1[$column] != $data2[$column]) $diffs[] = $column;
//					}
//				}
//
//				if (count($diffs) > 0) {
//					#echo $row_key . ' => ' . implode(', ', $diffs) . PHP_EOL;
//					$table_data[] = [++$row_count, $row_key, implode(', ', $diffs)];
//				}
			}

//			foreach($missing_original as $problem) {
//				$table_data[] = [++$row_count, $problem, 'Missing from original'];
//			}
//			foreach($missing_hacked as $problem) {
//				$table_data[] = [++$row_count, $problem, 'Missing from hacked'];
//			}
//			display_table($table_header, $table_data);
		}

		break;
	case 'struct':
		$table = isset($argv[2]) ? $argv[2] : null;
		if (empty($table)) die('Missing table name' . PHP_EOL . $usage);

		echo '[DB1]' . PHP_EOL;
		show_table_structure($hacked_db, 'wp_posts', $tables_info);
		echo '[DB2]' . PHP_EOL;
		show_table_structure($original_db, 'wp_posts', $tables_info);
		break;
	case 'tables':
		missing_tables($hacked_db, $original_db);
		break;
	default:
		echo $usage;

}

function render_data_diffs($row_key, $data1_label, $data1, $data2_label, $data2) {
	$columns = array_unique(array_merge(array_keys($data1), array_keys($data2)));

	$diffs = [];

	foreach($columns as $column) {
//		if (!isset($data1[$column])) $diffs[] = [$column, '*** Missing ***', $data2[$column]];
//		else if (!isset($data2[$column])) $diffs[] = [$column, $data1[$column], '*** Missing ***'];
//		else if ($data1[$column] != $data2[$column]) $diffs[] = [$column, $data1[$column], $data2[$column]];

		if (isset($data1[$column]) && isset($data2[$column]) && $data1[$column] != $data2[$column]) $diffs[] = [$column, $data1[$column], $data2[$column]];
	}

	if (count($diffs) > 0) {
		echo str_repeat('-', 50) . PHP_EOL;
		echo 'KEY: ' . $row_key . PHP_EOL;
	}
	foreach($diffs as $diff) {
		list($column, $data1_value, $data2_value) = $diff;

		echo str_repeat('-', 50) . PHP_EOL;
		echo '[' . $column . ']' . PHP_EOL;
		echo $data1_label . ': ' . PHP_EOL;
		echo $data1_value . PHP_EOL;
		echo $data2_label . ': ' . PHP_EOL;
		echo $data2_value . PHP_EOL;
	}
}
function get_rows($db, $table, $tables_info) {
	$sql = "SELECT * FROM $table";
	$table_info = get_param($tables_info, $table, []);
	$where = get_param($table_info, 'where', '');

	if (!empty($where)) $sql .= 'WHERE ' . $where;

	$query = mysqli_query($db, $sql);
	$data = [];
	$keys = get_table_key($db, $table, $tables_info);

	while ($row = mysqli_fetch_assoc($query)) {
		$data_key = '';
		foreach($keys as $key) {
			$data_key .= $row[$key] . ';';
		}
		$data[$data_key] = $row;
	}

	return $data;
}

function missing_tables($hacked_db, $original_db) {
	$hacked_db_tables = get_tables($hacked_db);
	$original_db_tables = get_tables($original_db);

	$diff1 = array_diff($hacked_db_tables, $original_db_tables);
	$diff2 = array_diff($original_db_tables, $hacked_db_tables);

	if (count($diff1) > 0) echo 'Fresh missing: ' . implode(', ', $diff1) . PHP_EOL;
	if (count($diff2) > 0) echo 'Hacked missing: ' . implode(', ', $diff2) . PHP_EOL;
}

function get_table_key($db, $table_name, array $tables_info) {
	$table = describe_table($db, $table_name);
	$primary_key = get_table_param($tables_info, $table_name, 'primary_key');
	if (count($primary_key) > 0) return $primary_key; // Hard-coded into config, so use it

	foreach($table['fields'] as $field) {
		if (!empty($field['Key'])) $primary_key[] = $field['Field'];
	}

	return $primary_key;
}

function show_table_structure($db, $table_name, array $tables_info) {
	$table = describe_table($db, $table_name);

	echo $table['name'] . ' - (' . implode(', ', get_table_key($db, $table_name, $tables_info)) . ')' . PHP_EOL;

	foreach($table['fields'] as $field) {
		// Field, Type, Null, Key, Default, Extra
		echo '  ' . $field['Field'] . ' (' . $field['Type'] . ')' . PHP_EOL;
	}
}

/**
 * @param $db
 * @param $table
 * @return ['name' => 'string', 'fields' => []]
 */
function describe_table($db, $table) {
	$query = mysqli_query($db, "DESCRIBE $table");

	$table = [
		'name' => $table,
		'fields' => []
	];

	while ($row = mysqli_fetch_assoc($query)) {
		// Field, Type, Null, Key, Default, Extra
		$table['fields'][] = $row;
	}

	return $table;
}

function get_table_columns($db, $table) {
	$table = describe_table($db, $table);

	return array_map(function($field) {
		return $field['Field'];
	}, $table['fields']);
}

function get_tables($db) {
	$query = mysqli_query($db, "SHOW TABLES");

	$tables = [];

	while ($row = mysqli_fetch_array($query)) {
		$tables[] = $row[0];
	}

	return $tables;
}

function get_param(array $arr, $key, $default=null) {
	return array_key_exists($key, $arr) ? $arr[$key] : $default;
}

function get_table_param(array $tables_info, $table_name, $key, $default=null) {
	$table_info = get_param($tables_info, $table_name, []);

	return get_param($table_info, $key, $default);
}

function display_table(array $headers, array $rows) {
	$col_widths = col_widths($headers, $rows);
	$start_char = '| ';
	$separator = ' | ';
	$end_char = ' |';

	$table_width = get_table_width($col_widths, $start_char, $separator, $end_char);
	echo str_repeat('-', $table_width) . PHP_EOL;
	display_table_row($headers, $col_widths, $start_char, $separator, $end_char);
	echo str_repeat('-', $table_width) . PHP_EOL;
	foreach($rows as $row) {
		display_table_row($row, $col_widths, $start_char, $separator, $end_char);
	}
	echo str_repeat('-', $table_width) . PHP_EOL;
}

function get_table_width($col_widths, $start_char, $separator, $end_char) {
	$width = array_reduce($col_widths, function($carry, $col_width) {
		return $carry + $col_width;
	}, 0);

	return $width + strlen($start_char) + strlen($separator) + strlen($end_char);
}

function display_table_row(array $row, array $widths, $start_char, $separator, $end_char) {
	$row_values = [];
	for($i=0, $j=count($row); $i < $j; $i++) {
		$row_values[] = str_pad($row[$i], $widths[$i], ' ');
	}

	echo $start_char . implode($separator, $row_values) . $end_char . PHP_EOL;
}

function col_widths(array $headers, array $rows) {
	$col_widths = field_lengths($headers);

	foreach($rows as $row) {
		$row_col_widths = field_lengths($row);
		for($i=0, $j=count($row_col_widths); $i < $j; $i++) {
			$len = strlen($row[$i]);
			if ($len > $col_widths[$i]) $col_widths[$i] = $len;
		}
	}

	return $col_widths;
}

// Return the length of each record in the array
function field_lengths(array $data) {
	return array_map(function($data_row) {
		return strlen($data_row);
	}, $data);
}