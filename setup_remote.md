# Setting up on a remote machine

If for some reason you don't want to host everything on a single machine you can put a Djukebox installation on a remote webserver and just link to it. I don't recommend doing this due to security reasons.

This will change up the URL syntax a little bit as instead of using mydomain.com/player you'll have to use mydomain.com/jukeboxfolder/?PASS=password&q=player for the player and mydomain.com/jukeboxfolder/?PASS=password for the editor.

1. Open up config.php
2. Find the $RUN_IN_FOLDER line, change it from false to true
3. Find the RUN_IN_FOLDER_PASS line, change it from "doge" to whatever password you want to use.

Make sure to clear your browsing history after editing the songs to prevent someone using your history to reveal the password.

