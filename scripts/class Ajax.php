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
	
	// Checks post for a wallet address and does simple validation
	private static function getAddress($type){
		$type = strtolower($type);
		
		if(isset($_POST['wallet_'.$type]) && !empty($_POST['wallet_'.$type])){
			$wallet = trim($_POST['wallet_'.$type]);
			// Address has to be alphanumeric
			if(ctype_alnum($_POST['wallet_'.$type])){
				return $wallet;
			}
			else 
				Tools::addError("Invalid address for ".htmlspecialchars($type).": ".htmlspecialchars($wallet));
		}
		return "";
	}
	
	
	
	
// PUBLIC
	// Returns songs
	static function pubGetPlaylist(){
		self::setVal("_SONGS_", Song::getAllOut());
		return true;
	}
	
	static function pubDeleteSong($id = 0){
		if(!Config::isLocal()){
			Tools::addError("This command can only be done from localhost.");
			return false;
		}
		$song = new Song($id);
		if(!$song->id){
			Tools::addError("Song not found");
			return false;
		}
		$song->delete();
		return true;
	}
	
	// Saves a song. Requires localhost.
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
		$song->author = trim($_POST['author']);
		$song->location = trim($_POST['location']);
		$song->title = trim($_POST['title']);
		$song->cost_btc = (float)$_POST['cost_btc'];
		$song->cost_ltc = (float)$_POST['cost_ltc'];
		$song->cost_doge = (int)$_POST['cost_doge'];
		
		
		$song->wallet_dogecoin = self::getAddress('dogecoin');
		$song->wallet_bitcoin = self::getAddress('bitcoin');
		$song->wallet_litecoin = self::getAddress('litecoin');
		
		

		
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
