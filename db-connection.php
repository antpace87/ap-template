<?php
// Report all errors
error_reporting(E_ALL);
ini_set("display_errors", 1);

$username = "root";
$password = "root";

if( getenv('APPLICATION_ENV') == "qa"){
	$password = "qapassword";
}
if( getenv('APPLICATION_ENV') == "production"){
	$password = "livepassword";
}

$database = "MyApp";
$options = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
);

try {
	$conn = new PDO("mysql:host=127.0.0.1;port=3306;dbname=$database", $username, $password, $options);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	// echo "Connected successfully"; 
}
catch(PDOException $e)
{
	echo "Connection failed: " . $e->getMessage();
}

?>