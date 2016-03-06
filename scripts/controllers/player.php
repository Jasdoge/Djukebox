<script src="https://connect.soundcloud.com/sdk.js"></script>
<script src="/libraries/jquery.qrcode-0.12.0.min.js"></script>
<script src="/scripts/class Song.js?"></script>
<script src="/scripts/class Jukebox.js?"></script>
<script src="/scripts/class Player.js?"></script>
<script src="/scripts/class Audio.js?"></script>


<script>
$(function(){
	"use strict";
	Audio.init("0c5e06eef479dec10a30e3bb4bb027dd");
	var widget = new Audio();
	
	
	// Here we can configure some things
	Player.location = <?php echo json_encode(Config::$STORE_NAME); ?>;
		
	
	// Grab the songs
	Song.init(function(){
		// Initialize the player
		Player.init(widget);
	});
	
	
	// Bind debug buttons
	$("#clear").click(Player.ls.clear);
	$("#clearPlaylist").click(Player.clearPlaylist);
	$("#skipToEnd").click(Player.skipCurrentSongToEnd);
	$("#testTip").click(function(){
		
		Jukebox.setInfoBox("<img src=\"/media/doge.png\" class=\"tipReceived\" /> "+10+" new tips received!");
		
	});
	
});
</script>
<div id="content" class="noclick" style="overflow:hidden;">
    <div style="position:absolute; width:100%; top:50%; transform:translateY(-50%); text-align:center;">
        
    
        <div id="title" class="boxGeneric" style="width:auto; margin:30px 90px; overflow:visible;">
            
            <div class="pwrap">
                <div class="progress"></div>
            </div>
            <div class="content"></div>
            
            <img src="/media/pineapple.png" id="pineapple" />
        </div>
        <div id="songList"></div>
    </div>
   
    <div id="infoBox" class="boxGeneric"></div>
</div>
<div id="console">
<input type="button" value="Clear Localstorage" id="clear" />
<input type="button" value="Clear Playlist" id="clearPlaylist" />
<input type="button" value="Skip to End" id="skipToEnd" />
<input type="button" value="Test Tip" id="testTip" />
</div>
    

