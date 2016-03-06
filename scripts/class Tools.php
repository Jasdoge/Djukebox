<?php

	class Tools{
		public static $ERRORS = array();
		public static $NOTICES = array();
		public static $N_DUMPED = false;
		
		static function addError($text){
			self::$ERRORS[] = $text;
		}
		static function addNotice($text){
			self::$NOTICES[] = $text;
		}
		
		static function minmax($val, $min = 0, $max = 1){
			if($val<$min)return $min;
			else if($val>$max)return $max;
			return $val; 
		}
		
		static function dumpNotices(){
			if(isset($GLOBALS['errors']))self::$ERRORS = array_merge(self::$ERRORS, $GLOBALS['errors']);
			if(isset($GLOBALS['notices']))self::$NOTICES = array_merge(self::$NOTICES, $GLOBALS['notices']);
			$GLOBALS['errors'] = array();
			$GLOBALS['notices'] = array();
			
			
			if(!self::$N_DUMPED){

				echo '<div class="noticewindow hidden buttonSound" id="noticediv"><p>';
				echo '<p class="noticeblue" id="noticetext"></p>';
				echo '</div>';
								
				echo '<div class="errorwindow hidden buttonSound" id="errordiv">';
				echo '<p class="red" id="errortext"></p>';
				echo '</div>';
				self::$N_DUMPED = true;
			}
			
			if(count(self::$ERRORS) || count(self::$NOTICES)){ 
				echo '<script><!-- 
				'; 
					if(count(self::$ERRORS))
						echo 'Tools.addErrors(\''.nl2br(addslashes(implode('<br />', self::$ERRORS))).'\', true);';
					
					if(count(self::$NOTICES))
						echo 'Tools.addErrors(\''.nl2br(addslashes(implode('<br />', self::$NOTICES))).'\', false);';
			
				echo '
				 --></script>';
			}
			
			self::$ERRORS = array();
			self::$NOTICES = array();
		}
		
		public static function postExists(){
			$fields = func_get_args();
			foreach($fields as $key=>$val)
				if(!isset($_POST[$val]))return false;
			return true;
		}
		
		public static function multiIsset($source, $test){
			foreach($test as $key=>$val){
				if(!isset($source[$val]))return false;
			}
			return true;
		}
		
		public static function float2fraction($n, $tolerance = 1.e-6) {
			$h1=1; $h2=0;
			$k1=0; $k2=1;
			$b = 1/$n;
			do {
				$b = 1/$b;
				$a = floor($b);
				$aux = $h1; $h1 = $a*$h1+$h2; $h2 = $aux;
				$aux = $k1; $k1 = $a*$k1+$k2; $k2 = $aux;
				$b = $b-$a;
			} while (abs($n-$h1/$k1) > $n*$tolerance);
		
			return "$h1/$k1";
		}
		
		public static function jasPaginate($startfrom, $ppp, $maxentries, $linkprefix, $nrstoshow){
			$totalpages = ceil($maxentries/$ppp);
			if($totalpages>1){
				$currentpage = round($startfrom/$ppp);
				$start = $currentpage-floor($nrstoshow/2);
				$end = $currentpage+floor($nrstoshow/2);
				if($start<0){
					$end -= $start;
					$start = 0;
				}
				if($end>$totalpages){
					$start-=($end-$totalpages);
					$end=$totalpages;
				}
				if($start<0)$start = 0;
				$prev = $currentpage-1; if($prev<0)$prev=0;
				$next = $currentpage+1; if($next>=$totalpages)$next=$totalpages-1;
				echo '<p>';
				echo '<a class="nav" href="'.$linkprefix.'0'.'">&lt;&lt;</a>';
				echo '<a class="nav"  href="'.$linkprefix.($prev*$ppp).'">&lt;</a>';
				for($i=$start; $i<$end; $i++){
					echo '<a  class="nav';
					if($i==$currentpage)echo ' current';
					echo '"  href="'.$linkprefix.$i*$ppp.'">'.($i+1).'</a> ';
				}
				echo '<a class="nav"  href="'.$linkprefix.$next*$ppp.'">&gt;</a>';
				echo '<a  class="nav" href="'.$linkprefix.($totalpages-1)*$ppp.'">&gt;&gt;</a>';
				echo '</p>';
			}
		}
		
	}
	
	

