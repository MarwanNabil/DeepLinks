<?php
	session_start();

	if(!isset($_SESSION['loggedInPerson'])){
		header('Location: login.php');
		exit();
	}


	include_once 'essentials/databaseConnect.php';
	include_once 'essentials/classes/person.php';
	include_once 'essentials/classes/message.php';	
	
	$loggedInPerson = unserialize($_SESSION['loggedInPerson']);

	$targetPerson;
	$targetChat = '';

	loadPeopleFromDatabase();

	function activeSince($activeDate){
		$timeDif = strtotime(date("Y-m-d H:i:s")) - strtotime($activeDate);
		$timeDif = ($timeDif + 59) / 60; //ceiling the minutes
		if((int)$timeDif < 10){
			return 'now'; //ten minutes he is online
		} else if($timeDif < 60){
			return round($timeDif) . ' min'; //active since some minutes 
		} else if( ($timeDif+59) / 60 < 12 ) {
			return round(($timeDif+59)/60) . ' hr'; //active since some hours
		} else {
			$thatMoment = strtotime($activeDate);
			return date('Y/m/d' , $thatMoment);
		}
	}

	if($_SERVER['REQUEST_METHOD'] == "POST"){
		
		$targetChatPersonID = $_COOKIE['clickedMessengerHead'];


		
		global $targetPerson;
		//echo $targetChatPersonID;

		for($i = 0; $i < $numberOfPeople; $i++){
			if($allPeople->get($i)->getID() == $targetChatPersonID){
				$targetPerson = $allPeople->get($i);
				break;
			}
		}

		if(isset($_POST['message']) && strlen($_POST['message']) > 0){
			sendMessage($_POST['message']);
		}

		uploadMessagesTargetPersonAndLoggedInPerson($targetPerson , $loggedInPerson);

		//prin them on the page.
		for($i = 0; $i < $sizeOfActiveConverstation; $i++){
			if($activeConversation->get($i)->getMessageSenderID() == $loggedInPerson->getID()){
				//you
				$targetChat .= '<div class="you">
							  	<h3>You</h3>
							  	<p>'. $activeConversation->get($i)->getTextMessage() .'</p>
							  	<span class="you-time">'. $activeConversation->get($i)->getDateTime() .'</span>
							  	</div>';
			} else {
				//friend
				$targetChat .= '<div class="friend">
							  	<h3>'. $activeConversation->get($i)->getMessageSenderFirstName() .'</h3>
							  	<p>'. $activeConversation->get($i)->getTextMessage() .'</p>
							  	<span class="friend-time">'. $activeConversation->get($i)->getDateTime() .'</span>
								</div>';
			}
		}
	}

	function clickedMessengerHeadCookie($personID){
		$cookieName = "'clickedMessengerHead'";
		$ret = 'setCookie(';
		$ret .= $cookieName . ', 10 , ' . $personID . ')';
		return $ret;
	}

	/*
		for messages head
		you must do it to intialize the sorting crieteria based on the newest action to the top
		the oldest action is at the bottom.
	*/
	chatHeadsIntializer();

	$headMessages = "";
	for($i = 0; $i < $numberOfPeople; $i++){
		$msg = $chatHeads->get($i);

		$headMessages .= '<div class="container" onclick="' . clickedMessengerHeadCookie($msg->getFriend()->getID()) . '">
	  		  			  <img src="data:image/jpeg;base64,'.base64_encode($msg->getFriend()->getProfilePicture()) . '"' .' alt="Avatar">
	 		  			  <h3>'. $msg->getFriend()->getName() .'</h3>';
	 	

	 	$sender = "";
	 	if($msg->getReceivedMessageBool() == 0){
	 		$sender = "You: ";
	 	}

	  	$headMessages .= '<p>'. $sender . $msg->getTextMessage() .'</p>';
	  	$headMessages .= '<span class="time-right">'. $msg->getDateTime() . '</span>
	  					  </div>';
	}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Messenger</title>
	<link rel="shortcut icon" href="images/logos/DeepLinks.png"/>
	<link rel="stylesheet" type="text/css" href="styles/form.css"/>
	<link rel="stylesheet" type="text/css" href="styles/fonts.css"/>
	<link rel="stylesheet" href="styles/navBar.css"/>
	<link rel="stylesheet" type="text/css" href="styles/requestedMessages.css"/>
	<link rel="stylesheet" type="text/css" href="styles/messages.css"/>
	<link rel="stylesheet" type="text/css" href="styles/scrollBar.css"/>
	<script>
		function setCookie(cname, exHours, cvalue) {
		  const d = new Date();
		  d.setTime(d.getTime() + (exHours*60*60*1000));
		  let expires = "expires="+ d.toUTCString();
		  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
		  document.getElementById('messagesHeads').submit();
		}
	</script>
</head>
<body style="overflow: hidden; background-color: #f1f1f1;">
		<div style="width: 100%; height: 8vh; border-radius: 5px;">
			<ul>
	 	 	  <img style="float: left; margin-left: 18px; margin-top: 5px; width: 350px;" src="images/logos/DeepLinksLogo2.png">
			  <li style="float:right">
			  	<a href="actions/logout.php">Logout</a>
			  </li>
			</ul>
		</div>
		<div>
			<!-- left side -->
			<div style="background-color: #f1f1f1; width: 20%; height: 91vh; float: left; border-style: solid; border-color: white; border-right-color: black; ">
				<form style="margin:10px;" method="POST">
							<input type="text" placeholder="Search Friend" name="name">
				</form>
				<form style="overflow-x: hidden; overflow-y: scroll; height: 80vh;" id="messagesHeads" method="POST">
						<?php
							echo $headMessages;
						?>
				</form>
			</div>
			<!-- right side -->
			<div style="height: 91vh; background-color: #f1f1f1; width: 100%;">
					
				<!-- Friend Info -->
				<div style="width: 100%; height: 10vh;">
						<div style="  border: 0.5px solid white; padding: 1px;">
							<img style="float: left; max-width: 60px; border-radius: 50%; margin-top: 15px; margin-left: 10px; margin-right: 10px;" src="data:image/jpeg;base64,<?php echo base64_encode($targetPerson->getProfilePicture()); ?>" alt="Avatar">
							<h2 style="color: black; margin-top: 20px;"><?php echo $targetPerson->getName(); ?></h2>
							<h3 style="margin-top: -15px; color: #18D10B;"><?php 

								$ret = activeSince($targetPerson->getActiveTime());

								if($ret instanceof date){
									echo "Active now";
									echo 'Active since : ' . $ret;
								} else if($ret == "now"){
									echo "Active now";	
								} else {
									echo "Active from " . $ret;
								}
							 ?></h3>
						</div>
				</div>


				<script>
					function stickToBottomScroll(){
						var element = document.getElementById("chatHeadsID");
						element.scrollTop = element.scrollHeight;
					}
				</script>
				<!-- messages will work with container 2-->
				<div id="chatHeadsID"  style="overflow-y: scroll; height: 65vh; background-color: white;" onclick="stickToBottomScroll()">
						<?php 
							echo $targetChat;
						?>
						<!--
						<div class="friend">
							  <h3>Marwan Nabil</h3>
							  <p>Hello. How are you?</p>
							  <span class="friend-time">11:00</span>
						</div>
						<div class="you">
							  <h3>You</h3>
							  <p>Hello. How are you?</p>
							  <span class="you-time">11:00</span>
						</div>
						<div class="you">
							  <h3>You</h3>
							  <p>Hello. How are you?</p>
							  <span class="you-time">11:00</span>
						</div>
						<div class="you">
							  <h3>You</h3>
							  <p>Hello. How are you?</p>
							  <span class="you-time">11:00</span>
						</div>
						<div class="friend">
							  <h3>Marwan Nabil</h3>
							  <p>Hello. How are you?</p>
							  <span class="friend-time">11:00</span>
						</div>
						<div class="friend">
							  <h3>Marwan Nabil</h3>
							  <p>Hello. How are you?</p>
							  <span class="friend-time">11:00</span>
						</div>
					-->
				</div>

				<!--controllers-->
				<div style="background-color: #f1f1f1;">
					<form style="float:left; margin:10px; width: 78%;" method="POST">
							<input type="text" placeholder="Type your message here..." style="height: 70px;" name="message">
							<input type="submit" name="sendButton" value="Send" style="background-color: #e91b24;">
					</form></div>
			
			</div>

</body>
</html>