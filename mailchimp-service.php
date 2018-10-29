<?php 

class MailchimpService { 
	public $responseCode;
	public $apiKey;
	public $email;
	
	function __construct()
	{
		$this->responseCode = 0;
		$this->apiKey = 'XXX';
		if(!isset($_GET['email'])){
			die("no email");
		};

		$email = $_GET['email'];
		$this->email = $email;
	}

    function addToMailchimp() {
	    $apiKey = $this->apiKey;
	    $email = $this->email;
	    $memberId = md5(strtolower($email));
	    $dataCenter = substr($apiKey,strpos($apiKey,'-')+1);

	    $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/9c32c1aa96/members/' . $memberId;

	    $json = json_encode([
	        'email_address' => $email,
	        'status'        => 'subscribed' // "subscribed","unsubscribed","cleaned","pending"
	        // 'merge_fields'  => [
	        //     'First Name'     => $data['firstname'],
	        //     'Last Name'     => $data['lastname']
	        // ],
	        // 'interests'     => $interests_array
	        //'PurchaseStatus'     => $purchaseStatus_array
	    ]);

	    $ch = curl_init($url);

	    curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);                                                                                                                 
	    $result = curl_exec($ch);
	    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    curl_close($ch);
	    // var_dump($result);
	    $this->responseCode = $httpCode;
	    // echo $data['email'];
	    
	}
} 

?>