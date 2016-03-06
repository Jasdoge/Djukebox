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
			"wallet_litecoin" => htmlspecialchars($this->wallet_litecoin)
		);
	}

	static function getAllOut(){
		$q = self::getAll();
		$out = array();
		foreach($q as $val)$out[] = $val->getOut();
		return $out;
	}
	
	static function getAll(){
		return self::QGOA("SELECT * FROM ".self::$table." ORDER BY author, title");
	}

}

