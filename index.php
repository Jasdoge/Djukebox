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
$(function(){
	Ajax.bindErrorOverride(function(errors, notices){
		if(errors.length){
			$("#errordiv").append(errors.join("<br />")+"<br />").toggleClass("hidden", false);
		}
		if(notices.length){
			$("#noticediv").append(notices.join("<br />")+"<br />").toggleClass("hidden", false);
		}
	});
	
	$("#errordiv, #noticediv").click(function(){$(this).html("").toggleClass("hidden", true);});
	
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