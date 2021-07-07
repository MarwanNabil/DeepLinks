<?php

//databaseConnect Directory.
include_once $_SERVER['DOCUMENT_ROOT'] . '\PhpProjects\Training\DeepLinks\essentials\databaseConnect.php';

//we include person class to identify the current person identity.
include_once $_SERVER['DOCUMENT_ROOT'] . '\PhpProjects\Training\DeepLinks\essentials\classes\person.php';


/*
link means a connection between
the logged in user and all people.

but currently link means current connection "the one you are talking with him now."

once you go from one conversation to another the old link will be destroyed and a new link will be established.

it's abstract class as we will inherit from it
class messages.
*/

abstract class link{
 	protected $friend; //from type person
 	protected static $numberOfDownloadedMessages = 0;
 	protected function __construct($user){
 		$this->friend = $user;
 		self::$numberOfDownloadedMessages++;
 	}
}

class message extends link{
	private $textMessage;
	private $dateTime;
	private $receivedMessage;

	//receivedMessage is a boolean , 0 means your message to that friend
	//1 means that friend (from class link) , had sent you that message.
	public function __construct($user , $textMessage , $dateTime , $receivedMessage){
		parent::__construct($user);
		/*
			this is for more optimizing the efficiency
			at first for every link (person)
			you only get(download from database) one message
			as it appears in the messages' head.
			once the user click on any head, it's time to download more depending on where he clicked.
		*/
		$this->textMessage = $textMessage;
		$this->dateTime = $dateTime;
		$this->receivedMessage = $receivedMessage;
	}
	public function getMessageSenderID(){
		global $loggedInPerson;
		if($this->receivedMessage){
			return $this->friend->getID();
		} else {
			return $loggedInPerson->getID();
		}
	}
	public function getMessageSenderFirstName(){
		global $loggedInPerson;
		if($this->receivedMessage){
			return $this->friend->getFirstName();
		} else {
			return $loggedInPerson->getFirstName();
		}
	}
	public function isBelongsToMyFriend(){
		return $this->receivedMessage;
	}
	public function getTextMessage(){
		return $this->textMessage;
	}
	public function getDateTime(){
		return $this->dateTime;
	}
}

$sizeOfActiveConverstation;
$activeConversation = new \ds\Vector();

function uploadMessagesTargetPersonAndLoggedInPerson($targetPerson , $loggedInPerson){
	global $db;
	global $sizeOfActiveConverstation;
	global $activeConversation;

	$q = $db->prepare("SELECT * FROM message WHERE (receiverID = ? and senderID = ?) or (receiverID = ? and senderID = ?) ORDER BY sentTime");

	$q->execute(array($targetPerson->getID() , $loggedInPerson->getID() , $loggedInPerson->getID() , $targetPerson->getID()));

	while($row = $q->fetch()){

		$receivedFromFriend = 1;
		if($row['senderID'] == $loggedInPerson->getID()){
			$receivedFromFriend = 0;
		}

		$msgUpload = new message($targetPerson , $row['msgText'] , $row['sentTime'] , $receivedFromFriend);
		$activeConversation->push($msgUpload);
		$sizeOfActiveConverstation ++;
	}
}

//pre conditions 
//logged In User exists
//Target Person User exists
function sendMessage($yourMessage){
	global $db;
	global $loggedInPerson;
	global $targetPerson;
	$q = $db->prepare("INSERT INTO message (senderID , receiverID , sentTime , msgText) VALUES (? , ? , ? , ?)");
	$q->execute(array($loggedInPerson->getID() , $targetPerson->getID() , date("Y-m-d H:i:s") , $yourMessage));
}



//this class only for messages' head.
class easyMsg{
	private $friend;
	private $receivedMessageBool; //true when friend sent it.
	private $textMessage;
	private $dateTime;
	public function __construct($friend , $receivedMessageBool , $textMessage , $dateTime){
		$this->textMessage = $textMessage;
		if(is_null($dateTime)){
			$this->dateTime = date("Y-m-d H:i:s"); //Year - Month - Day - Hour - Min - Sec
		} else {
			$this->dateTime = $dateTime;
		}
		$this->friend = $friend;
		$this->receivedMessageBool = $receivedMessageBool;
	}
	public function getFriend(){
		return $this->friend;
	}
	public function getFriendID(){
		return $this->friend->getID();
	}
	public function getDateTime(){
		return $this->dateTime;
	}
	public function getReceivedMessageBool(){
		return $this->receivedMessageBool;
	}
	public function getTextMessage(){
		return $this->textMessage;
	}
}

function getLastMessageFromDatabase($loggedInPerson , $targetPerson){
	global $db;

	$q = $db->prepare("SELECT * FROM message WHERE (receiverID = ? and senderID = ?) or (receiverID = ? and senderID = ?) ORDER BY sentTime DESC LIMIT 1");
	$q->execute(array($loggedInPerson->getID() , $targetPerson->getID() , $targetPerson->getID() , $loggedInPerson->getID()));
	$row = $q->fetch();
	
	$friendID = $targetPerson->getID();

	$msg;

	if($q->rowCount() == 0){
		$msg = new easyMsg($targetPerson , 0 , "you're new friends." , null);
	} else {

		$isReceived = 1;
		if($row['senderID'] == $loggedInPerson->getID()){
			$isReceived = 0;
		}
		
		$msg = new easyMsg($targetPerson , $isReceived , $row['msgText'] , $row['sentTime']);
	}
	return $msg;
}


/*
this is so important to loads the chats where the one who made the newest action is at
the top of the message heads , the oldest is the bottm one.
*/

$chatHeads = new \ds\Vector();

function chatHeadsIntializer(){
	global $numberOfPeople; //number of active people
	global $allPeople; //actual people as objects
	global $loggedInPerson;
	global $chatHeads;

	for($i = 0; $i < $numberOfPeople; $i++){
		 $chatHeads->push(getLastMessageFromDatabase($loggedInPerson , $allPeople->get($i)));
	}

	//sort the message heads from the newest action to the oldest action.

	$chatHeads->sort(function($a , $b){
			return strtotime($a->getDateTime()) < strtotime($b->getDateTime());
	});
}

?>
