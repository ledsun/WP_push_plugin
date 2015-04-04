<?php
header("Content-Type: text/javascript; charset=utf-8");
$token = $_GET["token"]; 
include_once dirname(__FILE__) . "/wp-config.php";
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
//!!! SQL Injection
$hash = md5($token);
$query = "insert into wp_tokens(token, token_long) values ('{$hash}', '{$token}');";
//echo $query;
if( $mysqli->query( $query ) ) {
    echo '{"status":1}';
} else {
    echo '{"status":0}';
}
$mysqli->close();
