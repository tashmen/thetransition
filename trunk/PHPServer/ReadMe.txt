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