<?php

class Song extends DB4{
	// CLONE THESE ONES OVER TO THE SCRIPT THAT EXENDS THIS
	// Each table needs to have the primary key named "id" and be an integer
	
	public $author = "";
	public $title = "";
	public $location = "";
	public $added = "";
	public $wallet_dogecoin = "";
	public $wallet_bitcoin = "";
	public $wallet_litecoin = "";
	public $cost_btc = 0.0;
	public $cost_doge = 0;
	public $cost_ltc = 0.0;
	
	
	static $table = "jukebox_songs";							// Here you set the table to read/save to
	protected static $insertFields = array("author", "title");			// Fields required to insert a new row
	protected static $disregard_vals = array("added");			// These are member vars that should not be saved even when changed
	protected function onDataLoad(array $data){			// $data contains the data returned from mysl, so this is where you load that data into your class. Ex: $this->name = $data['name'];
		$this->autoload();
	}

	public function getOut(){
		return array(
			"id" => $this->id,
			"author" => htmlspecialchars($this->author),
			"title" => htmlspecialchars($this->title),
			"location" => htmlspecialchars($this->location),
			"added" => htmlspecialchars($this->added),
			"wallet_dogecoin" => htmlspecialchars($this->wallet_dogecoin),
			"wallet_bitcoin" => htmlspecialchars($this->wallet_bitcoin),
			"wallet_litecoin" => htmlspecialchars($this->wallet_litecoin),
			"cost_btc" => (float)$this->cost_btc,
			"cost_doge" => (int)$this->cost_doge,
			"cost_ltc" => (float)$this->cost_ltc,
			"proper" => $this->isProper()
		);
	}
	
	
	public function isProper(){
		// Needs to have an author, title, and address
		if(empty($this->author) || empty($this->title) || empty($this->location))return false;
		// Needs at least 1 address
		if(empty($this->wallet_bitcoin) && empty($this->wallet_litecoin) && empty($this->wallet_dogecoin))return false;
		return true;
	}

	static function getAllOut($limit_proper = true){
		$q = self::getAll($limit_proper);
		$out = array();
		foreach($q as $val)$out[] = $val->getOut();
		return $out;
	}
	
	static function getAll($limit_proper = true){
		$q = self::QGOA("SELECT * FROM ".self::$table." ORDER BY author, title");
		if(!$limit_proper)return $q;
		$out = array();
		foreach($q as $v){
			if($v->isProper())$out[] = $v;
		}
		return $out;
	}

}

