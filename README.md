RemoteWAKE
==========

The "We need you... WAKE UP!" remote web application. Locked down alarm clock for people you trust to wake you up remotely.

The setup is pretty self-explanatory. Install everything in the /website directory to your website.
Install everything in the /server directory to a Python enabled computer in your house with speakers. Make sure to port forward the port you choose (default port is 2040) to the computer/server you use.

Configs to set once installed...

- Set up any users in file_database/users.json - use md5 http://www.adamek.biz/md5-generator.php to make the passwords
- I'd recommend locking down the RemoteWake with a htaccess password http://www.htaccesstools.com/htpasswd-generator/ for extra security
- open /server/config.py and edit config settings there. Make sure to match them up within the website file (server_settings.php).
- Run RemoteWake.py when done (python RemoteWake.py)
