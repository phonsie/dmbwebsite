<?php

	define('INCLUDE_CHECK',true);

	require('../dmbConfig/configPower.php');
	require 'lib/functions.php';
	require 'lib/db.php';

	initSession();

	if(isset($_SESSION['id']) && !isset($_COOKIE['tzRemember']) && !$_SESSION['rememberMe'])
	{
		// If you are logged in, but you don't have the tzRemember cookie (browser restart)
		// and you have not checked the rememberMe checkbox:

		$_SESSION = array();
		session_destroy();
		
		// Destroy the session
	}		

	if(isset($_GET['logoff']))
	{
		$_SESSION = array();
		session_destroy();
		
		header("Location: register.php");
		exit;
	}

	if(isset($_POST['submit']))
	{	
		if($_POST['submit']=='Register')
		{
			// If the Register form has been submitted		
			$err = array();
			
			if(strlen($_POST['username'])<4 || strlen($_POST['username'])>32)
			{
				$err[]='Your username must be between 3 and 32 characters!';
			}
			
			if(preg_match('/[^a-z0-9\-\_\.]+/i',$_POST['username']))
			{
				$err[]='Your username contains invalid characters!';
			}

			/*
			if(!checkEmail($_POST['email']))
			{
				$err[]='Your email is not valid!';
			}
			*/
			
			if(!count($err))
			{
				// If there are no errors								
				// Generate a random password
				$pass = substr(md5($_SERVER['REMOTE_ADDR'].microtime().rand(1,100000)),0,6);
				$secret = substr(md5($_SERVER['REMOTE_ADDR'].microtime().rand(1,100000)),0,6);
								
				$db = new Database($db_config);
				if(!$db->init()) throw new Exception($db->get_error());
				$data = array("usr" => $_POST['username'], 
							  "pass" => md5($pass), 
							  "email" => "", 
							  "regIP" => $_SERVER['REMOTE_ADDR'], 
							  "secret" => md5($secret), 
							  "dt" => date("Y-m-d H:i:s") 
							  );
				
				$id = $db->insertPDO("members", $data);
						
				if($id)
				{					
					$_SESSION['msg']['reg-success']='<br />Your username is <b>'.$_POST['username'].'</b><br />Your password is <b>'.$pass.'</b>';
				}
				else $err[]='An error has occurred and your registration failed! :( <br> There\'s probably a user already with that name!';
			}
		
			if(count($err))
			{
				$_SESSION['msg']['reg-err'] = implode('<br />',$err);
			}	
			
			header("Location: register.php");
			exit;
		}
	}  
	
	setHeader("Register");

	if(!isset($_SESSION['id'])):			
	?>
		<div class="left right">
			<!-- Register Form -->
			<form action="" method="post">
				<?php							
					if(isset($_SESSION['msg']['reg-success']))
					{
						echo("<h1>Registration Successful!</h1>");											
					}
					else
					{
				?>
						<h1>Not a member yet? Sign Up!</h1>
						<br />
						All you have to do is choose a username and enter it below.
						<b> Be sure to save the password you get on the next page somewhere safe </b> 
						as we don't know your e-mail address to resend it to you!
						<br />
						<br />
						<label class="grey" for="username">Enter your preferred username:</label>
						<input class="field" type="text" name="username" id="username" value="" size="23" /> 
						<br />
						<br />
						<input type="submit" name="submit" value="Register" class="bt_register" />
				<?php					
					}
				
				if(isset($_SESSION['msg']['reg-err']))
					{
						echo '<div class="err">'.$_SESSION['msg']['reg-err'].'</div>';
						unset($_SESSION['msg']['reg-err']);
					}
					
					if(isset($_SESSION['msg']['reg-success']))
					{
						echo '<div class="success">'.$_SESSION['msg']['reg-success'].'</div>';
						echo '<br /><b>Please save this somewhere safe as we don\'t have your e-mail address to resend it to you!</b>';
						unset($_SESSION['msg']['reg-success']);
					}
				?>
				<!--
				<br />
				<br />
				<label class="grey" for="email">Email:</label> 
				<input class="field" type="text" name="email" id="email" size="23" /> 
				<br />
				<br />
				<label>A password will be e-mailed to you.</label> 							
				-->
			</form>						
		</div>
	<?php				
	else:			
		echo "Please log off first if you want to create another account!";
	endif;
	
	setFooter();
?>