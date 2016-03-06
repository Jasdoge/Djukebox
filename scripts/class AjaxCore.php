<?php
	
	class AjaxCore{
		static $task = '';
		static $data = array();
		static $vals = array();
		static $redir = false;
		
		// Optional methods
		protected static function preInit(){
			return true;
		}
		protected static function postInit(){
			
		}
		// Required method
		protected static function validateCSRF($CSRF){
			Tools::addError("CSRF needs to be set up");
			return false;
		}

		
		static function init(){
			if(!isset($_GET['t'])){
				self::$vals = "TASK_ERROR";
				Tools::addError("Unable to load JSON. Task missing.");
				self::finish(false);
			}
			
			$csrf = '';
			if(isset($_GET['csrf']))$csrf = $_GET['csrf'];
			if(!static::validateCSRF($csrf)){
				self::$vals = "CSRF_ERROR";
				Tools::addError("CSRF fail in AJAX. Got: ".$csrf); 
				self::finish(false);
			}
			

			self::$task = $_GET['t'];
			
			if(isset($_GET['d']))self::$data = (array)json_decode($_GET['d'], true);
			
			if(!static::preInit())continue;
			
			if(!method_exists(get_called_class(), 'pub'.self::$task)){
				Tools::addError("Method does not exist: pub".htmlspecialchars(self::$task));
				self::finish();
				continue;
			}
				
			$r = new ReflectionMethod(get_called_class(), 'pub'.self::$task);
			if(count(self::$data)<$r->getNumberOfRequiredParameters()){
				Tools::addError("Invalid amount of parameters for ".htmlspecialchars(self::$task));
				self::finish();
				continue;
			}
				
			if(!call_user_func_array(get_called_class().'::'.'pub'.self::$task, self::$data))self::finish();
			
			static::postInit();
			
			self::finish(true);			
			return true;
		}
		
		static function setVals($vals){
			if(!is_array($vals) && !is_object($vals)){
				self::$vals = $vals;
				return;
			}
			
			foreach($vals as $key=>$val){
				self::$vals[$key] = $val;
			}
		}
		
		static function setVal($key, $val){
			self::$vals[$key] = $val;
		}
		
		static function setRedir($redir, $blank = false){
			if($blank)self::$redir = array($redir, true);
			else self::$redir = $redir;
		}
		
		static function finish($success = false){
			header('Content-Type: application/json');
			$output = array(
				"vars" => self::$vals,
				"err" => Tools::$ERRORS,
				"note" => Tools::$NOTICES,
				"succ" => $success,
				"redir"=>self::$redir
			);
			die(json_encode($output));
		}
		

	}



