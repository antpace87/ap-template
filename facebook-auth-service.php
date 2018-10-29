<?php

// Report all errors
error_reporting(E_ALL);
ini_set("display_errors", 1);

$response = [];

include 'mailchimp-service.php';
$mailchimpService = new MailchimpService();
$mailchimpService -> addToMailchimp(); 
$response["mailchimpResponseCode"] = $mailchimpService->responseCode;

include 'database-service.php';
$databaseService = new DatabaseUserService();
$databaseService -> facebookAuth();
$response["userid"] = $databaseService->userid;
$response["status"] = $databaseService->status;

echo json_encode($response);
// print_r($response);

?>