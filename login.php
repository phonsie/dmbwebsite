<?php

	define('INCLUDE_CHECK',true);

	require('../dmbConfig/config.php');
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
		
		header("Location: index.php");
		exit;
	}

	if(isset($_POST['submit']))
	{	
		if($_POST['submit']=='Login')
		{
			// Checking whether the Login form has been submitted		
			$err = array();
			// Will hold our errors
			
			if(!$_POST['username'] || !$_POST['password'])
				$err[] .= 'All the fields must be filled in!';
			
			if(!count($err))
			{
				$_POST['rememberMe'] = (int)$_POST['rememberMe'];
			
				$db = new Database($db_config);
				if(!$db->init()) throw new Exception($db->get_error());
					
				$username = $_POST['username'];
				$pass = md5($_POST['password']);
	
				$row = getMemberID($db,$username,$pass);
				
				if(isset($row['usr']))
				{
					// If everything is OK login
					$_SESSION['usr']= $row['usr'];
					$_SESSION['id'] = $row['MID'];
					$_SESSION['secret'] = $row['secret'];
					$_SESSION['rememberMe'] = $_POST['rememberMe'];
					
					// Store some data in the session					
					setcookie('tzRemember',$_POST['rememberMe']);
				}
				else $err[] .='Wrong username and/or password!';
			}
			
			if($err)
			$_SESSION['msg']['login-err'] = implode('<br />',$err);
			// Save the error messages in the session
		
			header("Location: shows.php?artistID=6&year=2014");
			exit;
		}	
	}  

	setHeader("Login");
	
?>
<?php	
	if(!isset($_SESSION['id'])):
?>
		<div class="right">
			<!-- Login Form -->
			<form class="clearfix" action="" method="post">		
				<h1>Enter your credentials:</h1>										
				<?php						
					if(isset($_SESSION['msg']['login-err']))
					{
						echo '<div class="err">'.$_SESSION['msg']['login-err'].'</div>';
						unset($_SESSION['msg']['login-err']);
					}
				?>
				<br />							
				<label class="grey" for="username">Username:</label> 
				<input class="field" type="text" name="username" id="username" value="" size="23" /> 
				<br />
				<br />
				<label class="grey" for="password">Password:</label>
				<input class="field" type="password" name="password" id="password" size="23" /> 
				<br />
				<br />
				<label>
					<input name="rememberMe" id="rememberMe" type="checkbox" checked="checked" value="1" />
					&nbsp;Remember me
				</label>
				<br />
				<br />
				<input type="submit" name="submit" value="Login" class="bt_login" />
			</form>
		</div>					
<?php
	endif;	
	
	setFooter();
?>