<?php
	
	require_once(__DIR__.'/config.php');
	
	//if(!Config::isLocal())die();
	

?>
<!doctype html>
<html><head>
<meta charset="utf-8">
<title>The Djukebox</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script src="/scripts/ajax3.js.php"></script>
<script>
var Tools = {
	ini :function(){
		$("#errordiv, #noticediv").click(function(){$(this).html("").toggleClass("hidden", true);});
	},
	addErrors:function(text, isNotice){
		var targ = "#errordiv"
		if(!isNotice){
			targ = "#noticediv";
		}
		$(targ).append(text).toggleClass("hidden", false);
	}
};


$(function(){
	
	Ajax.bindErrorOverride(function(errors, notices){
		if(errors.length){
			Tools.addErrors(errors.join("<br />")+"<br />", true);
		}
		if(notices.length){
			Tools.addErrors(notices.join("<br />")+"<br />", true);
		}
	});
	
	Tools.ini();
});
</script>
<link rel="stylesheet" href="/style.css" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
</head>


<body><div id="ff_fix">
<?php

Tools::dumpNotices();

FrontController::ini();

Tools::dumpNotices();

?>
</div></body>
</html>