**This is the first release of the tutorial, you will probably notice missing or incorrect data. Please file a support request at the [issues page](https://github.com/Jasdoge/Djukebox/issues) or in [the following reddit thread](https://www.reddit.com/r/dogecoin/comments/4be8aa/%C3%B0jukebox_how_to_install_your_own/) and I'll try to answer your questions as good as I can!**

# :cake::dog2: Setting up your PI

The Djukebox is basically just a website. You can easily host it on any server, I just picked a raspberry PI 3 with raspbian jessie because it was cheap and easy. This first part of the guide will install LEMP (webserver) and clone the Djukebox source code. I'll start this guide as if you have just bought a PI 3, an adaptor and a memory card. We'll start off on your personal desktop computer.

You can obviously also use LAMP for this. If you already have/know how to install a webserver you can just clone the Djukebox repo into your webroot and get started on [the second guide](https://github.com/Jasdoge/Djukebox/blob/master/setup_config.md) immediately.

## :floppy_disk::dog2: Preparing the memory card and hardware.

1. I've downloaded the non-lite version of Raspbian Jessie from the raspberry pi's official site here: [https://www.raspberrypi.org/downloads/raspbian/](https://www.raspberrypi.org/downloads/raspbian/)
2. Follow the instructions from [here](https://www.raspberrypi.org/documentation/installation/installing-images/) on how to set up the SD card.
3. Add the SD card into the PI, connect the network, monitor, keyboard and mouse.
4. Connect the power supply and wait for the PI to boot.

If you're familiar with linux, you can disregard the monitor/keyboard/mouse if you can figure out the network ID for the PI and just SSH in with user: pi, password: raspberry.

## :computer::dog2: Configuring the pi

1. Open a terminal.
2. Enter: sudo raspi-config
3. Select expand filesystem. Upon completion: Reboot.
4. Open a new terminal and enter again: sudo raspi-config
5. Select change user password. Change your user password to something more secure. By default it's "raspberry"

**Keeping it up to date**
1. Enter a terminal and type: sudo apt-get update
2. After that's finished downloading, enter: sudo apt-get upgrade
3. This is going to take at least half an hour so go make yourself some pineapple wuffins.

**Optional: Change keyboard layout**

1. If you like me don't use an english layout keyboard you might want to install that. Enter raspi-config and select internationalisation options.
2. Select change keyboard layout.
3. I'm gonna stick with generic 105-key (intl) PC, so just hit enter.
4. Find the keyboard setup  you want to use, in my case Swedish.
5. Just hit enter until you're done.
6. Hit finish



## :mailbox_with_mail::dog2: Installing software

1. Open a terminal, enter: sudo apt-get install nginx
2. Once you're finished installing, enter: sudo apt-get install php5-fpm php5-mysql
3. Once that's done installing enter: sudo apt-get install mysql-server
4. You will have to pick a root password for mysql. Make sure to write that down.
5. Wget was not included on my install so also run: sudo-apt-get install wget

## :pencil::dog2: Configuring Software

**nginx**

1. Run: sudo nano /etc/php5/fpm/php.ini
2. Find the line: cgi.fix_pathinfo=1 (You can use ctrl+w to search)
3. Uncomment it by removing the ; in front, then change the =1 to =0 and hit ctrl+X, Y and enter to save and exit.
4. Run: sudo nano /etc/nginx/sites-available/default
5. Find the line "index index.html index.htm index.nginx-debian.html;" and add index.php to it like "index index.html index.php index.htm index.nginx-debian.html;"
6. Find the line "root /var/www/html" and change it to "root /var/www/Djukebox"
7. Uncomment the following lines by removing the # in front of them:
  * location ~ \.php$ {
  * include snippets/fastcgi-php.conf;
  * fastcgi_pass unix:/var/run/php5-fpm.sock;
  * }
8. Find location / { and in that section replace the try_files line with: try_files $uri $uri/ @rewrite;
9. After the closing bracket } of location / add the following block:
  <pre>location @rewrite {
          rewrite ^/(.*)$ /index.php?q=$1;
   }</pre> 
10. Hit ctrl+x, Y and enter to save and exit.

Here's roughly what my default file looks like:
<pre>
server {
        listen 80 default_server;
        listen [::]:80 default_server;
        root /var/www/Djukebox;
        index index.html index.php index.htm index.nginx-debian.html;
        server_name jukebox;
        location / {
                try_files $uri $uri/ @rewrite;
        }
        location @rewrite {
                rewrite ^/(.*)$ /index.php?q=$1;
        }
        location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/var/run/php5-fpm.sock;
        }
}
</pre>

**mysql**

1. Run: sudo mysql_install_db
2. Run: sudo mysql_secure_installation
3. Follow the instructions. Since you set the password when installing MYSQL you can hit N on the first option, but you should hit Y on the rest.
4. Run: sudo mysql -u root -p
5. Enter your password.
6. If you want to set this up as easy as possible without having to mess too much with the Djukebox config file, we can simply create a database, user and password all with the value of "jasdoge":
7. Run: CREATE DATABASE jasdoge;
8. Run: CREATE USER 'jasdoge'@'localhost' IDENTIFIED BY 'jasdoge';
9. Run: GRANT ALL PRIVILEGES ON jasdoge . * TO 'jasdoge'@'localhost';
10. Run: FLUSH_PRIVILEGES;
11. Run: exit

Sweet, now we got MYSQL set up. To make sure everything is in order we should run the following two commands:

1. sudo service php5-fpm restart
2. sudo service nginx restart

We still need chromium to display it though. [Please follow this guide](https://www.raspberrypi.org/forums/viewtopic.php?t=121195).

## :microphone::dog2: Setting up the Djukebox

1. Run: git clone https://github.com/Jasdoge/Djukebox
2. This will create a Djukebox folder in your home directory. We'll have to give www-data access to it though.
3. Run: sudo chown -R pi:www-data Djukebox
4. That will give www-data ownership of the directory. Now we just need to create a symlink:
5. Run: sudo ln -s /home/pi/Djukebox /var/www/

That should be it for basic installation. If you now open up chromium and enter the url: localhost, it should take you to the song editor. In [the next tutorial](https://github.com/Jasdoge/Djukebox/blob/master/setup_config.md) I will go over how to add some songs and setting up the visuals for the Djukebox!

