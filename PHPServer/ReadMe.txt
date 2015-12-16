This folder contains the directory structure of the PHP Server.  


The Server specs used are as follows:
PHP 5.5
MySQL 5.5

Free server in use at this location: thetranisition.comeze.com
Paid server in use at this location: thetransitionbud.com


Installing PHP locally:

Download 2.2.19 for apache:
http://olex.openlogic.com/packages/apache/2.2.19#package_detail_tabs

Update the root directory of apache to point to the PHPServer/public_html directory.

Add the php handler as directed here:
http://php.net/manual/en/install.windows.apache2.php

Download 5.5 for php: 
http://php.net/releases/

If you get an error which states that PHP can't load certain dll's be sure that the extension directory is using the 8 character dos format in php.ini.


To Debug you can use NetBeans + xDebug:
http://www.xdebug.org/download.php

Be sure to use quotes around the filename when adding the xdebug extension to php.ini

Files to be modified with your server location and configurations:
public_html/.htaccess
public_html/core/common/Settings.php



Setting up Nationbuilder with a PHP server:
1) Be sure that the Settings are configured with your server location and the location of the nationbuilder server and the nationbuilder clientid and client secret key
2) In a browser navigate to {php server location}/api.php?setupnbclient=1
3) Copy the link displayed and navigate to it in a new tab
4) This should bring you to a nationbuilder page to authorize the app
5) Authorize the application
6) It should redirect back to the php server where it will display more information
7) Find the access token and add that to your settings
8) In a browser navigate to {php server location}/api.php?addwebhooks=1
9) This will display some information noting that the configuration was added
10) Verify in Nationbuilder that the server is authorized and the webhooks are displayed