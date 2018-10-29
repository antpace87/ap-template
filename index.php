<?php include 'head.php';?>
<body class="index-body">


<?php include 'header.php';?>
<?php include 'register-ui.php';?>


<div class="ap-container ">

	<?php if(isset($authLevel)){?>
	<!-- Logged in -->

	<?php }else{ ?>
	<!-- Not logged in -->

	<?php } ?>


</div>
 

<div class="feature-button-wrap">
    <h2>Feature buttons:</h2>
    <div class="top-buttons button-section">
        <a href="create-record.php?type1"><button class="feature-button btn">Type 1 <i class="fab fa-accessible-icon"></i></button></a>
        <a href="create-record.php?type2"><button class="feature-button btn">Type 2 <i class="fas fa-dice-two"></i></button></button></a>
    </div>

</div>

<?php include 'footer.php';?>

 

<script>
$(document).ready(function() {


});


</script>

</body>

</html>
