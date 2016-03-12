<script src="https://connect.soundcloud.com/sdk.js"></script>
<script src="/scripts/class Song.js?"></script>
<script src="/scripts/class Audio.js?"></script>
<script>

<?php

	
	function getCurrency($currency){
		$data = (array)json_decode(file_get_contents("https://www.cryptonator.com/api/ticker/btc-".$currency), true);
		if(isset($data['ticker']['price'])){
			return +$data['ticker']['price'];
		}
		return 0;
	}
	

?>


var Conversions = {
	doge:<?php echo getCurrency("doge"); ?>,
	ltc:<?php echo getCurrency("ltc"); ?>
};

$(function(){
	Audio.init("0c5e06eef479dec10a30e3bb4bb027dd");
});

jQuery.fn.scrollTo = function(elem, speed) { 
    $(this).animate({
        scrollTop:  $(this).scrollTop() - $(this).offset().top + $(elem).offset().top - $(this).height()/2 + $(elem).height()/2
    }, speed == undefined ? 500 : speed); 
    return this; 
};

</script>

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
			<p>This is where you edit your songs. This page is only available from localhost or if debug mode is available.</p>
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
		echo json_encode(Song::getAllOut(!Config::isLocal())); 
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
	
	Song.prototype._updateSatoshi = function(){
		var satconv = 0.00000001;

		$("span.cost_doge", this._dom).html(
			'('+
			Math.round(+$('input[name=cost_doge]', this._dom).val()/Conversions.doge/satconv)
			+' SAT)'
		);
		$("span.cost_btc", this._dom).html(
			'('+
			Math.round(+$('input[name=cost_btc]', this._dom).val()/satconv)
			+' SAT)'
		);
		$("span.cost_ltc", this._dom).html(
			'('+
			Math.round(+$('input[name=cost_ltc]', this._dom).val()/Conversions.ltc/satconv)
			+' SAT)'
		);
		
	};
	
	// Updates DOM with the object's current properties
	Song.prototype._updateDOM = function(editor){
		var dom = this._dom;
		$("span.songTitle", dom).html(this.author+' - '+this.title);
		
		$(dom).toggleClass("red", !this.proper);
		
		// Create editor
		if(editor){
			$("input[name=id]", dom).val(this.id);
			$("input[name=author]", dom).val(this.author);
			$("input[name=title]", dom).val(this.title);
			$("input[name=location]", dom).val(this.location);
			$("input[name=wallet_dogecoin]", dom).val(this.wallet_dogecoin);
			$("input[name=wallet_bitcoin]", dom).val(this.wallet_bitcoin);
			$("input[name=wallet_litecoin]", dom).val(this.wallet_litecoin);
			$("input[name=cost_btc]", dom).val(this.cost_btc);
			$("input[name=cost_ltc]", dom).val(this.cost_ltc);
			$("input[name=cost_doge]", dom).val(this.cost_doge);
			
			this._updateSatoshi();
		}
		
		// Create listing
		else{
			if(this._listing_set === true){return;}
			
			// Listing has been created
			this._listing_set = true;
			
			var html = '<span class="st">'+this.author+' - '+this.title+'</span><br /><br />';
			if(this.wallet_dogecoin.trim() !== ""){
				html+= '<a class="payButton doge" href="'+"dogecoin:"+this.wallet_dogecoin+"?amount="+(this.cost_doge > 0 ? this.cost_doge : 50)+"&label="+encodeURIComponent("<?php echo Config::$STORE_NAME; ?> Jukebox :: "+this.author+" - "+this.title)+'"><img src="/media/doge.png" />['+(this.cost_doge > 0 ? this.cost_doge : "ANY")+'] Dogecoin</a><br />'; 
			}
			if(this.wallet_bitcoin.trim() !== ""){
				html+= '<a class="payButton btc" href="'+"bitcoin:"+this.wallet_bitcoin+"?amount="+(this.cost_btc > 0 ? this.cost_btc : 0.001)+"&label="+encodeURIComponent("<?php echo Config::$STORE_NAME; ?> Jukebox :: "+this.author+" - "+this.title)+'"><img src="/media/btc.png" />['+(this.cost_btc > 0 ? this.cost_btc : "ANY")+'] Bitcoin</a><br />'; 
			}
			if(this.wallet_litecoin.trim() !== ""){
				html+= '<a class="payButton ltc" href="'+"litecoin:"+this.wallet_litecoin+"?amount="+(this.cost_ltc > 0 ? this.cost_ltc : 0.01)+"&label="+encodeURIComponent("<?php echo Config::$STORE_NAME; ?> Jukebox :: "+this.author+" - "+this.title)+'"><img src="/media/ltc.png" />['+(this.cost_ltc > 0 ? this.cost_ltc : "ANY")+'] Litecoin</a><br />'; 
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
				html+= '<input type="hidden" name="id" size=1 /><br />';
				html+= 'Author:<br /><input type="text" placeholder="Song Author" name="author"  /><br />';
				html+= 'Title:<br /><input type="text" placeholder="Song Title" name="title"  /><br />';
				html+= '<strong>Costs</strong><br /><i>Set to 0 for "pay what you want". Shift click to even out satoshis.</i><br />'+
					'DOGE <input type="number" class="dogecoin cost" step=1 min=0 placeholder="Cost (Doge)" name="cost_doge" /><span class="cost_doge"></span><br />'+
					'BTC &nbsp;<input type="number" class="bitcoin cost" step=0.00001 min=0 placeholder="Cost (Bitcoin)" name="cost_btc" /><span class="cost_btc"></span><br />'+
					'LTC &nbsp;<input type="number" class="litecoin cost" step=0.0001 min=0 placeholder="Cost (Litecoin)" name="cost_ltc" /><span class="cost_ltc"></span><br />';
				
				
				html+= 'Soundcloud URL:<br />';
				html+= '<input type="text" class="soundcloud" style="width:100%" placeholder="Soundcloud URL" name="location" /><br />';
				
				html+= 'Dogecoin Address:<br />';
				html+= '<input class="address doge" type="text" style="width:100%" placeholder="Dogecoin Address" name="wallet_dogecoin"  /><br />';
				html+= 'Bitcoin Address:<br />';
				html+= '<input class="address btc" type="text" style="width:100%" placeholder="Bitcoin Address" name="wallet_bitcoin" />';
				html+= 'Litecoin Address:<br />';
				html+= '<input class="address ltc" type="text" style="width:100%" placeholder="Litecoin Address" name="wallet_litecoin" /><br />';
				
				html+= '<input type="submit" value="Done" /><input type="button" value="Cancel" class="cancelEdit" /><input type="button" class="delete" value="Delete" />';
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
		
		// Event binding to handle open and closing
		$(this._dom).click(function(){
			// This is run on someone's phone
			if(!$(this).hasClass("editor")){
				// Close all the others
				$(this).siblings(".editSong").toggleClass("closed", true);
				me._updateDOM(false);
				setTimeout(function(){
					$("#content").scrollTo(me._dom);
				}, 10);
			} 
			else{
				
			}
			if($(this).hasClass("closed") || $(this).hasClass("list")){
				$(this).toggleClass("closed");
			}
		});
		
		
		
		// Bind costs
		$("input.bitcoin, input.litecoin, input.dogecoin", this._dom)
			.on('keyup change', function(){
				me._updateSatoshi();
			})
			.on('click', function(event){
				if(!event.shiftKey){return;}
				
				var val = +$(this).val();
				// Grab our cost in bitcoins
				var btcCost = val;
				if($(this).hasClass("dogecoin")){btcCost/=Conversions.doge;}
				else if($(this).hasClass("litecoin")){btcCost/=Conversions.ltc;}
				
				// Now let's convert these things
				$("input.bitcoin", me._dom).val(Math.ceil(btcCost*100000)/100000);
				$("input.dogecoin", me._dom).val(Math.ceil(btcCost*Conversions.doge));
				$("input.litecoin", me._dom).val(Math.ceil(btcCost*Conversions.ltc*10000)/10000);
				me._updateSatoshi();
			});
		
		$("input.address", this._dom)
			.on('keyup change', function(event){
				$(this).toggleClass("red green", false);
				var address = $(this).val();
				if(address == "")return;
				if(/^[a-z0-9]+$/i.test(address)){
					$(this).toggleClass("green", true);
				}else{
					$(this).toggleClass("red", true);
				}
			});
		
		
		$("input.delete", this._dom).click(function(){
			if(!confirm("Really delete this?")){return false;}
			
			if(me.id){
				console.log("Deleting");
				new Ajax("DeleteSong", [me.id], null, function(){
					if(this.success){
						Tools.addErrors("Song deleted.", true);
					}
				});
			}
			$(me._dom).remove();
		});
		
		// Event binding to handle soundcloud validation
		$("input.soundcloud", this._dom).change(function(event){
			var audio = new Audio,
				elem = this;
			
			$(this).toggleClass("yellow", true).toggleClass("red green", false);

			audio.getSoundBySoundcloud($(this).val(), function(sound){
				$(elem).toggleClass("yellow", false);
				if(sound === null){
					Tools.addErrors("Error: Soundcloud API token invalid!", true);
					$(elem).toggleClass("red", true).focus();
					return;
				}
				if(sound.hasOwnProperty("errors")){
					var errors = [],
						i = 0;
					for(i=0; i<sound.errors.length; i++){errors.push(sound.errors[i].error_message);}
					Tools.addErrors(errors.join('<br />'), true);
					$(elem).toggleClass("red", true).focus();
					return;
				}
				$(elem).toggleClass("green", true)
			});
		});
		
		
		
		// Event binder to handle form submit
		$("form", this._dom).submit(function(event){
			event.stopImmediatePropagation();
			
			var red = $("input.red:first", this._dom);
			if(red.length){
				red.focus();
				Tools.addErrors("Please correct the red fields before saving.");
				return false;
			}
			
			
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
