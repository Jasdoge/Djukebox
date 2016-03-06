<?php

require_once(__DIR__.'/class AjaxCore.php');

class Ajax extends AjaxCore{
	
	// Optional methods
	protected static function preInit(){
		return true;
	}
	protected static function postInit(){
		
	}
	// Required method
	protected static function validateCSRF($CSRF){
		return true;
	}
	
	
	
	static function pubGetPlaylist(){
		
		self::setVal("_SONGS_", Song::getAllOut());
		return true;
	}
	
	
	static function pubSaveSong($id = 0){
		if(!Config::isLocal()){
			Tools::addError("This command can only be done from localhost.");
			return false;
		}
		if(!Tools::multiIsset($_POST, array("author", "location", "title"))){
			Tools::addError("Missing essential save data.");
			return false;
		}
		
		foreach($_POST as $key=>$val){
			$_POST[$key] = trim($val);
		}
		
		
		$song = new Song($id);
		$song->author = $_POST['author'];
		$song->location = $_POST['location'];
		$song->title = $_POST['title'];
		
		$song->wallet_dogecoin = "";
		$song->wallet_bitcoin = "";
		$song->wallet_litecoin = "";
		
		if(isset($_POST['wallet_dogecoin']))
			$song->wallet_dogecoin = $_POST['wallet_dogecoin'];
		if(isset($_POST['wallet_bitcoin']))
			$song->wallet_bitcoin = $_POST['wallet_bitcoin'];
		if(isset($_POST['wallet_litecoin']))
			$song->wallet_litecoin = $_POST['wallet_litecoin'];
		
		if($song->id && $song->save()){
			Tools::addNotice("Song saved!");
		}
		else if(!$song->id && $song->insert()){
			Tools::addNotice("Song added!");
		}
		else{
			Tools::addError("Song not saved.");
			return false;
		}
		self::setVal("SONG", $song->getOut());
		return true;
	}


	
}
