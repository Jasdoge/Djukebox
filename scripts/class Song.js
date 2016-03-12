/*
	
	Contains song data
	
*/

// Song object
var Song = function(data){
	"use strict";
	
	// Set by PHP
	this.id = 0;
	this.author = "";
	this.title = "";
	this.location = "";
	this.wallet_dogecoin = "";
	this.wallet_bitcoin = "";
	this.wallet_litecoin = "";
	this.cost_btc = 0.0;
	this.cost_doge = 0;
	this.cost_ltc = 0.0;
	this.proper = false;

	// Set by JS/localstorage
	this.lastPlayed = 0;		// Last time this track was played
	this.playing = false;
	this.transactions = {};
	this.transactions[Song.BLOCKCHAIN_DOGE] = {};
	this.transactions[Song.BLOCKCHAIN_LTC] = {};
	this.transactions[Song.BLOCKCHAIN_BTC] = {};

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

	this.tips = function(blockchain){		// Gets total pending tips from transactions
		var out = 0;
		for(var i in this.transactions[blockchain]){if(true){
			out+= +this.transactions[blockchain][i];
		}}
		return out;
	};							// Tips since last played

	// Returns a total value in bitcoin of current tips
	this.totalTips = function(){
		// Player.dogeValue Player.ltcValue
		return 	this.tips(Song.BLOCKCHAIN_BTC)+
				this.tips(Song.BLOCKCHAIN_DOGE)/Player.dogeValue+
				this.tips(Song.BLOCKCHAIN_DOGE)/Player.ltcValue;
	};

	this.chainToCost = function(blockchain){
		if(blockchain === Song.BLOCKCHAIN_BTC){return this.cost_btc;}
		else if(blockchain === Song.BLOCKCHAIN_LTC){return this.cost_ltc;}
		return this.cost_doge;
	};
	
	// Tipped enough to play
	this.tippedEnough = function(){
		var chains = [Song.BLOCKCHAIN_DOGE, Song.BLOCKCHAIN_BTC, Song.BLOCKCHAIN_LTC];
		for(var i = 0; i<chains.length; i++){
			var blockchain = chains[i];
			var received = this.tips(blockchain);
			var cost = this.chainToCost(blockchain);
			if(
				(received>=cost && cost > 0) ||
				(received && cost <= 0)
			){
				return true;
			}
		}
		return false;
	};
	
	// Returns true if this was a new addition
	this.handleTip = function(txid, amount, blockchain){
		if(this.transactions[blockchain].hasOwnProperty(txid)){return false;}
		this.transactions[blockchain][txid] = amount;
		this.save();
		return true;
	};
	
	
	
	
		
	// Events
	this.onPlayStart = function(){
		// Set last played for now
		this.playing = true;
		this.lastPlayed = Date.now();
		return this;
	};
	
	// Saves into localstorage
	this.save = function(){
		Player.ls.saveTrack(this);
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
	
	Song.BLOCKCHAIN_DOGE = 'DOGE';
	Song.BLOCKCHAIN_BTC = 'BTC';
	Song.BLOCKCHAIN_LTC = 'LTC';
	
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