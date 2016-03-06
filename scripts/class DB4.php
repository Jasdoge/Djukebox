<?php

class DB4{
// REQUIRED IN NEW CLASS
	protected static $PDO;
	public $id = 0; 								// Each object must always have an ID. There must always be an id in objectfell
	protected $stored_vals = array();				// This contains info about the table, used to only update changed values.
	public $created = 0;								// These are optional but common enough to warrant auto usage
	public $updated = '';
	public $flags = 0;
	static $convert_colons = TRUE;					// Make sure : vars are unique
	static $USE_REPLACE = false;					// Use REPLACE INTO instead of INSERT INTO
	
// CLONE THESE ONES OVER TO THE SCRIPT THAT EXENDS THIS
	// Each table needs to have the primary key named "id" and be an integer
	
	static $table = "";								// Here you set the table to read/save to
	protected static $insertFields = array();		// Fields required to insert a new row
	protected static $disregard_vals = array();			// These are member vars that should not be saved even when changed
	protected function onDataLoad(array $data){		// $data contains the data returned from mysl, so this is where you load that data into your class. Ex: $this->name = $data['name'];
		$this->autoload();
		
		// Debug backtrace
		$backtrace = array();
		$bt = debug_backtrace();
		foreach($bt as $key=>$val){
			if(isset($val['file']) && isset($val['line']))
				$backtrace[] = $val['file'].'.ln'.$val['line'];
		}
		$debug = ' @ '.implode($backtrace, ' &lt;- ');
		die('ERROR onDataLoad needs to be overwritten. '.$debug);
	}
	protected function onDataPreSave(){				// Optional, if you want to do actions right before data is saved
		// Raised before data is saved
	}
	protected function onDataPostSave(){			// Optional, if you want to do something after data is saved
		// Raised after save
	}
	protected function onInsertPre(){				// Optional
		//$this->created = time();
		return true;
	}
	protected function onInsertPost(){
		
	}
	protected function onDeletePre(){
		
	}
	protected function onDeletePost(){
		
	}
// CLONE END
	
	function __construct($id = 0){					// Obvious construct is obvious
		if((int)$id)$this->loadById($id);
	}
	
	static function ini($pdo){
		static::$PDO = $pdo;
		//static::$PDO = RootConfig::$PDO;
	}
	

	
// SQL methods
	static function MQ($query, $vars = array(), $pdo = NULL){
		if($pdo === NULL)$pdo = static::$PDO;
		$vars = (array)$vars;
		$original = $query;
		
		// PDO doesn't allow multiple vars with the same :name label, so this is a workaround that renames them in sequence like :name, :name1, :name2...
		if(self::$convert_colons){
			reset($vars);
			if(key($vars) !== 0){
				$search = array_reverse(array_keys($vars));
				$split = preg_split("/(".implode('|', $search).")/", $query, -1, PREG_SPLIT_DELIM_CAPTURE);
				$delims = array();
				$query = '';
				foreach($split as $key=>$val){
					if(isset($vars[$val])){
						$delims[] = $vars[$val];
						$query.= '?';
						continue;
					}
					$query.=$val;
				}
				$vars = $delims;
				
				//echo $query;
				//print_r($vars);
			}
		}
		
		// Debug backtrace
		$backtrace = array();
		$bt = debug_backtrace();
		foreach($bt as $key=>$val){
			if(isset($val['file']) && isset($val['line']))
				$backtrace[] = $val['file'].'.ln'.$val['line'];
		}
		$debug = ' @ '.implode($backtrace, ' &lt;- ');
		
		if($pdo ===  NULL)die("MYSQL PDO undefined <br />".$debug);
		
		try{
			$call = $pdo->prepare($query);
		}catch(Exception $e){ 
			die("PDO prepare error ".$e); 
		}
		if(!is_object($call))die("PDO prepare failed: ".$debug);
		
		
		$call->execute($vars)or die("MYSQL ERROR <br />".print_r($call->errorInfo(), true).'<br />'.$debug);
		return $call;
	}
	
	// MysqlInsertID Last insert id
	static function MII($pdo = NULL){
		if($pdo === NULL)$pdo = static::$PDO;
		return $pdo->lastInsertId();
	}
	
	// Mysql Affected Rows
	static function MAR($query){
		return $query->rowCount();
	}
	
	// MysqlNumRows
	static function MNR($query){
		return count($query->fetchAll());
	}
	
	// query -> get assoc - Returns an array of all results
	static function QGA($query, $vars = array(), $pdo = NULL){
		$vars = (array)$vars;
		$call = static::MQ($query, $vars, $pdo);
		return $call->fetchAll(PDO::FETCH_ASSOC);
	}
	
	// query -> get single assoc
	static function QGS($query, $vars = array(), $pdo = NULL){
		$vars = (array)$vars;
		$call = static::MQ($query, $vars, $pdo);
		return $call->fetch(PDO::FETCH_ASSOC);
	}
	
	// query -> get objects / all
	static function QGOA($query, $vars = array(), $pdo = NULL){
		$vars = (array)$vars;
		$q = self::QGA($query, $vars, $pdo);
		if($q === false)return array();
		$out = array();
		foreach($q as $val){
			$o = new static;
			$o->load($val);
			$out[] = $o;
		}
		return $out;
	}
	
	// query -> get objects / single
	static function QGOS($query, $vars = array(), $pdo = NULL){
		$vars = (array)$vars;
		$arr = self::QGS($query, $vars, $pdo);
		if($arr === false)return false;
		$obj = new static;
		$obj->load($arr);
		return $obj;
	}
	// quickly Check if an ID exists
	static function idExists($id, $pdo = NULL){
		$call = static::MQ("SELECT id FROM ".static::$table." WHERE id=? LIMIT 1", array((int)$id), $pdo);
		return static::MNR($call);
	}

	
// Methods you'll probably use
	// Load an object from DB by id (which should be your primary key and an integer)
	public function loadByID($id, $vals_only = false){
		if($arr = static::QGS("SELECT * FROM ".static::$table." WHERE id=?", array((int)$id)))
			$this->load($arr, $vals_only);
	}
	
	// This is where you load data returned from mysql
	public function load(array $data, $vals_only = false){
		$this->stored_vals = $data;
		$this->id = (int)$data['id'];
		if(!$vals_only)$this->onDataLoad($data);
	}
	
	
	// Delete this object from mysql
	public function delete(){
		if($this->onDeletePre()===false)return false;
		$out = static::MAR(static::MQ("DELETE FROM ".static::$table." WHERE id=?", array((int)$this->id)));
		$this->onDeletePost();
		return $out;
	}
	
	public function insert($ignore_errors = false){
		$this->created = time();
		
		
		if($this->onInsertPre() === false){
			return false;
		}
		
		$save = array();
		foreach(static::$insertFields as $key=>$val){
			if(isset($this->{$val})){
				if($this->{$val} === NULL || $this->{$val} === ''){
					Tools::addError("Unable to insert, required var: ".htmlspecialchars($val).' is NULL in class '.get_class($this));
					return false;
				}
				$save[$val] = $this->{$val};
			}
		}
		$out = $this->save($save, true, false, true);
		$this->onInsertPost();
		return $out;
	}
	
		
	protected function autoload($data = NULL){
		if($data === NULL)$data = $this->stored_vals;
		
		$set = $this->typecast($data);
		foreach($set as $key=>$val){
			$this->{$key} = $val;
		}
	}
	
	private function typecast(array $data){
		$vars = get_class_vars(get_class($this));
		foreach($vars as $key=>$val){
			if(isset($data[$key]) || array_key_exists($key, $data)){
				if(is_array($val))$data[$key] = (array)json_decode($data[$key], true);
				else{
					$type = gettype($val);
					if($type === "boolean")$type = "integer";
					if($type === "NULL")$type = "string";
					settype($data[$key], $type);
				}
			}
		}
		return $data;
	}
	
	static function a2q(array $input){
		$out = array();
		foreach($input as $key=>$val)$out[] = "?";
		return $out;
	}
	static function q(array $input){
		return self::a2q($input);
	}
	static function qn($prefix, array $input){
		$out = array();
		foreach($qn as $key=>$val)
			$out[] = $prefix;
		return $out;
	}
	
	private function typeToForm($var){
		if(is_numeric($v))return 'number';
		if(is_array($v) || strlen($var)>100)return 'textarea';
		return 'text';
	}
	
	// if inputs are array(inputType, inputName, inputVal/NULL(Use current), placeholder, inputData)
	// Inputdata is only used for a couple of types:
	// radio/checkbox = (int)checked, (str)displayname
	// select = select options key=>val, val = current selected option
	// freetext = just put the text in inputName field
	// br = rowbreak
	public function buildStdForm($action = '', array $inputs, $add = ''){
		$out = '<form enctype="multipart/form-data" id="'.get_class($this).'Editor" method="POST" '.($action ? 'action="'.$action.'" ' : '').'>';
		
		foreach($inputs as $val){
			$inputType = strtolower(array_shift($val));
			$inputName = array_shift($val);
			$inputVal = array_shift($val);
			if($inputVal == NULL && isset($this->{$inputName}))$inputVal = $this->{$inputName};
			if(is_array($inputVal)||is_object($inputVal))
						$inputVal = json_encode($inputVal);
			
			$inputPlaceholder = array_shift($val);
			$inputData = array_shift($val);
			
			if(!empty($inputType)){
				if($inputType == 'freetext'){
					$out .= $inputName;
					continue;
				}
				
				$nobr = array('radio', 'checkbox', 'submit', 'button');
				$nolabel = array('submit', 'button');
				
				$stdinputs = array('text', 'number', 'date', 'timestamp', 'email', 'password', 'radio', 'checkbox', 'button', 'submit', 'color', 'range', 'month', 'week', 'time', 'search', 'tel', 'url');
				$o = '<label>'
					.(!in_array($inputType,$nolabel) ? 
						'<span class="title">'.htmlspecialchars((
							$inputType!='checkbox' && $inputType != 'radio' 
							? $inputPlaceholder 
							: $inputData[1] 
						))
						: ''
					).'</span>'
					.(!in_array($inputType,$nobr) ? '<br />' :'');
				if(in_array($inputType, $stdinputs)){
					$o.= '<input type="'.$inputType.'" name="'.$inputName.'" '.(!empty($inputPlaceholder) ? 'placeholder="'.htmlspecialchars($inputPlaceholder).'"' : '').' value="'.htmlspecialchars($inputVal).'" ';
					if(($inputType == 'radio' || $inputType == 'checkbox') && (int)$inputData[0])$o .= 'checked';
					$o.= ' />';
				}
				else if($inputType == 'select'){
					$o.= '<select name="'.$inputName.'">';
					foreach($inputData as $key=>$val)
						$o.= '<option value="'.$key.'" '.($inputVal == $key ? 'selected' :'').'>'.$val.'</option>';
					$o.= '</select>';
				}
				else if($inputType == 'textarea'){
					$o.= '<textarea name="'.$inputName.'">';
					$o.= htmlspecialchars($inputVal);
					$o.= '</textarea>';
				}
				else if($inputType == 'br'){}
				else continue;
				$out.= $o.'</label>';
				if($inputType == 'br')$out.= '<br />';
			}
		}
		$out.= $add;
		$out .= '</form>';
		return $out;
	}
	
	public function readStdForm(array $allowed_vals, $insertNew = false){
		if($insertNew)$this->id = 0;
		foreach($allowed_vals as $val){
			if(isset($this->{$val}) && isset($_POST[$val])){
				if(is_array($_POST[$val])){
					$this->{$val} = 0;
					foreach($_POST[$val] as $i)$this->{$val} = $this->{$val}|(int)$i;
				}else{
					$type = $this->typecast(array($val=>$_POST[$val]));
					$this->{$val} = array_shift($type);
				}
			}
		}
		if($this->id>0)return $this->save();
		return $this->insert();
	}
	
	// Standard save - $fieldsOnCreate is REQUIRED to create a new row if one doesn't already exist
	// If the object's id doesn't exist in the DB and $fieldsOnCreate is empty, it will fail
	// fieldsOnCreate needs to contain at least 1 field present in the table, and usually all unique fields other than primary index
	// such as $user->save(array("name"=>"Jasdac")); which will insert a new row with name as jasdac
	// after which all vars set in your class will be saved
	// default behavior is to convert empty strings to mysql NULL, you can override that with setting $emptyToNull to false
	public function save(array $fieldsOnCreate = array(), $emptyToNull = true, $echo_query = false, $ignore_errors = false){
		if($this->id == 0){
			$qs = array();
			if(empty($fieldsOnCreate)){return false;}
			// Escape NULL
			foreach($fieldsOnCreate as $key=>$val){
				if(($val === '' && $emptyToNull) || $val === NULL)$fieldsOnCreate[$key] = NULL;
				$qs[] = "?";
			}
			
			$keys = array_keys($fieldsOnCreate);
			
			$query = static::MQ((self::$USE_REPLACE ? 'REPLACE': 'INSERT').($ignore_errors ? ' IGNORE ':'')." INTO ".static::$table." ".(!empty($keys) ? "(`".implode('`,`',$keys)."`)" : '()')." VALUES ".(!empty($keys) ? "(".implode(',',$qs).")" : '()'), array_values($fieldsOnCreate));
			
			$this->id = static::MII();
			$this->loadByID($this->id, true);
		}
		if($this->id == 0){
			Tools::addError("ID is 0 error in DB4");
			return false;
		}
		if(empty($this->stored_vals)){
			Tools::addError("Stored vals are empty in DB4");
			return false;
		}
		$this->onDataPreSave();
		
		$op = array(); $set = array();

		foreach($this->stored_vals as $key=>$val){
			$dis = array_merge(static::$disregard_vals, array('updated', 'created', 'id'));
			if(isset($this->{$key}) && !in_array($key, $dis)){
				$v = $this->{$key};
				if(is_array($v) || is_object($v))$v = json_encode($v);
				if($v != $val){
					$op[':'.$key] = $v;
					$set[] = '`'.$key.'`=:'.$key;
				}
			}
		}
		
		$success = true;
		if(count($set)){
			$q = "UPDATE ".static::$table." SET ".implode(',',$set)." WHERE id=:id";
			$op[':id'] = $this->id;
			if($echo_query)echo $q;
			//if(JasxSessionManager::$JASX_USER->id == 1)echo "UPDATE ".static::$table." SET ".implode(',',$set)." WHERE id=:id ::";
			$query = static::MQ($q, $op);
			$success = static::MAR($query);
		}
		$this->onDataPostSave();
		return $success;
	} 
	
	static function getConsts($prefix = false){
		$refl = new ReflectionClass(get_called_class());
		$consts = $refl->getConstants();
		if($prefix === false)return $consts;
		$out = array();
		foreach($consts as $key=>$val){
			if(strrpos($key, $prefix, -strlen($key)) !== FALSE)$out[$key] = $val;
		}
		return $out;
	}		

	

}

