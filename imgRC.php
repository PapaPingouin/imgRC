<?php
/*
 * imgRC_class.php
 * 
 * Copyright 2014 PapaPingouin <papapingouin@imw.fr>
 * 
 * usage only via .htaccess :
 * RewriteRule		^(.*\.(jpg|png|JPG|PNG|jpeg|JPEG|gif|GIF|pdf\.jpg|pdf\.png))$	imgRC.php?uri=$1	[NC,L,QSA]
 * 
 */

	require_once('imgRC_class.php');
	
	imgRC::$cacheFolder = './CACHE/';
	imgRC::$salt = "6Nn@LrB.g:,Vy};R#@J}BX+23y#S[$*=&=;25HhU"; // do not change after generate urls
		
	imgRC::$OPTtrim = true;
	imgRC::$OPTq= 80;
	imgRC::$OPTbg='BLANC';
	imgRC::$OPTcrop= 0;
	imgRC::$OPTsizeauto= 0;
	imgRC::$OPTnocache= 0;
	imgRC::$OPTprogress= 0;
	imgRC::$OPTdureeCache= 2678400; //1mois
	imgRC::$OPTff= false;
	imgRC::$OPTgris= false ;
	
	
	imgRC::sendRequestImg();
	


?>

