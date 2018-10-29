<?php


$response = [];
include 'database-service.php';
$databaseService = new DatabaseUserService();;
$databaseService -> forgotPw();

echo json_encode($response);

?>