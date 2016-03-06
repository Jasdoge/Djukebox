/*
	
	Handles the visuals

*/

var Jukebox = {};

(function(){
	"use strict";
	
	var J = Jukebox;
	J.subtitle = null;			// The GPL part at the bottom
	J.title = null;				// The now playing part
	J.infoBox = null;			// Little box that pops up when a song starts playing or when a tip is received
	J.songList = null;			// List of songs in the jukebox
	J.pineapple = null;			// Animated pineapple
	
	J.init = function(){
		// Set a default title
		J.subtitle = $("#subtitle");
		J.title = $("#title");
		J.title.progress = J.title.find("div.progress");
		J.title.content = J.title.find("div.content");
		
		J.infoBox = $("#infoBox");
		J.songList = $("#songList");
		J.pineapple = $("#pineapple");

		J.setTitle({title:"Loading", author:"..."});
		J.updateSongPositions();
	};

	
	// Handles the progress bar for song playing
	J.onPlayProgress = function(perc){
		//console.log(bounds);
		J.title.progress.width((perc*100)+'%');
	};
	
	// Song starts playing
	J.onPlayStart = function(){
		J.pineapple.toggleClass("animating", true);
	};
	// Song has finished
	J.onPlayEnd = function(){
		J.pineapple.toggleClass("animating", false);
	};
	
	// Updates the available songs and up next positions
	J.updateSongPositions = function(){ 
		var nr = 0;
		for(var i = 0; i<Song.playlist.length; i++){
			var container = J.songContainer(Song.playlist[i]);
			if(i<8 && Song.playlist[i].tips()>0 && !Song.playlist[i].playing){
				nr++;
				container.find("span.order:first").html(nr+".");
				$("#songList > div:nth-child(" + i + ")").before(container);
			}else{
				container.toggleClass("hidden", true);
			}
		}
	};
	
	
	// Returns a song DOM element, creates it if it doens't exist
	J.songContainer = function(song){
		var container = song.j_container;
		var is_tipped = (song.tips() > 0);
		
		// Create container if not exists
		if(song.j_container === null){
			container = $("<div class=\"song boxGeneric queued\"></div>");
			container._song = song;
			J.songList.append(container);
			song.j_container = container;
		}
		
		// Update colors and text
		container.html('<h2><span class="order"></span> '+song.title+'</h2><p>'+song.author+'</p>');
		container.toggleClass("hidden", song.playing || !is_tipped);
		
		
		return container;
	};
	
	

	// Sets the center box that flies in on an event
	J.setInfoBox = function(text){
		var box = J.infoBox.toggleClass("anim", false);
		box.html('<h2>'+text+'</h2>');
		setTimeout(function(){box.toggleClass("anim", true);}, 10); 
	};
	

	// Sets the currently playing song with a Player.song object	
	J.setTitle = function(song){
		J.title.content.html(
			'<h2>'+song.title+'</h2>'+
			'<h3>By '+song.author+'</h3>'
		);		
		
	};
	

})();


