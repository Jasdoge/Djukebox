<?php
session_start();

require_once(__DIR__.'/scripts/class Tools.php');
require_once(__DIR__.'/scripts/class DB4.php');
require_once(__DIR__.'/scripts/class Song.php');
require_once(__DIR__.'/scripts/class FrontController.php');

class Config{
	
	static $PDO = NULL;
	
	static $DEBUG = true;					// Enables/Disables non-localhost access to the editor. Make sure this is off in production
	static $STORE_NAME = "Shibe Café";				// Store name
	static $BLOCKIO_DOGE = "ffd0-84c9-5154-91cd";		// BlockIO API key - Doge
	static $BLOCKIO_BTC = "2fab-7903-e588-36ce";		// BlockIO API key - Bitcoin
	static $BLOCKIO_LTC = "0344-96ca-8652-7bb1";		// BlockIO API key - Litecoin
	
	
	static $MYSQL_SERVER = "localhost";
	static $MYSQL_USER = "jasdoge";
	static $MYSQL_PASS = "jasdoge";
	static $MYSQL_DB = "jasdoge";
	
	
	
	static function initMYSQL($url, $db, $user, $pass){
		self::$PDO = new PDO('mysql:charset=utf8;host='.$url.';dbname='.$db, $user, $pass)or die("Unable to connect");
	}
	
	static function isLocal(){
		if(self::$DEBUG)return true;
		if ($_SERVER['SERVER_ADDR'] != $_SERVER['REMOTE_ADDR']){return false;}
		return true;
	}
	
	// Table creation
	static function createTable(){
	DB4::MQ(
"
CREATE TABLE IF NOT EXISTS `jukebox_songs` (
`id` int(11) NOT NULL,
  `author` varchar(128) NOT NULL,
  `title` varchar(128) NOT NULL,
  `location` tinytext,
  `wallet_dogecoin` varchar(34) DEFAULT NULL,
  `wallet_bitcoin` varchar(34) DEFAULT NULL,
  `wallet_litecoin` varchar(34) DEFAULT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cost_btc` decimal(20,8) NOT NULL DEFAULT '0.00000000',
  `cost_doge` bigint(20) NOT NULL DEFAULT '0',
  `cost_ltc` decimal(20,8) NOT NULL DEFAULT '0.00000000'
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

ALTER TABLE `jukebox_songs`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `author` (`author`,`title`), ADD UNIQUE KEY `wallet_dogecoin` (`wallet_dogecoin`), ADD UNIQUE KEY `wallet_bitcoin` (`wallet_bitcoin`), ADD UNIQUE KEY `wallet_litecoin` (`wallet_litecoin`);
ALTER TABLE `jukebox_songs`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;
");
	
	}
	
	static function ini(){
		self::initMYSQL(self::$MYSQL_SERVER, self::$MYSQL_USER, self::$MYSQL_PASS, self::$MYSQL_DB);
		DB4::ini(self::$PDO);
		
		
		if(self::isLocal())
			self::createTable();
			
		
	}
	
}

Config::ini(); 

