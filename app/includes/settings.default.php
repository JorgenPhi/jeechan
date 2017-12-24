<?php
/* jeechan
 * https://github.com/JorgenPhi/jeechan
 * http://wakaba.c3.cx/shii/shiichan
 *
 * Settings (copy to settings.php)
 */

define('JEE_PRETTYURLS', false); // Use /read.php/boardname/1400999437/ instead of /read.php?b=boardname&t=1400999437 (requires URL rewriting)
define('JEE_SALT', "changeme"); // Enter some random data, and don't change this in the future (used for secure tripcodes)

define('JEE_PDODSN', "mysql:host=localhost;dbname=changeme;charset=utf8");
define('JEE_PDOUSER', "changeme");
define('JEE_PDOPASS', "changeme");
