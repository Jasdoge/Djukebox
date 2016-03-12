/*
	
	Handles the actual playing of music

*/

var Player = {};
(function(){
	"use strict";
	var P = Player;
	P.widget = null;		// Soundcloud widget
	P.playingID = 0;		// Currently playing song ID
	P.location = "JasCafé";
	P.blockio_doge = "";
	P.blockio_ltc = "";
	P.blockio_btc = "";
	P.coinslot = null;
	P.dogeValue = false;		// Nr of doge per bitcoin
	P.ltcValue = false;			// Nr of LTC per bitcoin
	
	
	// Timers
	P.interval_refreshTips = null;
	P.interval_refreshValues = null;
	
	// Init, called from the script that requires the player
	P.init = function(widget){
		// Soundcloud widget
		P.widget = widget;
		// Initialize localstorage
		P.ls.load();
		
		P.coinslot = new Audio();
		P.coinslot.setSound('/media/coin_in_slot.ogg');		
		
		
		P.widget.onLoad = function(){this.play();};
		P.widget.onFinish = P.loadNextSong;
		P.widget.bindOnProgress(Jukebox.onPlayProgress, 100);  
		P.widget.onPlay = function(){
			var song = P.getCurrentSong();
			if(song !== false){
				Jukebox.setInfoBox("Now playing: "+song.author+" - "+song.title);
			}
		};

		// Init jukebox canvas
		Jukebox.init();
		
		
		// Grab the current LTC/DOGE values
		
		clearInterval(P.interval_refreshValues);
		P.interval_refreshValues = setInterval(P.refreshValues, 10000);		// Refresh every 10 sec until we have prices
		
		P.refreshValues();
		
		
		
	};
	
	// We got the values so now we can start the actual jukebox
	P.onValuesFetched = function(){
		// Start refreshing tips
		clearInterval(P.interval_refreshValues);
		P.interval_refreshValues = setInterval(P.refreshValues, 3600000);	// Refresh the coin values once an hour
		clearInterval(P.interval_refreshTips);
		P.interval_refreshTips = setInterval(P.refreshTips, 10000);
		P.loadNextSong();
		P.refreshTips();	
	};
	
	
	
	
	// Goes to 3 sec before a song ends
	P.skipCurrentSongToEnd = function(){
		var current = P.getCurrentSong();
		if(current === false){return;}
		P.widget.seekTo(P.widget.container.duration-3);
	};
	
	// Refreshes the coin values
	P.refreshValues = function(){
		//https://www.cryptonator.com/api/ticker/ltc-btc
		var currencies = [
			"btc-ltc",
			"btc-doge"
		];
		
		for(var i = 0; i<currencies.length; i++){
			$.get("https://www.cryptonator.com/api/ticker/"+currencies[i]).done(P.onCoinValue);
		}
	};
	
	// Data is something like {"ticker":{"base":"BTC","target":"LTC","price":"125.58790840","volume":"","change":"-0.24700626"},"timestamp":1457798285,"success":true,"error":""}
	P.onCoinValue = function(data){
		if(!data.hasOwnProperty("success") || !data.success){return;}		// Failed request
		var ticker = data.ticker;
		if(ticker.price <= 0){return;}										// Invalid cost
		
		// Validates if all coins have been set up and we should start the thing
		var pre = P.coinsSetUp();
		
		if(ticker.target === "LTC"){P.ltcValue = +ticker.price;}
		else if(ticker.target === "DOGE"){P.dogeValue = +ticker.price;}
		console.log("Value of ", ticker.target, "is now ", +ticker.price, "per BTC");
		
		// The jukebox is ready, let's grab the transaction values
		if(!pre && P.coinsSetUp()){
			P.onValuesFetched();
		}		
	};
	// Returns TRUE if all coins have values bound to them
	P.coinsSetUp = function(){
		return (P.dogeValue !== false && P.ltcValue !== false);
	};
	
	
	// Handles a wallet update from BlockIO
	P.onRefresh = function(data, api_key){

		// Get the song blockchain so it can put the tip into the proper location
		var chain = Song.BLOCKCHAIN_DOGE;
		if(api_key === P.blockio_btc){chain = Song.BLOCKCHAIN_BTC;}
		else if(api_key === P.blockio_ltc){chain = Song.BLOCKCHAIN_LTC;}
		
		
		var numReceived = 0;
		for(var i = 0; i<data.txs.length; i++){
			var received = data.txs[i].amounts_received,
				txid = data.txs[i].txid,
				time = data.txs[i].time;
			
			for(var x = 0; x<received.length; x++){
				var rec = received[x];
				var song = P.getSongByAddress(rec.recipient);
				if(song !== false){
					if(time > song.lastPlayed/1000){

						if(song.handleTip(txid, +rec.amount, chain)){
							numReceived++;
						}
						
					}
				}
				else{
					console.log("Address not found: ", rec.recipient);
				}
			}
		}
		P.onTipsLoaded(numReceived, api_key);
	};

	
	// Returns an array of wallet addresses by using an API key to detect the blockchain
	P.getAddressesByApiKey = function(key){
		var out = [];
		
		var type = "wallet_dogecoin";
		if(key === P.blockio_btc){type = "wallet_bitcoin";}
		else if(key === P.blockio_ltc){type = "wallet_litecoin";}
		
		
		for(var i = 0; i<Song.playlist.length; i++){
			var add = Song.playlist[i][type];
			if(add.length >= 20){
				out.push(add);
			}
		}
		
		return out;
	};
	
	
	// Fetches recent transactions by api key and addresses. Calls P.onRefresh on completion
	P.refreshCoin = function(api_key, addresses){
		$.get("https://block.io/api/v2/get_transactions/?api_key="+api_key+"&type=received&addresses="+addresses.join(',')).done(function(data){
			P.onRefresh(data.data, api_key);
		});
	};
	
	// Refreshes tips for the three blockchains
	P.refreshTips = function(){
		var keys = [
			P.blockio_doge,
			P.blockio_ltc,
			P.blockio_btc
		];
		
		// This keeps the balance checking rolling every 2.5 sec
		var tout = function(key, addresses){
			setTimeout(function(){P.refreshCoin(key, addresses);}, 1+i*2500);
		};
		
		for(var i = 0; i<keys.length; i++){
			var key = keys[i];
			var addresses = P.getAddressesByApiKey(key);
			if(key === "" || !addresses.length){continue;}
			tout(key, addresses);
			//P.refreshCoin(key, addresses);
		}
		
	};
	
	// Takes an address and returns a song object
	P.getSongByAddress = function(address){
		for(var i = 0; i<Song.playlist.length; i++){
			var song = Song.playlist[i];
			
			if(
				song.wallet_dogecoin === address ||
				song.wallet_litecoin === address ||
				song.wallet_bitcoin === address
			){
				return song;
			}
		}
		return false;
	};
	
	// Returns a song object by id
	P.getSongByID = function(id){
		for(var i = 0; i<Song.playlist.length; i++){
			var song = Song.playlist[i];
			if(song.id === id){
				return song;
			}
		}
		return false;
	};
	
	// A song tips has loaded, but we should wait a while to give the other tracks a moment to load before starting
	// NumReceived is nr of tips received since last
	P.onTipsLoaded = function(numReceived, api_key){
		if(P.playingID === 0){
			P.loadNextSong();
		}
		else{
			var pre = Song.playlist.slice(0);		// Grab the pre value
			// Just sort up the playlist with this new data
			P.sort();
			P.rearrange(pre);						// Updates song positions
			
			
			// Alert tips received
			if(+numReceived > 0){
				// Todo: Allow other currency icons
				var icon = "doge.png";
				if(api_key === P.blockio_btc){icon = "btc.png";}
				else if(api_key === P.blockio_ltc){icon = "ltc.png";}
				
				Jukebox.setInfoBox("<img src=\"/media/"+icon+"\" class=\"tipReceived\" /> "+numReceived+" new tips received!");
			}
		}
	};
	
	// Clears teh playlist
	P.clearPlaylist = function(){
		for(var i = 0; i<Song.playlist.length; i++){
			Song.playlist[i].onPlayStart().onPlayFinish();
		}
		P.loadNextSong();
	};
	
	
	// Gets the currently playing song object
	P.getCurrentSong = function(){
		return P.getSongByID(P.playingID);
	};
	
	
	
	// Sorts the playlist
	P.sort = function(){
		Song.playlist.sort(function(a,b){
			// First sort on the song actually being tipped enough
			if(a.tippedEnough() && !b.tippedEnough()){return -1;}
			else if(!a.tippedEnough() && b.tippedEnough()){return 1;}
			
			// Then sort on the total amount of tips for that song
			if(a.totalTips() > b.totalTips()){return -1;}
			else if(a.totalTips() < b.totalTips()){return 1;}
			
			// Finally sort by name
			if(a.title < b.title){return -1;}
			else if(a.title > b.title){return 1;}
			
			return 0;
		});
	};
	
	
	
	// Usually run after a sort, rearranges the visual track list if it has changed
	P.rearrange = function(pre){
		if(pre.length != Song.playlist.length){return Jukebox.updateSongPositions();}
		for(var i = 0; i<pre.length; i++){
			if(pre[i] !== Song.playlist[i]){
				return Jukebox.updateSongPositions();
			}
		}
	};
	
	// Fetches the next song
	P.loadNextSong = function(){
		var playing = P.getCurrentSong();
		if(playing !== false){
			playing.onPlayFinish();
		}
		
		// Make sure we only need to call position update operations if the list has changed
		var pre = Song.playlist.slice(0);
		P.sort();
				
		var track = Song.playlist[0];
		
		if(!track.tippedEnough() && P.playingID){
			console.log("Queue has finished.");
			Jukebox.onPlayEnd();
			P.playingID = 0;
		}
		
		if(track.tippedEnough() > 0){
			if(P.playingID === 0){
				// Play coin slot sound or something
				console.log("Jukebox is starting");
				Jukebox.onPlayStart();
				P.coinslot.play(0.25);
			}
			
			P.playingID = track.id;
			

			P.widget.setSoundBySoundcloud(track.location);
			
			Jukebox.setTitle(track);
			track.onPlayStart();
		}
		else{
			Jukebox.setTitle({title:"Awaiting Input", author:"Pay one of the tracks to play it!"});
		}
		// Update visual
		P.rearrange(pre);
	};
	
	
	
	
	





// Localstorage object
	P.ls = {
		tracks:[]	// Contains track objects
	};
	// Track object
	P.ls.track = function(data){
		this.id = 0;
		this.lastPlayed = 0;
		this.transactions = {};
		
		this.__construct = function(data){
			for(var i in data){
				if(this.hasOwnProperty(i)){
					this[i] = data[i];
				}
			}
			return this;
		};
		return this.__construct(data);
	};
	P.ls.save = function(){
		localStorage.tracks = JSON.stringify(P.ls.tracks);
	};
	P.ls.load = function(){
		if(localStorage.hasOwnProperty("tracks")){
			var p = JSON.parse(localStorage.tracks);
			for(var i=0; i<p.length; i++){
				P.ls.tracks.push(new P.ls.track(p[i]));
			}
			for(i=0; i<Song.playlist.length; i++){
				// Get from localstorage
				Player.ls.loadTrack(Song.playlist[i]);
			}
		}
	};
	// Finds a track in localstorage
	P.ls.findTack = function(id){
		for(var i = 0; i<P.ls.tracks.length; i++){
			var tr = P.ls.tracks[i];
			if(tr.id === id){
				return tr;
			}
		}
		return false;
	};
	// Saves track data to localstorage
	P.ls.saveTrack = function(track){
		var tr = P.ls.findTack(track.id);
		if(tr === false){	// Insert new
			tr = new P.ls.track({id:track.id});
			P.ls.tracks.push(tr);
		}
		tr.lastPlayed = track.lastPlayed;
		tr.transactions = track.transactions;
		P.ls.save();
	};
	// Loads track data from localstorage
	P.ls.loadTrack = function(track){
		var tr = P.ls.findTack(track.id);
		if(tr === false){return;}
		track.lastPlayed = tr.lastPlayed;
		track.transactions = tr.transactions;
	};
	// Removes all localstorage data
	P.ls.clear = function(){
		console.log("Localstorage Cleared");
		P.ls.tracks = [];
		P.ls.save();
	};
	

})();