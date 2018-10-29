<?php

class DatabaseRecordService{ 
	public $connection;
	public $userid;
	public $status;
	public $recordid;
	public $record_not_found;
	public $record_row;
	public $detail_rows;
	public $all_record_rows;

	function __construct()
	{
		if (session_status() == PHP_SESSION_NONE) {
		    session_start();
		}
		include 'db-connection.php';
		$this->connection = $conn;
		$this->userid = "0";
		$this->recordid = "0";
		$this->record_not_found = false;
		$this->status = "";
		$this->record_row = "None.";
		$this->detail_rows = "None.";
		$this->all_record_rows = "None.";

	}

	function createAnonUser(){
		$conn = $this->connection;
		$stmt = $conn->prepare("INSERT INTO `users` (email, first, last, password, authid) VALUES (:email,  :first, :last, :password, :authid)");
		$email = "";
		$first = "";
		$last = "";
		$password = "";
		$timestamp = time();
		$authid = md5($timestamp.md5("anon"));

		$stmt->bindParam(':email', $email);
		$stmt->bindParam(':first', $first);
		$stmt->bindParam(':last', $last);
		$stmt->bindParam(':password', $password);
		$stmt->bindParam(':authid', $authid);
		$stmt->execute();
		$userid = $conn->lastInsertId();
		
		$_SESSION['userid'] = $userid;
		setcookie("authid", $authid, time() + (86400 * 60), "/");
		$this->status .= "Anon user record created.";
		$this->userid = $userid;
	}

	function viewAllRecordsByUseridByWeek(){
		//get records from this current week
		$userid = $this->userid;

		if(isset($_SESSION['userid'])){
			$userid = $_SESSION['userid'];
		}
		$conn = $this->connection;
		$stmt = $conn->prepare("SELECT * FROM `records` WHERE userid = ? and YEARWEEK(`date`, 0) = YEARWEEK(CURDATE(), 0)");	
		$stmt->execute(array($userid));
		$number_of_rows = $stmt->rowCount();
		if($number_of_rows < 1){
			$this->record_not_found = true;
			$this->status .= "There are no records with that userid.";
			return;
		}

		$this->all_record_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	}

	function viewAllDetailsByUseridAndType($type){
		$userid = $this->userid;
		$type = $type || "generic"

		if(isset($_SESSION['userid'])){
			$userid = $_SESSION['userid'];
		}
		$conn = $this->connection;
		$stmt = $conn->prepare("SELECT * FROM `recordsdetails` WHERE userid = ? and detailtype = ?");	
		$stmt->execute(array($userid, $type));
		$number_of_rows = $stmt->rowCount();
		if($number_of_rows < 1){
			$this->record_not_found = true;
			$this->status .= "There are no records with that userid.";
			return;
		}

		$this->all_record_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	function viewAllRecordsByUserid(){
		$userid = $this->userid;

		if(isset($_SESSION['userid'])){
			$userid = $_SESSION['userid'];
		}
		$conn = $this->connection;
		$stmt = $conn->prepare("SELECT * FROM `records` WHERE userid = ?");	
		// $stmt = $conn->prepare("SELECT * FROM `records` WHERE userid = ? and MONTH(`date`) = MONTH(CURDATE())");	
		$stmt->execute(array($userid));
		$number_of_rows = $stmt->rowCount();
		if($number_of_rows < 1){
			$this->record_not_found = true;
			$this->status .= "There are no records with that userid.";
			return;
		}

		$this->all_record_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	}

	function deleteRecord(){
		if(!isset($_GET['rid'])){ 
			$this->record_not_found = true;
			$this->status .= "Record id is missing from GET.";
			return;
		}
		$userid = $this->userid;

		if(isset($_SESSION['userid'])){
			$userid = $_SESSION['userid'];
		}

		$rid = $_GET['rid'];
		$conn = $this->connection;
		$stmt = $conn->prepare("delete FROM `records` WHERE recordid = ? and userid = ?");	
		$stmt->execute(array($rid, $userid));
		$stmt = $conn->prepare("delete FROM `recordsdetails` WHERE recordid = ? and userid = ?");	
		$stmt->execute(array($rid, $userid));

	}
	function viewRecord(){
		if(!isset($_GET['rid'])){ 
			$this->record_not_found = true;
			$this->status .= "Record id is missing from GET.";
			return;
		}
		
		$userid = $this->userid;

		if(isset($_SESSION['userid'])){
			$userid = $_SESSION['userid'];
		}
		
		$rid = $_GET['rid'];
		$conn = $this->connection;
		$stmt = $conn->prepare("SELECT * FROM `records` WHERE recordid = ? and userid = ? ");	
		// $stmt = $conn->prepare("SELECT * FROM `records` INNER JOIN recordsdetails ON recordsdetails.recordid=? and records.recordid = ? and records.userid = ?");	

		$stmt->execute(array($rid, $userid));

		$number_of_rows = $stmt->rowCount();
		if($number_of_rows < 1){
			$this->record_not_found = true;
			$this->status .= "Record id with that userid is not found in our DB.";
			return;
		}
		$stmt2 = $conn->prepare("SELECT * FROM `recordsdetails` WHERE recordid = ?");	
		$stmt2->execute(array($rid));

		$this->detail_rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
		$this->record_row = $stmt->fetch(PDO::FETCH_ASSOC);


	}

	function editRecord(){
		if(!isset($_SESSION['userid']) || $_SESSION['userid'] == "0"){
			$this->status .= "No userid. ";
			return;
		}else{
			$this->userid = $_SESSION['userid'];
		}

		$recordType = "";
		$date = "";
		$weight = "";
		$belt = "";
		$userid = $this->userid;
		$conn = $this->connection;

		if(isset($_GET['rid'])){
			$recordid = $_GET['rid'];
		}else{
			$this->status .= " No rid.";
			return;
		}
		if(isset($_POST['record-type'])){
			$recordType = $_POST['record-type'];
		}else{
			$this->status .= " No record-type.";
			return;
		}

		if(isset($_POST['date'])){
			$date = $_POST['date'];
			$date = date('Y-m-d', strtotime(str_replace('-', '/', $date)));
		}
		
		if(isset($_POST['notes'])){
			$notes = $_POST['notes'];
		}
		

		$stmt = $conn->prepare("UPDATE `records` set userid=?, type=?, `date`=?, beltrank=?, notes=?, medal=?, weight=? where recordid=? ");	
	
		$stmt->execute(array($userid, $recordType, $date, $belt, $notes, $comp_results, $weight, $recordid));
		
		$this->recordid = $recordid;
		$this->status .= "Record ID $recordid updated.";

		//delete all detailsrecords by rid, an re-write newly
		$stmt = $conn->prepare("DELETE FROM `recordsdetails` where recordid=? ");	
		$stmt->execute(array($recordid));
		$this->status .= "Recorddetails rows deleted. ";

		//detail record
		$x = 0;
		if(isset($_POST['finish'])){
			foreach ($_POST['finish'] as $value) {
				$detailtype = "match";
				$result = $_POST['matchresult'.$x];
				$finish = $_POST['finish'][$x];
			

				$stmt = $conn->prepare("INSERT INTO `recordsdetails` (detailtype, userid, recordid, result, finish) VALUES (:detailtype, :userid,  :recordid, :result, :finish)");	
				$stmt->bindParam(':detailtype', $detailtype);
				$stmt->bindParam(':userid', $userid);
				$stmt->bindParam(':recordid', $recordid);
				$stmt->bindParam(':result', $result);
				$stmt->bindParam(':finish', $finish);
				$stmt->execute();
				$detailid = $conn->lastInsertId();
				$this->status .= " detail record, id $detailid, created.";

				$x = $x + 1;

			}
		}
		 
	} //editRecord

	function createRecord(){
		
		if(!isset($_SESSION['userid']) || $_SESSION['userid'] == "0"){
			//create anon user record
			$this->createAnonUser();
		}else{
			$this->userid = $_SESSION['userid'];
		}

		$recordType = "";
		$date = "";
		$notes = "";
		$userid = $this->userid;
		$conn = $this->connection;
		
		// var_dump($_POST);
		// die();

		if(isset($_POST['record-type'])){
			$recordType = $_POST['record-type'];
		}else{
			$this->status .= " No record-type.";
			return;
		}
		if(isset($_POST['date'])){
			$date = $_POST['date'];
			$date = date('Y-m-d', strtotime(str_replace('-', '/', $date)));
		}

		if(isset($_POST['notes'])){
			$notes = $_POST['notes'];
		}
	 
		$stmt = $conn->prepare("INSERT INTO `records` (userid, type, `date`, notes) VALUES (:userid,  :type, :date, :notes)");	
		$stmt->bindParam(':userid', $userid);
		$stmt->bindParam(':type', $recordType);
		$stmt->bindParam(':date', $date);
		$stmt->bindParam(':notes', $notes);
		$stmt->execute();
		$recordid = $conn->lastInsertId();
		$this->recordid = $recordid;
		$this->status = "Record ID $recordid recorded.";

		
		//detail record
		$x = 0;
		if(isset($_POST['finish'])){
			foreach ($_POST['finish'] as $value) {
				$detailtype = "match";
				$result = $_POST['matchresult'.$x];
				$finish = $_POST['finish'][$x];
			

				$stmt = $conn->prepare("INSERT INTO `recordsdetails` (detailtype, userid, recordid, result, finish) VALUES (:detailtype, :userid,  :recordid, :result, :finish)");	
				$stmt->bindParam(':detailtype', $detailtype);
				$stmt->bindParam(':userid', $userid);
				$stmt->bindParam(':recordid', $recordid);
				$stmt->bindParam(':result', $result);
				$stmt->bindParam(':finish', $finish);
				$stmt->execute();
				$detailid = $conn->lastInsertId();
				$this->status .= " detail record, id $detailid, created.";

				$x = $x + 1;

			}
		}
		 

	}
}
class DatabaseUserService{ 
	// email/user/password related services
	public $connection;
	public $emailFound;
	public $userid;
	public $number_of_rows;
	public $email;
	public $row;
	public $status;

	function __construct()
	{
		if (session_status() == PHP_SESSION_NONE) {
		    session_start();
		}

		if(!isset($_GET['email']) && !isset($_SESSION['email']) && !isset($_SESSION['userid'])){
			die("no email or userid");
		};
		
		include 'db-connection.php';
		$this->connection = $conn;
		$this->status = "None.";
		$email = "";
		$userid = "0";

		if(isset($_SESSION['email'])){
			$email = $_SESSION['email'];
		}

		if(isset($_GET['email'])){
			$email = $_GET['email'];
		}
		
		if(isset($_SESSION['userid'])){
			$userid = $_SESSION['userid'];
		}

		$this->userid = $userid;

		if(strlen($email)>0){
			$this->email = $email;
			$sql = "SELECT * FROM `users` WHERE email = ?"; 
			$result = $conn->prepare($sql); 
			$result->execute(array($email));
		}

		if($userid !== "0"){
			$sql = "SELECT * FROM `users` WHERE ID = ?"; 
			$result = $conn->prepare($sql); 
			$result->execute(array($userid));
		}
		
		$this->row = $result->fetch(PDO::FETCH_ASSOC);
		$this->number_of_rows = $result->rowCount();

	}


	function forgotPw(){
		$email = $this->email;
		$row = $this->row;
		$number_of_rows = $this->number_of_rows;
		$conn = $this->connection;
		if($number_of_rows > 0){
			$this->emailFound = 1;
			$userid = $row['ID'];
			$this->userid = $userid;
			// $first = $row['first'];
			// $last = $row['last'];

			//create reset token
			$timestamp = time();
			$expire_date = time() + 24*60*60;
			$token_key = md5($timestamp.md5($email));
			$statement = $conn->prepare("INSERT INTO `passwordrecovery` (userid, token, expire_date) VALUES (:userid, :token, :expire_date)");
			$statement->bindParam(':userid', $userid);
			$statement->bindParam(':token', $token_key);
			$statement->bindParam(':expire_date', $expire_date);
			$statement->execute();

			//send email via amazon ses
			#TODO


		}else{
			$this->emailFound = 0;
		}
	}

	function logout(){
		$_SESSION['auth'] = false;
		$_SESSION['email'] = "";
		$_SESSION['userid'] = 0;
		$_SESSION['usingPassword'] = false;
		
		if ( isset( $_COOKIE[session_name()] ) ){
			setcookie( session_name(), "", time()-3600, "/" );
		}if ( isset( $_COOKIE["authid"] ) ){
			setcookie( "authid", "", time()-3600, "/" );
		}
		
		$_SESSION = array();
		$_COOKIE = array();
		
		session_destroy();
		$this->status = "Log out complete.";
	}
	function authorizeUser($authid, $email, $userid, $notUsingPassword){

		$notUsingPassword = $notUsingPassword || false;
		$_SESSION['auth'] = true;
		$_SESSION['email'] = $email;
		$_SESSION['userid'] = $userid;
		setcookie("authid", $authid, time() + (86400 * 60), "/"); // 86400 = 1 day; total 60 days

		$this->userid = $userid;
		if(!$notUsingPassword){
			$_SESSION['usingPassword'] = true;
		}
	}

	function registerPassword(){
		$number_of_rows = $this->number_of_rows;
		$conn = $this->connection;
		if($number_of_rows == 1){
			$this->emailFound = 1;
			$email = $this->email;
			if(isset($_POST['password']) && !empty($_POST["password"])){
				$password = $_POST['password'];
				$passwordHash = password_hash($password, PASSWORD_DEFAULT);
				$sql = "UPDATE `users` SET password = ? WHERE ID = ?"; 
				$result = $conn->prepare($sql);
				$result->execute(array($passwordHash, $_SESSION['userid']));
				$this->status = "Password added.";
				$_SESSION['usingPassword'] = true;
			}else{
				$this->status = "No password.";
				die("No password.");
			}

		}else{
			$this->emailFound = 0;
		}
	}

	function facebookAuth(){
		$number_of_rows = $this->number_of_rows;
		$conn = $this->connection;
		$userid = $this->userid;
		$first = $_GET['first'];
		$last = $_GET['last'];
		$password = "";
		$timestamp = time(); 
		$authid = md5($timestamp);
		$socialreg = "facebook";
		$email = $this->email;
		
		if($number_of_rows == 1){ //could be by email or by userid
			
			$row = $this->row;
			$email = $row['email'];

			if(strlen($email)>0){ 
				//login
				//check if email exists for this record (not anon user)
				$this->emailFound = 1;
				$userid = $row['ID'];
				$authid = $row['authid'];
				$this->status = "Login successful.";
			}else{
				//anon records exist

				$email = $this->email;
				$anonUserId = $this->userid;
				$sql = "SELECT * FROM `users` WHERE email = ?"; 
				$result = $conn->prepare($sql); 
				$result->execute(array($email));
				$row = $result->fetch(PDO::FETCH_ASSOC);
				$number_of_rows = $result->rowCount();
				if($number_of_rows == 1){
					//if user already created anon records, but has account
					$this->emailFound = 1;
					$userid = $row['ID'];
					$authid = $row['authid'];
					$this->status = "Login successful.";
					
					//update those anon records to be related to this existing user
					$stmt = $conn->prepare("UPDATE `records` SET userid = ? WHERE userid = ?");	
					$stmt->execute(array($userid, $anonUserId));
					
					//and delete anon user
					$stmt = $conn->prepare("DELETE from `users` WHERE ID = ?");	
					$stmt->execute(array($anonUserId));

				}else{
					//user has anon userid already set, with records, but never logged before
					$stmt = $conn->prepare("UPDATE `users` SET email = ?, first = ?, last = ?, password = ?, authid = ?, socialreg = ? WHERE ID = ?");	
					$stmt->execute(array($email, $first, $last, $password, $authid, $socialreg, $_SESSION['userid']));
				}



			}


		}else{
			//no email or userid found in DB
			//register
			$stmt = $conn->prepare("INSERT INTO `users` (email, first, last, password, authid, socialreg) VALUES (:email,  :first, :last, :password, :authid, :socialreg)");	
			$stmt->bindParam(':email', $email);
			$stmt->bindParam(':first', $first);
			$stmt->bindParam(':last', $last);
			$stmt->bindParam(':password', $password);
			$stmt->bindParam(':authid', $authid);
			$stmt->bindParam(':socialreg', $socialreg);
			$stmt->execute();
			$userid = $conn->lastInsertId();
			$this->status = "User record created.";

		}
		
		$this->authorizeUser($authid, $email, $userid, false);

	}
	function login(){
		$number_of_rows = $this->number_of_rows;
		$conn = $this->connection;
		
		if($number_of_rows == 1){
			$this->emailFound = 1;
			$email = $this->email;
			$row = $this->row;
			$passwordHash = $row['password'];
			if(isset($_POST['password']) && !empty($_POST["password"])){
				 
				$password = $_POST['password'];
				if(password_verify($password, $passwordHash)) {
					$userid = $row['ID'];
					$authid = $row['authid'];
					$this->status = "Login successful.";
					$this->authorizeUser($authid, $email, $userid, false);
					return;
				}
			}
		}
		$this->status = "Failed login.";
		// $this->status = "pw: ".$_POST['password'];
	}

	function registerEmail(){
		$number_of_rows = $this->number_of_rows;
		$conn = $this->connection;
		$userid = $this->userid;
		$first = "";
		$last = "";
		$password = "";
		$timestamp = time(); 
		$authid = md5($timestamp);
		$emailFound = 0;

		if($number_of_rows > 0){
			
			$row = $this->row;
			$email = $row['email'];

			if(strlen($email)>0){ 
				//check if email exists for this record (not anon user)
				$emailFound = 1;
				$this->status = "Duplicate email found.";
			}else{
				//user might have anon userid already set, in which case we want to update, not insert
				$email = $this->email;
				$stmt = $conn->prepare("UPDATE `users` SET email = ?, first = ?, last = ?, password = ?, authid = ? WHERE ID = ?");	
				$stmt->execute(array($email, $first, $last, $password, $authid, $_SESSION['userid']));

			}

		}else{
			$email = $this->email;
			$stmt = $conn->prepare("INSERT INTO `users` (email, first, last, password, authid) VALUES (:email,  :first, :last, :password, :authid)");	
			$stmt->bindParam(':email', $email);
			$stmt->bindParam(':first', $first);
			$stmt->bindParam(':last', $last);
			$stmt->bindParam(':password', $password);
			$stmt->bindParam(':authid', $authid);
			$stmt->execute();
			$userid = $conn->lastInsertId();
			$this->status = "User record created.";
		}
		
		$this->emailFound = $emailFound;

		if($emailFound == 0){ //make sure not a duplicate email address before setting authid cookie and authorizing user
			$this->authorizeUser($authid, $email, $userid, true);
		}
	}
}
?>