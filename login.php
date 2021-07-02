<?php
	session_start();

	if(isset($_SESSION['loggedInPerson'])){
		header('Location: messenger.php');
		exit();
	}

	include_once 'essentials/databaseConnect.php';
	include_once 'essentials/classes/person.php';

	$BadLogIn = '';

	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		
			if(isset($_POST['username'])){

						$username = $_POST['username'];
						$password = $_POST['password'];
						
						$q = $db->prepare("SELECT * FROM person WHERE username = ? AND password = ?");
						$q->execute(array($username , $password));
						
						$counts = $q->rowCount();
						//echo $username . ' ' . $password;

						if($counts == 1){
							$row = $q->fetch();

							$loggedInPerson = new person($row['id'] , $row['firstName'] , $row['secondName'] , $row['username'] , $row['email'] , $row['password'] , $row['date'] , $row['activeTime'] , $row['gender'] , $row['country'] , $row['profilePicture']);

							$loggedInPerson->setActiveTimeToNow();

							$_SESSION['loggedInPerson'] = serialize($loggedInPerson);
							
							$_SESSION['logs'] .= '<h4>Person with username = '. $row['username'] . ' has signed in Successfully</h4>';
							header('Location: messenger.php');
							exit();
						} else {
							$BadLogIn = 'Wrong username or password.';
						}
			}
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Login</title>
	<link rel="shortcut icon" href="images/logos/DeepLinks.png"/>
	<link rel="stylesheet" type="text/css" href="styles/fonts.css"/>
	<link rel="stylesheet" type="text/css" href="styles/form.css"/>
</head>
<body style="background-image: url('images/logos/backgroundWall.png');
				background-attachment: fixed;
 				background-position: center;
  				background-repeat: no-repeat;
  				background-size: cover;"> 

			<div style="width: 1500px; height: 850px; background-color: white; border-radius: 5px; margin-left: auto; margin-right:auto; opacity: 0.9; margin-top: 70px; ">

				<!-- Left Box -->
				<div style="margin-left: 120px; margin-top:90px; width: 700px; float: left;">
					<h1 style="">Stay connected with all lovers, relatives and friends.</h1>
					<img style="width: 700px; border-radius: 9px;" src="images/friendsWallpaper.jpg">
					<h2 style="">Good relations start by strong attention, even when you are busy Deep Links will be your planner when needed.</h2>
				</div>
				<!-- Right Box -->

				<div style="float: right; margin-right: 70px;">
						<img style="margin-top: 10px; width: 400px;" src="images/logos/DeepLinksLogo.png">

						<form style="margin-top:10px;" method="POST">
							<label for="un"><b>Username</b></label><br>
							<input type="text" placeholder="Enter Username" name="username"><br><br>
							<label for="fname"><b>Password</b></label><br>
							<input type="password"  placeholder="Enter Password" name="password"><br><br>
							<h4 style="color:red;"> <?php echo $BadLogIn; ?></h4>
							<input style="background-color: #e91b24;" type="submit" value="Login">
							<p id="belowLogin">Don't have an account ? create one from here.</p>
						</form>
				</div>
			</div>


</body>
</html>