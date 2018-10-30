<?php 
    // Report all errors
    error_reporting(E_ALL);
    ini_set("display_errors", 1);

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    include 'database-service.php';
    //keep a user logged in based on authid cookie.
    //this setup allows for anonymous users, and users that have only enter an email (no password)
    function lookupAuthid($authid){
        
        include 'db-connection.php';
        $sql = "SELECT * FROM `users` WHERE authid = ?"; 
        $result = $conn->prepare($sql); 
        $result->execute(array($authid));
        $authrow = $result->fetch(PDO::FETCH_ASSOC);
        $userid = $authrow['ID'];
        $_SESSION['userid'] = $userid;
        $_SESSION['auth'] = true;
        //log them in, only if they are non-anon
        //make sure email exists (not anon user)
        
        $authLevel = 0; //0 == anon, 1 == not using password, 2 = using pw
        if(isset($authrow['email']) && strlen($authrow['email']) > 0){
            $email = $authrow['email'];
            $password = $authrow['password'];
            $socialreg = $authrow['socialreg'];
            $_SESSION['email'] = $email;
            
            $notUsingPassword = true;
            
            if(strlen($password)>0 || strlen($socialreg)>0){
                $notUsingPassword = false;
            }
            if($notUsingPassword){
                $authLevel = 1;
            }else{
                $authLevel = 2;
            }
           
            $databaseServiceHead = new DatabaseUserService();
            $databaseServiceHead -> authorizeUser($authid, $email, $userid, $notUsingPassword);

        }
        return $authLevel;
            
    } //end lookupAuthid()

    if(isset($_COOKIE['authid'])){
        $authid = $_COOKIE['authid'];
        $authLevel = lookupAuthid($authid);
    }
?>
<?php if(isset($authLevel)){ ?>
    <script>console.log("<?php echo $authLevel; ?>")</script>
    <script>console.log("<?php echo $_SESSION['userid']; ?>")</script>
<?php } ?>
<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>AntPace.com</title>
    
    <meta name="description" content="My app's description.">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">

    <link rel="stylesheet" href="/css/bootstrap.min.css">
   
    <link rel="stylesheet" href="/css/bootstrap-theme.min.css">
    
    <link rel="stylesheet" href="/simpleMobileMenu/styles/jquery-simple-mobilemenu.css" />

    <link rel="stylesheet" href="/css/main.css">

    <!-- <script async src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script> -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500" rel="stylesheet">

    
</head>
