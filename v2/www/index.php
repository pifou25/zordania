<?php
/* redirection ici remplace le .htaccess de apache - pour lighttpd */

if ( (! empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') ||
     (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
     (! empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ) {
    define('PROTOCOL', 'https');
} else {
    define('PROTOCOL', 'http');
}

if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'www.zordania.com'){

	$url = PROTOCOL . '://zordania.com' . $_SERVER['REQUEST_URI'];
	header('Status: 301 Moved Permanently', false, 301);
	header("Location: $url");
	exit();
}

define("_INDEX_",true);

require("../index.php");
?>