<script src="/scripts/class Song.js?"></script>

<div id="content" style="text-align:center;"><div id="centerMe">
    <div class="editBox title">
    	<h1><?php echo Config::$STORE_NAME; ?> Songs</h1>        
    </div>
    <div class="editBox">
    	<?php
			
		if(!Config::isLocal()){
			echo '<p>Welcome to the &ETH;jukebox!<br />From here you can control our jukebox by paying cryptocurrencies to the songs you would like to hear! Select a song to get started!</p>';	
		}
			
		?>
		<input type="text" id="search" placeholder="Search" />
        <?php
        if(Config::isLocal()){
			echo '
			<br />
			<input type="button" id="add" value="+ New Song" />
			';
		}
		?>
	</div>
    

</div></div>


<script>
$(function(){
	"use strict";

	
	// Songlist object
	// Grab from database
	var SongList = {};
	SongList.SONGS = 
	<?php
		echo json_encode(Song::getAllOut());
	?>
	;
	
	SongList.containsWord = function(str, words){
		str = str.toLowerCase();
		for(var i =0; i<words.length; i++){
			if(str.indexOf(words[i].toLowerCase()) === -1){return false;}
		}
		return true;
	};
	
	SongList.ini = function(){
		for(var i =0; i<SongList.SONGS.length; i++){
			var s = new Song(SongList.SONGS[i]);
			SongList.SONGS[i] = s;
			s._buildDOM();
		}
		
		// Generic binds
		$("#add").click(function(){
			var s = new Song();
			SongList.SONGS.push(s);
			s._buildDOM();
			s._dom.insertBefore("div.editBox.editSong:first");
		});
		
		$("#search").on('keyup change', function(event){
			var words =  $(this).val().trim().split(' ');
			var search = [];
			for(var i = 0; i<words.length; i++){
				var str = words[i].trim();
				if(str.length){
					search.push(str);
				}
			}
			
			if(!search.length){
				$("div.editBox.editSong").toggleClass("hidden", false);
				return;
			}
			
			for(i = 0; i<SongList.SONGS.length; i++){
				var s = SongList.SONGS[i];
				s._dom.toggleClass("hidden", !SongList.containsWord(s.title+" - "+s.author, search));
			}
		});
	};


	
	
	// Song prototype
	// Add custom editor functionality to song
	Song.prototype._dom = null;
	Song.prototype._listing_set = false;		// Whether the listing has been created
	
	// Updates DOM with the object's current properties
	Song.prototype._updateDOM = function(editor){
		var dom = this._dom;
		$("span.songTitle", dom).html(this.author+' - '+this.title);
		
		// Create editor
		if(editor){
			$("input[name=id]", dom).val(this.id);
			$("input[name=author]", dom).val(this.author);
			$("input[name=title]", dom).val(this.title);
			$("input[name=location]", dom).val(this.location);
			$("input[name=wallet_dogecoin]", dom).val(this.wallet_dogecoin);
			$("input[name=wallet_bitcoin]", dom).val(this.wallet_bitcoin);
			$("input[name=wallet_litecoin]", dom).val(this.wallet_litecoin);
		}
		
		// Create listing
		else{
			if(this._listing_set === true){return;}
			
			// Listing has been created
			this._listing_set = true;
			
			var html = '<span class="st">'+this.author+' - '+this.title+'</span><br /><br />';
			if(this.wallet_dogecoin.trim() !== ""){
				html+= '<a class="payButton doge" href="'+"dogecoin:"+this.wallet_dogecoin+"?amount=50&label="+encodeURIComponent("<?php echo Config::$STORE_NAME; ?> Jukebox :: "+this.author+" - "+this.title)+'"><img src="/media/doge.png" />Dogecoin</a><br />'; 
			}
			if(this.wallet_bitcoin.trim() !== ""){
				html+= '<a class="payButton btc" href="'+"bitcoin:"+this.wallet_bitcoin+"?amount=50&label="+encodeURIComponent("<?php echo Config::$STORE_NAME; ?> Jukebox :: "+this.author+" - "+this.title)+'"><img src="/media/btc.png" />Bitcoin</a><br />'; 
			}
			if(this.wallet_litecoin.trim() !== ""){
				html+= '<a class="payButton ltc" href="'+"litecoin:"+this.wallet_litecoin+"?amount=50&label="+encodeURIComponent("<?php echo Config::$STORE_NAME; ?> Jukebox :: "+this.author+" - "+this.title)+'"><img src="/media/ltc.png" />Litecoin</a><br />'; 
			}
			 
			$("div.editSong", dom).append(html);
			$("div.editSong a", dom).on("click", function(event){console.log("Stopping");event.stopImmediatePropagation();});
		}
	};
	
	Song.prototype._buildDOM = function(){
		var me = this;
		
		var html = '';
			html+= '<div class="editBox editSong <?php echo Config::isLocal() ? 'editor' : 'list' ?> closed">';
			html+= '<span class="songTitle"></span>';
<?php



if(Config::isLocal()){
echo <<<EOF
			// The values are bound with _updateDOM
			html+= '<form class="editSong">';
				html+= '<input type="text" placeholder="#ID" name="id" disabled size=1 /><br />';
				html+= '<input type="text" placeholder="Song Author" name="author"  /><br />';
				html+= '<input type="text" placeholder="Song Title" name="title"  /><br />';
				html+= 'Soundcloud URL:<br />';
				html+= '<input type="text" class="soundcloud" style="width:100%" placeholder="Soundcloud URL" name="location" /><br />';
				
				html+= 'Dogecoin Address:<br />';
				html+= '<input class="doge" type="text" style="width:100%" placeholder="Dogecoin Address" name="wallet_dogecoin"  /><br />';
				html+= 'Bitcoin Address:<br />';
				html+= '<input class="btc" type="text" style="width:100%" placeholder="Bitcoin Address" name="wallet_bitcoin" />';
				html+= 'Litecoin Address:<br />';
				html+= '<input class="ltc" type="text" style="width:100%" placeholder="Litecoin Address" name="wallet_litecoin" /><br />';
				html+= '<input type="submit" value="Done" /><input type="button" value="Cancel" class="cancelEdit" />';
			html+= '</form>';
EOF;
}
else{
echo <<<EOF
			// The values are bound with _updateDOM
			html+= '<div class="editSong"></div>';
EOF;
}

?>
		this._dom = $(html);
		this._updateDOM(true);									// Set values
		$("#centerMe").append(this._dom); 
		// Bind events
		$(this._dom).click(function(){
			// This is run on someone's phone
			if(!$(this).hasClass("editor")){
				// Close all the others
				$(this).siblings(".editSong").toggleClass("closed", true);
				me._updateDOM(false);
			}
			else{
				
			}
			if($(this).hasClass("closed") || $(this).hasClass("list")){
				$(this).toggleClass("closed");
			}
		});
		
		$("form", this._dom).submit(function(event){
			event.stopImmediatePropagation();
			var obj = $(this);
			var fdata = new FormData(this);
			new Ajax("SaveSong", [+$("input[name=id]", this).val()], fdata, function(){
				if(this.success && this.response.hasOwnProperty("SONG")){
					me.__construct(this.response.SONG);
					me._updateDOM(true);
					me._dom.toggleClass("closed", true);
				}
			});
			return false;
		});
		
		$("input.cancelEdit", this._dom).click(function(event){
			event.stopImmediatePropagation();
			me._dom.toggleClass("closed", true);
			me._updateDOM(true);
		});
		
	};
	
	
	
	
	
	
	// Initialize
	SongList.ini();
	

});

</script>
