/*
	
	Contains song data
	
*/

// Song object
var Song = function(data){
	// Set by PHP
	var me = this;
	this.id = 0;
	this.author = "";
	this.title = "";
	this.location = "";
	this.wallet_dogecoin = "";
	this.wallet_bitcoin = "";
	this.wallet_litecoin = "";



	// Set by JS/localstorage
	this.lastPlayed = 0;		// Last time this track was played
	this.tips = function(){		// Gets total pending tips from transactions
		var out = 0;
		for(var i in this.transactions){if(true){
			out+= +this.transactions[i];
		}}
		return out;
	};							// Tips since last played
	this.playing = false;
	
	
	this.transactions = {};		// txid : amountReceived
	
	
	
	// set by Jukebox
	this.j_container = null;	// CreateJS container
	
	
	
	this.__construct = function(data){
		for(var i in data){
			if(this.hasOwnProperty(i)){
				this[i] = data[i];
			}
		}
		return this;
	};
		
	// Events
	this.onPlayStart = function(){
		// Set last played for now
		this.playing = true;
		this.lastPlayed = Date.now();
		return this;
	};
		
	this.onPlayFinish = function(){
		// Only save last played once the track is finished in case of glitchy fuckery
		this.playing = false;
		this.transactions = {};				// Clear old transactions
		Player.ls.saveTrack(this);
		return this;
	};
		

	return this.__construct(data);
};


// Static
(function(){
	"use strict";
	Song.playlist = [];			// Contains all song objects
	Song.add = function(data){	// Adds to the above list
		var s = new Song(data);
		Song.playlist.push(s);
	};
	
	// Fetches a list of songs
	Song.init = function(callback){
		new Ajax("GetPlaylist", [], null, function(){
			if(this.success && this.response.hasOwnProperty("_SONGS_")){
				for(var i =0; i<this.response._SONGS_.length; i++){
					Song.add(new Song(this.response._SONGS_[i]));
				}
				callback.apply();
			}
			else{
				console.log("Failed to initialize due to failed ajax request.");
			}
		});
		
	};
	
})();