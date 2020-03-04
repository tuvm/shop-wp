<?php
require ('../../../wp-load.php');
$del_id = $_POST['checkbox']; 
foreach($del_id as $value){
	global $wpdb;
	$table_name = $wpdb->prefix.'muahangnhanh';
	$wpdb->delete($table_name, array('id' => $value), array('%s'));
}
$url = $_SERVER['HTTP_HOST'].'/wp-admin/admin.php?page=mua-hang-nhanh';
header('Location: http://'.$url);
?>