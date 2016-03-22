<?php

class FrontController{
	
	static $ARGS = array();
	static $PAGE = "";
	
	static function ini(){
		if(isset($_GET['q'])){
			self::$ARGS = explode('/', $_GET['q']);
		}
		$page = strtolower(array_shift(self::$ARGS));
		

		if(Config::isLocal()){
			if($page === "player"){
				include __DIR__.'/controllers/player.php';
				return;
			}
			else if($page === "editor"){
				include __DIR__.'/controllers/editor.php';
				return;
			}
		}
		
		include __DIR__.'/controllers/listing.php';
		
	}
	
	
}

