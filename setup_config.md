#h1 Configuring your Djukebox

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
9. Hit ctrl+x and enter to save.

The rest can be done through the wonderful use of GUIs. Simply load up chromium on the raspberry pi and enter in the address bar: localhost<br />
This will take you to a song editor.
