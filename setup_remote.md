# Setting up on a remote machine

If for some reason you don't want to host everything on a single machine you can put a Djukebox installation on a remote webserver and just link to it. I don't recommend doing this due to security reasons.

This will change up the URL syntax a little bit as instead of using mydomain.com/player you'll have to use mydomain.com/jukeboxfolder/?q=player for the player.

1. Open up config.php
2. Find the $RUN_IN_FOLDER line, change it from false to true
3. Find the EDITOR_PASS line, change it from "doge" to whatever password you want to use.
4. Go to your domain like mydomain.com/jukeboxfolder/?LOGIN - Enter the username and pass you set.
5. To log out, go to your domain like mydomain.com/jukeboxfolder/?LOGOUT

