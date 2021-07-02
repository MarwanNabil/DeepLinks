<?php

//databaseConnect Directory.
include_once $_SERVER['DOCUMENT_ROOT'] . '\PhpProjects\Training\DeepLinks\essentials\databaseConnect.php';

class person{
	private $ID;
	private $firstName , $secondName;
	private $username , $email , $password;
	private $birthDate , $activeTime;
	private $gender;
	private $country;
	private $profilePicture;
	function __construct($ID , $firstName , $secondName , $username , $email , $password , $birthDate , $activeTime , $gender , $country , $profilePicture){
		$this->ID = $ID;
		$this->username = $username;
		$this->email = $email;
		$this->password = $password;

		$this->firstName = $firstName;
		$this->secondName = $secondName;
		$this->gender = $gender;
		$this->country = $country;
		$this->profilePicture = $profilePicture;

		$this->birthDate = $birthDate;
		$this->activeTime = $activeTime;
	}

	function getID(){
		return $this->ID;
	}
	function getFirstName(){
		return $this->firstName;
	}
	function getName(){
		return $this->firstName . ' ' . $this->secondName;
	}
	function getEmail(){
		return $this->email;
	}
	function getProfilePicture(){
		return $this->profilePicture;
	}
	function setFirstName($firstName){
		$this->firstName = $firstName;
		global $db;
		$q = $db->prepare("UPDATE person set firstName = ? WHERE username = ? AND password = ?");
		$q->execute(array($this->firstName , $this->username , $this->password));
	}
	function setSecondName($secondName){
		$this->secondName = $secondName;
		global $db;
		$q = $db->prepare("UPDATE person set secondName = ? WHERE username = ? AND password = ?");
		$q->execute(array($this->secondName , $this->username , $this->password));
	}
	function setActiveTimeToNow(){
		global $db;
		$currentDatTime = date("Y-m-d H:i:s");
		$this->activeTime = $currentDatTime;
		$q = $db->prepare("UPDATE person set activeTime = ? WHERE username = ? AND password = ?");
		$q->execute(array($currentDatTime , $this->username , $this->password));
	}
	function getActiveTime(){
		return $this->activeTime;
	}
}


$numberOfPeople;
$allPeople = new \ds\Vector();

function loadPeopleFromDatabase(){
	global $db;
	global $numberOfPeople;
	global $allPeople;

	$q = $db->prepare("SELECT * FROM person");
	$q->execute(array());

	$counts = $q->rowCount();
	while($row = $q->fetch()){
		
		//$ID , $firstName , $secondName , $username , $email , $password , $birthDate , $activeTime , $gender , $country , $profilePicture
		$user = new person($row['id'] , $row['firstName'] , $row['secondName'] , $row['username'] , $row['email'] , $row['password'] , $row['date'] , $row['activeTime'] , $row['gender'] , $row['country'] , $row['profilePicture']);
		$numberOfPeople++;
		$allPeople->push($user);
	}
}

?>