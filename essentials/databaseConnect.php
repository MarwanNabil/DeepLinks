<?php
		$dsn = 'mysql:host=localhost;dbname=deeplinks;'; //Data Source Name
		$user = 'root'; //the user to connect
		$pass = ''; //password for this user
		$options = array(
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', //make the intial
		);

		try{
				$db = new PDO($dsn , $user , $pass , $options); //this made for enabling to to put arabic strings in the database
				$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				if(!isset($_SESSION['logs'])){
					$_SESSION['logs'] =  '<h4 style="color:#0FDC60;">Database Connected!</h4><br>';
				} else {
					$_SESSION['logs'] .=  '<h4 style="color:#0FDC60;">Database Connected!</h4><br>';
				}
		} catch (PDOException $e){
			if(!isset($_SESSION['logs'])){
				$_SESSION['logs'] = '<h4 style="color:red;">' . 'Failed ' . $e->getMessage() .'</h4><br>';
			} else {
				$_SESSION['logs'] .= '<h4 style="color:red;">' . 'Failed ' . $e->getMessage() .'</h4><br>';
			}
		}
	
?>