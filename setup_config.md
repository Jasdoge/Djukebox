# Configuring your Djukebox

Here's a quick way of setting things up. There are obviously lots of different ways of doing this, but I'll start with the easiest way possible:

**config.php**

1. Open a terminal and enter: cd /var/www/Djukebox
2. Enter: sudo nano config.php
3. Find static $STORE_NAME = "Shibe Café"; and change it from Shibe Café to whatever you want to name the place your Djukebox is hosted at.
4. If you want to you can register an account at [block.io](https://block.io) and replace BLOCKIO_DOGE, BLOCKIO_BTC, BLOCKIO_LTC with your own API keys. This will likely improve stability of your Djukebox if it gets popular. You can feel free to use my own API keys (it's a throwaway account) but keep in mind if many people do, balance refreshes might fail.
5. $LIST_ADDRESS should be set to the IP the players can connect to when they scan the QR code. To figure out the IP  you can open up a new terminal and run: ifconfig
6. The IP should look something like 192.168.0.*
7. If you have a domain like mycafename.com you can also create a subdomain such as jukebox.mycafename.com - You have to set up port forwarding of port 80 to your PI in that case. Consult your network administrator or router manual on how to do this.
8. MYSQL_* settings can be changed if you didn't follow the previous guide and name all accounts and passwords to jasdoge.
9. Hit ctrl+x, Y and enter to save.

The rest can be done through the wonderful use of GUIs. Simply load up chromium on the raspberry pi and enter in the address bar: localhost<br />
This will take you to a song editor.

In the editor you should hit new song. Currently the Djukebox only handles soundcloud URLs. If you want some songs to test with, all [my songs](https://soundcloud.com/jasx) are free to stream. Or why not check out the [doge tracks soundcloud group](https://soundcloud.com/groups/doge-tracks) for much shibe tunes? Once you've picked a song, do the following:

1. Enter the Author and Title of the song.
2. (Optional) Enter a doge/btc/ltc value. You don't need to use all 3. Once you enter a value, you will see an approximate satoshi value on the right. If you want all 3 currencies to cost roughly the same by today's values you can shift click the input of the value you want. If you leave a value blank it will be set as "any" and be playable for pay-what-you-want.
3. Paste the soundcloud URL into the soundcloud URL box. It will turn green if the track is found and available for streaming!
4. Paste a doge and/or btc/ltc address for that particular song. You cannot reuse an address for multiple songs. You can leave the field blank if you don't want to show the pay button for that currency. So if you only want your Djukebox to accept dogecoin you can just fill out the Dogecoin Address field.
5. Click Done to save that track.
 

### But Mr Jasdoge, I do not have any dogecoin addresses :(

There are multiple ways to get some doge addresses! If you don't want to host a full dogecoin value I suggest using a [dogechain wallet](https://my.dogechain.info), it's what I've been using while testing.

As for bitcoin/litecoin addresses I don't really know. I suspect there are similar services, but you will just have to use ye oldde google for that.

## Starting the Djukebox

Now we have a PI with a lamp server. We have the Djukebox installed and we have added some songs. We have even made some fresh doge addresses just for our Djukebox. It's time to make some magic!

1. Open up chromium on your pi (or whatever computer you're hosting the Djukebox off of).
2. Enter the url: localhost/player
3. Hit F11 to set it to kiosk mode.
4. Scan the QR code and give it a try.

**Hopefully if you set everything up right, your Djukebox should now much much amaze.** However, if something is not wow, you should go to the [Issues page](https://github.com/Jasdoge/Djukebox/issues) and file a support request. 
