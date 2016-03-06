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
	
	// Timers
	P.interval_refreshTips = null;
	
	P.init = function(widget){
		// Soundcloud widget
		P.widget = widget;
		// Initialize localstorage
		P.ls.load();
		
				
		
		
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
		
		// Start refreshing tips
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
	
	
	P.refreshTips = function(){
		var addresses = [];
		for(var i = 0; i<Song.playlist.length; i++){
			var add = Song.playlist[i].wallet_dogecoin;
			if(add.length == 34){
				addresses.push(add);
			}
		}
		if(!addresses.length){return;}
		$.get("https://block.io/api/v2/get_transactions/?api_key=ffd0-84c9-5154-91cd&type=received&addresses="+addresses.join(',')).done(function(data){
			
			var numReceived = 0;
			
			data = data.data;
			for(i = 0; i<data.txs.length; i++){
				var received = data.txs[i].amounts_received,
					txid = data.txs[i].txid,
					time = data.txs[i].time;
				
				for(var x = 0; x<received.length; x++){
					var rec = received[x];
					var song = P.getSongByAddress(rec.recipient);
					if(song !== false){
						if(time > song.lastPlayed/1000 && !song.transactions.hasOwnProperty(txid)){
							song.transactions[txid] = +rec.amount;
							numReceived++;
						}
					}
					else{
						console.log("Address not found: ", rec.recipient);
					}
				}
			}

			P.onTipsLoaded(numReceived);
		});
		
		
	};
	
	// Takes an address and returns a song object
	P.getSongByAddress = function(address){
		for(var i = 0; i<Song.playlist.length; i++){
			var song = Song.playlist[i];
			
			if(song.wallet_dogecoin === address){
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
	P.onTipsLoaded = function(numReceived){
		if(P.playingID === 0){
			P.loadNextSong();
		}
		else{
			// Just sort up the playlist with this new data
			P.sort();
			P.rearrange();
			
			// Alert tips received
			if(+numReceived > 0){
				// Todo: Allow other currency icons
				Jukebox.setInfoBox("<img src=\"/media/doge.png\" class=\"tipReceived\" /> "+numReceived+" new tips received!");
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
			if(a.tips() > b.tips()){return -1;}
			else if(a.tips() < b.tips()){return 1;}
			if(a.title < b.title){return -1;}
			else if(a.title > b.title){return 1;}
			
			return 0;
		});
	};
	
	
	
	// Usually run after a sort, rearranges the visual track list
	P.rearrange = function(){
		Jukebox.updateSongPositions();
	};
	
	// Fetches the next song
	P.loadNextSong = function(){
		var playing = P.getCurrentSong();
		if(playing !== false){
			playing.onPlayFinish();
		}
		
		P.sort();
				
		var track = Song.playlist[0];
		
		if(track.tips() <= 0){
			console.log("Queue has finished.");
			Jukebox.onPlayEnd();
			P.playingID = 0;
		}
		
		if(track.tips() > 0){
			if(P.playingID === 0){
				// Play coin slot sound or something
				console.log("Jukebox is starting");
				Jukebox.onPlayStart();
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
		P.rearrange();
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
		console.log("Saving ", P.ls.tracks);
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