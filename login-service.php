<?php

// Report all errors
error_reporting(E_ALL);
ini_set("display_errors", 1);

$response = [];

include 'database-service.php';
$databaseService = new DatabaseUserService();
$databaseService -> login();
$response["status"] = $databaseService->status;
$response["emailFound"] = $databaseService->emailFound;
$response["userid"] = $databaseService->userid;
$response["status"] = $databaseService->status;

echo json_encode($response);
// print_r($response);

?>