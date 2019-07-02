<!-- start footer.php-->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.2.min.js"><\/script>')</script>

<script src="simpleMobileMenu/jquery-simple-mobilemenu.js"></script>


<script>
$(document).ready(function(){

	<?php if(isset($authLevel) && $authLevel == 2){ ?>
		$("body").removeClass("alt-reg");
	<?php }?>


	<?php if(isset($_SESSION['auth']) && $_SESSION['auth']){ ?>
		$("body").addClass("auth");

		<?php if(!isset($authLevel) || $authLevel !== 0){ ?>
			$(".reg-ui-email").hide();
			$(".reg-ui-pw").show();
			$(".reg-copy").text("Set a password, so you can log back in.");
			$(".or, .login-cta, .login-alt").hide();
		<?php } ?>
	<?php } ?>


	<?php if(!isset($_SESSION['usingPassword']) || !$_SESSION['usingPassword']){ ?>
		if($("body").hasClass("alt-reg")){
			$(".register-ui").slideDown()
		}else if($("body").hasClass("index-body")){
			$(".register-ui").show();
		}
		
	<?php } ?>

	<?php if(isset($_GET['login'])){ ?>
		$(".register-ui, .login-ui").toggle();
		$(".this-week-container").css("margin-top", "0px")
	<?php } ?>


	$(".mobile_menu").simpleMobileMenu();

	
	window.validateEmail = function(emailInput){
		var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  		var validEmail = regex.test(emailInput.val());
  		if(validEmail){
			console.log("valid")
			return true;
		}else{
			//error
			console.log("fake news")
			return window.inputError(emailInput);
		}
	}

	var inYourFaceTimeout;
	var inYourFaceRandoms = ["Good work!", "Hard work!", "Nice job!", "Hustle!"]
	window.showInYourFace = function(message, callback){
		var message = message || inYourFaceRandoms[Math.floor(Math.random()*inYourFaceRandoms.length)];;
		var callback = callback || function(){};
		if($(".in-your-face").is(':visible')){
	     clearTimeout(inYourFaceTimeout);
	    }

		$(".in-your-face .in-your-face-text").html(message);
		$(".in-your-face").show();
		
	    inYourFaceTimeout = setTimeout(function(){
	       $(".in-your-face").fadeOut(function(){
	       	callback();
	       }); 

	    }, 1000)
	}

	var statusMessageTimeout;
	window.showStatusMessage = function(message){
		if($(".status-message").is(':visible')){
	     clearTimeout(statusMessageTimeout);
	    }

		$(".status-message .status-message-text").html(message);
		$(".status-message").fadeIn();
		
	    statusMessageTimeout = setTimeout(function(){
	       $(".status-message").fadeOut(); 
	    }, 5000)
	}

	window.inputError = function(emailInput, errorMessage=""){
		emailInput.addClass("inputError");
		// console.log(errorMessage)
		if(errorMessage.length>0){
			window.showStatusMessage(errorMessage)
		}
		return false;
	}

	$("input").keypress(function(){
		$("input").removeClass("inputError");
	})


	$(".status-message-close").click(function(){
		$(".status-message").fadeOut();
	});


	$(document).on("click",".reset-pw-cta", function(){
		var email = $(this).attr("data");
		$.ajax({
			url:"forgot-pw-service.php?email="+email,
			complete:function(response){
				console.log(response.responseText)
				window.showStatusMessage("A password reset email as been sent to " + email);
			}
		})
	});

	window.passwordRegister = function(){
		var passwordInput = $(".password-input");
		var password = passwordInput.val();
		if(password.length > 0){	//#TODO: better pw reqs
			var data = $(".password-register-form").serialize();
			// console.log(data)
			$.ajax({
				url: "password-register-service.php",
				type: "POST",
				data: data,
				complete:function(response){
					console.log(response.responseText)
					
					$(".register-ui").slideUp();
					$("body").removeClass("alt-reg");
					
				}
			});
		}

	}

	window.login = function(){
		var emailInput = $(".email-login-input");
		var email = emailInput.val();
		
		var passwordInput = $(".password-login-input");
		var password = passwordInput.val();

		var data = $(".login-form").serialize();
		// console.log(data)
		if(window.validateEmail(emailInput) && password.length > 0){	
			$.ajax({
				url: "login-service.php?email="+email,
				type: "POST",
				data: data,
				complete: function(response){
					console.log(response.responseText);
					var jsonResponse = JSON.parse(response.responseText);
					if(jsonResponse['userid'] == 0){
						window.inputError(emailInput, "That email/password is incorrect. <span class='reset-pw-cta' data='"+email+"'>Reset password?</span>");
					}else{
						$(".login-ui").slideUp();
					}
				}

			});
		}
	}

	window.facebookAuth = function(email, first, last){
		
		var first = first || "";
		var last = last || "";
		// console.log(email)
		$.ajax({
			url: "facebook-auth-service.php?email="+email+"&first="+first+"&last="+last,
			complete:function(response){
				console.log(response.responseText)
				var jsonResponse = JSON.parse(response.responseText);
				
				window.location.reload();
			}
		});
	}
	window.emailRegister = function(){
		var emailInput = $(".email-input");
		var email = emailInput.val();

		if(window.validateEmail(emailInput)){
			
			$.ajax({
				url: "email-register-service.php?email="+email,
				complete:function(response){
					console.log(response.responseText)
					var jsonResponse = JSON.parse(response.responseText);
					if(jsonResponse['duplicateEmail']){
						window.inputError(emailInput, "That email is already registered. <span class='reset-pw-cta' data='"+email+"'>Reset password?</span>");
					}else{

						$(".reg-ui-email").slideUp(function(){
							$(".reg-ui-pw").slideDown();
						});
						$(".reg-copy").text("Set a password, so you can log back in.")
						$(".or, .login-cta, .login-alt").hide();
						$("body").addClass("auth");

					}
					
				}
			});

		}
	}

	//begin event listeners
	
	$(".dismiss-modal").click(function(){
		$(".modal").hide();
	});
	$(".modal").click(function(){
		$(".modal").hide();
    });
    $(".modal *").click(function(e){
    	e.stopPropagation();
    });


	$(".register-ui .close").on("click", function(){
		console.log("clicked")
		$(".register-ui").remove();
		$("body").removeClass("alt-reg");
	});

	$(".login-alt").click(function(){
		window.location = "index.php?login";
	});

	$(".log-out").click(function(){
		console.log("clicked")
		$.ajax({
			url:"logout-service.php",
			complete: function(response){
				// console.log(response.responseText);
				window.location.reload();
			}
		})
	});

	$(".switch").click(function(){
		$(".register-ui, .login-ui").toggle();
	});

	$("input.email-input").keypress(function(e) {
	    if(e.which == 13) {
	        window.emailRegister();
	    }
	});

	$(".register-button").click(function(){
		window.emailRegister();
	});

	$("input.password-input").keypress(function(e) {
	    if(e.which == 13) {
	    	e.preventDefault();
	        window.passwordRegister();
	    }
	});

	$(".password-button").click(function(){
		window.passwordRegister();
	});

	$(".login-form input").keypress(function(e) {
	    if(e.which == 13) {
	    	e.preventDefault();
	        window.login();
	    }
	});

	$(".login-button").click(function(){
		window.login();
	});

	window.fbAsyncInit = function() {
	    FB.init({
	      appId      : "", //PROD:  | QA:  | localhost: 
	      cookie     : true,
	      xfbml      : true,
	      version    : 'v2.12'
	    });
	      
	    FB.AppEvents.logPageView();   
	  		
	};

	(function(d, s, id){
	 var js, fjs = d.getElementsByTagName(s)[0];
	 if (d.getElementById(id)) {return;}
	 js = d.createElement(s); js.id = id;
	 js.src = "https://connect.facebook.net/en_US/sdk.js";
	 fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));



	$(".facebook-login").click(function(){
        FB.login(function(response) {
          if (response.status === 'connected') {
            // Logged into your app and Facebook.
            FB.api('/me', {fields: 'last_name, first_name, email'}, function(response) {
                console.log(response)
                var first = response.first_name || "";
                var last = response.last_name || "";

                window.facebookAuth(response.email, first, last)
            });
                            
          } else {
            // The person is not logged into this app or we are unable to tell. 
            console.log("not logged in")
          }

        }, {scope: 'public_profile,email'});
    });



});

</script>
<?php if( getenv('APPLICATION_ENV') == "production"){ ?>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script src="https://www.googletagmanager.com/gtag/js?id=XXXX"></script>
<?php } ?>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', 'XXXX');
	</script>
