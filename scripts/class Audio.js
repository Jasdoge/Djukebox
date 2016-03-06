// JavaScript Document

var Audio = function(){};

(function(){
	"use strict";
	Audio = function(){
		var me = this;
		
		// Intervals
		this._interval_progress = null;			// Updates progress
		this._progress_speed = 500;				// Time between onProgress updates
		
		// Programmatic
		this.container = null;
		
		// Events that should be overwritten
		this.onLoad = function(){};
		this.onPlay = function(){};
		this.onFinish = function(){};
		this.onProgress = null;					// Needs to be bound before playing a sound
		
		
		// GET
		this.playing = function(){return !this.container.paused && this.container.currentTime;};
		
		
		this.bindOnProgress = function(fn, speed){
			this.onProgress = fn;
			this._progress_speed = speed;
		};
		
		
		// Takes a soundcloud permanent url and sets it to the current url
		this.setSoundBySoundcloud = function(url){
			SC.get("/resolve/?url="+encodeURIComponent(url), {}, function(sound){
				me.setSound(sound.stream_url+"?client_id="+Audio.client_id);
			});
		};
		
		this.setSound = function(url){
			this.container.src = url;
		};
		
		this.seekTo = function(seconds){
			this.container.currentTime = seconds;
		};
		
		this.pause = function(){
			this.container.pause();
		};
		
		this.play = function(){
			this.container.play();
			if(this._progress_speed > 0 && this.onProgress !== null){
				if(this._interval_progress !== null){clearInterval(this._interval_progress);}

				this._interval_progress = setInterval(
					function(){
						var data = me.container.currentTime/me.container.duration;
						me.onProgress.apply(me, [data]); 
					}, 
					this._progress_speed
				);
			}
		};
		
		
		this.__construct = function(){
			this.container = document.createElement("audio");
			this.container.addEventListener('canplay', function(){me.onLoad.apply(me);});
			
			this.container.addEventListener('ended', function(){
				// Make sure to clear the interval just in case
				if(this._interval_progress !== null){clearInterval(this._interval_progress);}
				me.onFinish.apply(me);
			});
			
			this.container.addEventListener('play', function(){me.onPlay.apply(me);});
			
			return this;
		};
		
		
		return this.__construct();
	};
	
	Audio.client_id = "";
	
	Audio.init = function(client_id){
		Audio.client_id = client_id;
		SC.initialize({
			client_id: client_id // I added my id here
		});		
	};
	
})();


