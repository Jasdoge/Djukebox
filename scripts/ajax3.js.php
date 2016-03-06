<?php
	header('Content-Type: application/javascript');
	
	$CSRF = '';
	if(isset($_GET['CSRF_TOKEN']))$CSRF = $_GET['CSRF_TOKEN'];
	
?>
// JavaScript Document
// function callback((obj)this){}
function Ajax(task, data, form, callback, overrideErrors){
	this.task = task;
    this.data = data;
	this.form = form;
    this.success = false;
	this.response = null;
    this.url = Ajax.url;
    this.overrideErrors = overrideErrors;
    
    if(window.FormData === undefined){
    	console.log("Browser does not support file uploads.");
    }
    else{
    	if(!this.form instanceof FormData){
    		this.form = new FormData($(this.form)[0]);
        }
    }
	
    
	var me = this;
	
    $.ajax({
		url:Ajax.url+"?t="+encodeURIComponent(me.task)+"&d="+encodeURIComponent(JSON.stringify(data))<?php if(!empty($CSRF))echo "+'&csrf=".urlencode($CSRF)."'";?>,
		data:form,
        cache:false,
        contentType:false,
        processData:false,
        type:'POST'
	}).always(function(d){
    	var i;
        
        //console.log(me, d);
        
		me.response = d;
		if(d.hasOwnProperty("succ")){
            if(Ajax.overrideAllErrors !== null){
            	Ajax.overrideAllErrors(d.err, d.note);
            }
            else if(me.overrideErrors !== undefined){
            	overrideErrors(d.err, d.note);
            }
            else{
               	if(d.err.length){
                	addErrors(d.err.join('<br />'), true);
                }
                if(d.note.length){
                	addErrors(d.note.join('<br />'), false);
                }
                if(d.err.length || d.note.length){
                	$("html, body").animate({ scrollTop: 0 }, "slow");
                }
			}
            
            
            if(d.redir !== false){
            	console.log(d.redir);
            	var url = d.redir;
                var blank = false;
                if(typeof url !== "string" && url.length>1){
                	blank = url[1];
                    url = url[0];
                }
                
                if(!blank){
                	window.location = d.redir;
                }
                else{
                	window.open(url, '_blank');
                }
            }
            
            me.response = d.vars;
			if(d.succ){
            	me.success = true;
            }
		}else{
         	console.log("Ajax failure", me);
        }
        
        if(callback !== undefined){
        	callback.apply(me, [me]);
        }
        for(i = 0; i<Ajax.onSuccessFunctions.length; i++){
        	Ajax.onSuccessFunctions[i].apply(me, [me]);
        }
	}).fail(function(data){
		console.log("AJAX Fail: ");
		console.log(data);
	});
} 


// These let you tie project specific actions
Ajax.url = '/ajax.php';
Ajax.onSuccessFunctions = new Array;
Ajax.overrideAllErrors = null;

Ajax.setConf = function(url, onSuccessFunctions){
	Ajax.url = url;
    if(onSuccessFunctions !== undefined){
    	if(onSuccessFunctions.constructor !== Array){onSuccessFunctions = [onSuccessFunctions];}
    	Ajax.onSuccessFunctions = onSuccessFunctions;
    }
}
Ajax.bindOnSuccessFunction = function(fn){
	Ajax.onSuccessFunctions.push(fn);
}
Ajax.bindErrorOverride = function(fn){
	Ajax.overrideAllErrors = fn;
}

<?php

	session_write_close();

