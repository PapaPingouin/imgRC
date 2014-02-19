<?php
/*
 * imgRC_class.php
 * 
 * Copyright 2014 PapaPingouin <papapingouin@imw.fr>
 * 
 * 
 * 
 */
 
class imgRC
{
	// default values
	public static $cacheFolder = './CACHE/';
	public static $salt = "6Nn@LrB.g:,Vy};R#@J}BX+23y#S[$*=&=;25HhU"; // do not change after generate urls
		
	public static $OPTtrim = true;
	public static $OPTq= 80;
	public static $OPTbg='BLANC';
	public static $OPTcrop= 0;
	public static $OPTsizeauto= 0;
	public static $OPTnocache= 0;
	public static $OPTprogress= 0;
	public static $OPTdureeCache= 2678400; //1mois
	public static $OPTff= false;
	public static $OPTgris= false ;
	
	private static $type = 'img'; // type de la ressource à convertir
	
	public static function parseRequestOptions()
	{
		$opt = array();
		$opt['y'] = (isset($_REQUEST['y'] )) ? intval( $_REQUEST['y'] ) : 0;
		$opt['x'] = (isset($_REQUEST['x'] )) ? intval( $_REQUEST['x'] ) : 0;
		
		if( !empty( $_REQUEST['c'] ) && empty( $_REQUEST['option'] ) )
			$_REQUEST['option'] = self::uncryptOpt( $c );
			
		
		$o = (!empty($_REQUEST['option']) ) ? explode(',', $_REQUEST['option'] ) : array();
		// format des options : ?option=opt1,opt2,opt3 ...
		// options possible :
		//		xXXX : valeur en x (rempalce le x=)
		//		yYYY : valeur en y (rempalce le y=)
		// 		trim : supprime les bords sur les PDF ( défault)
		//		notrim : conserve les marges sur les pdf
		//		qXX : qualité pour les JPEG (de 0 à 100 ) défaut : 80
		//		bgRRVVBB : couleur de background (en hexa)
		//		crop : découpe le contenu de l'image à la taille demandée (sur le coté le plus petit) ... (ne fonctionne que si x et y sont définis)
		//		sizeauto : adapte les dimensions pour rentrer l'image dans la dimension demandée. réduit si besoin 1 des 2 cotés. (incompatible avec crop)
		//		nocache : désactive le cache
		//		cacheXXT : durée du cache pour cet élément : XX=quantité; T=unité (m,h,j minutes,heures,jours) (défault : 1j)
		//		ffxxx : fileformat (jpg,png,gif) => Force le format de sortie (indépendament de l'url)
		
		$opt['trim'] 		= self::$OPTtrim;
		$opt['q'] 			= self::$OPTq;
		$opt['bg'] 			= self::$OPTbg;
		$opt['crop'] 		= self::$OPTcrop;	
		$opt['sizeauto'] 	= self::$OPTsizeauto;
		$opt['progress'] 	= self::$OPTprogress;
		$opt['nocache'] 	= (!empty( $_REQUEST['nocache'] ) ) ? 1 : self::$OPTnocache;
		$opt['dureeCache'] 	= self::$OPTdureeCache;
		$opt['ff'] 			= self::$OPTff;
		$opt['gris'] 		= self::$OPTgris;
		$opt['type']		= 'img';
		$opt['resize']		= 0; // 1 si mode resize (sinon, resample)
		$opt['maxsize']		= 0; // défini si il s'agit de dimension max (permet de ne pas agrandir en activant )

		foreach( $o as $op )
		{
			if( $op == 'notrim' ) $opt['trim'] = false;
			if( $op == 'crop' ) $opt['crop'] = 1;
			if( $op == 'sizeauto' ) $opt['sizeauto'] = 1;
			if( $op == 'progress' ) $opt['progress'] = 1;
			if( $op == 'nocache' ) $opt['nocache'] = 1;
			if( $op == 'resize' ) $opt['resize'] = 1;
			if( $op == 'maxsize' ) $opt['maxsize'] = 1;
			if( preg_match( '#^q([0-9]{1,3})$#', $op, $out ) ) $opt['q'] = intval( $out[1] );
			if( preg_match( '#^bg([0-9A-Fa-f]{3,8})$#', $op, $out ) ) $opt['bg'] = $out[1] ;
			if( preg_match( '#^x([0-9]{1,4})$#', $op, $out ) ) $opt['x'] = intval( $out[1] );
			if( preg_match( '#^y([0-9]{1,4})$#', $op, $out ) ) $opt['y'] = intval( $out[1] );
			if( preg_match( '#^cache([0-9]+)([m,h,j])$#', $op, $out ) ) $opt['dureeCache'] = intval( $out[1] ) * $Tdurees[ $out[2] ] ;
			if( preg_match( '#^ff(jpg|png|gif)$#', $op, $out ) ) $opt['ff'] = $out[1] ;
			if( preg_match( '#^gris([0-9-]*)$#', $op, $out ) ) $opt['gris'] = !empty($out[1]) ? intval($out[1]) : 0; // par défaut 50% 
			
		} 
		
		return $opt;
	}
	
	public static function parseRequestUri( &$opt )
	{
		$Uri = ($_REQUEST['uri']);
	
		$urlcache = null;
		
		$dossierMD5 = self::getDossier( md5( $Uri ) );
		
		header('X-Nico-MD5: '.$dossierMD5 );
		
		$dossierCache = self::$cacheFolder.$dossierMD5;
		
		$urlfichier = trim( self::makeurl( $Uri."-$opt[x]"."-$opt[y]"."-".implode(',',$opt) ), ' \t\n\r.-_') ;
		$urlcache = $dossierCache.$urlfichier;
		
		self::testOrCreateDir( $dossierCache ); // dans le cas ou le dossier Cache n'existe pas
		
		return array('uri'=>$Uri,
					 'dossierCache'=>$dossierCache, 
					 'urlCache'=>$urlcache);
	}
	
	public static function getDossier( $md5 )
	{
		return $md5[0].'/'.$md5[1].'/'.$md5[2].'/'.$md5[3].'/';
	}
	
	public static function defineExtension( &$infoUrl, &$opt )
	{
		$infoUrl['type'] = 'img'; // par défaut img
		$ext = self::extension( $infoUrl['uri'] );
		if( $ext != 'jpg' && $ext !='jpeg' && $ext != 'png' && $ext != 'gif' )
			die( "Type de fichier incorect" );
		
		if( $opt['ff'] ) // obsolete, remplacé par la suite (gardé uniquement pour la compatibilité)
			$ext = $opt['ff']; // force le format de sortie
		
		if( preg_match( "#^(.*)\.(jpg|jpeg|gif|png|pdf)\.(jpg|gif|png)$#", $infoUrl['uri'], $out ) )
		{
			$infoUrl['uri'] = $out[1].'.'.$out[2]; // remplace l'url réelle du fichier (car la 2eme extension est bidon virtuelle
			$ext = $opt['ff'] = $out[3];
			$infoUrl['type'] = ( $out[2] == 'pdf' )  ? 'pdf' : 'img'; // défini si le type de génération est basée sur pdf ou img
				
		}
		if( preg_match( "#^(.*)/htmltoimg-(.*)\.(jpg|gif|png)$#", $infoUrl['uri'], $out ) )
		{
			    $infoUrl['uri'] = $out[2]; // ne conserve que l'url de la page à afficher
				$ext = $opt['ff'] = $out[3];
				$infoUrl['type'] = 'html';
		}
		self::$type = $infoUrl['type'];
		return $ext;
	}
	
	public static function killCache( $uri, $option )
	{
		$_REQUEST['option'] = $option; // place les options
		$_REQUEST['uri'] = $uri;
		
		$opt = self::parseRequestOptions();
		$infoUrl = self::parseRequestUri( $opt );
		
		if( file_exists( $infoUrl['urlCache'] ) )
			unlink( $infoUrl['urlCache'] ) ;
		
		return $infoUrl['urlCache'];
	}
	
	public static function verifFichier( $uri )
	{
		if( self::$type == 'html' )	return true; // dans le cas d'une url, on ne peut pas tester son existance en local
		
		return file_exists( $uri );
		
	}
	public static function getDates( &$infoUrl )
	{
		if( self::$type == 'html' )
			$infoUrl['dateModif'] = time()-86400; // une page html est considérée comme toujours changeante ... pour avoir une version à jour
		else
			$infoUrl['dateModif'] = filemtime( $infoUrl['uri'] );
		
		header('X-TEST-urlcache: '.$infoUrl['urlCache'] );
		
		if( !empty( $infoUrl['urlCache'] ) && file_exists( $infoUrl['urlCache'] ) && filesize( $infoUrl['urlCache'] )> 0 )
			$infoUrl['dateCache'] = filemtime( $infoUrl['urlCache'] );
		else
			$infoUrl['dateCache'] = 0;
	}
	
	public static function verifModifiedSince( &$infoUrl, &$opt )
	{
		if( strtotime(preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE'])) >= $infoUrl['dateModif'] && !$opt['nocache']) 
		{
			header('Status: 304 Not Modified', false, 304);
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', $infoUrl['dateModif']).' GMT');
			header('Expires: '.gmdate('D, d M Y H:i:s', ( $infoUrl['dateCache'] ? $infoUrl['dateCache'] : time() ) + $opt['dureeCache'] ).' GMT');
			die();
		} 
	}
	
	public static function checkCache( &$infoUrl, &$opt )
	{ // renvoi true si le fichier est en cache et valide, sinon, false;
		
		return ( !$opt['nocache'] && $infoUrl['dateCache'] > ( time()-$opt['dureeCache'] ) && $infoUrl['dateModif']< $infoUrl['dateCache']  );
	}
	
	public static function erreur404()
	{
		header('Status: 404 File Not Found', false, 404);
		var_dump( $_REQUEST['uri'] );
		die("Le fichier n'existe pas");
	}
	public static function sendHeader( &$infoUrl, &$opt, $ext )
	{
		header('Status: 200 OK', false, 200);
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $infoUrl['dateModif']) . ' GMT');
		header('X-NicRedim-Extension: '.$ext );
		header('X-NicCache-Date: '.date('D, d M Y H:i:s',$infoUrl['dateCache'] ) );
		header('Expires: '.gmdate('D, d M Y H:i:s', ( $infoUrl['dateCache'] ? $infoUrl['dateCache'] : time() ) + $opt['dureeCache'] ).' GMT');
		//$time = microtime(true) - $time_start;
		//header('X-NicCache-Generated-in: '.$time );
				
		self::headerDebug();
				
		switch( $ext )
		{
			case "jpeg" :
			case "jpg" :
				header('Content-type: image/jpeg'); break;
			case "gif" :
				header('Content-type: image/gif'); break;
			case "png" :
				header('Content-type: image/png'); break;
			default:
				die( "Type de fichier incorect" );
		}
		
	}
	
	public static function sendCache( &$infoUrl, &$opt, $ext)
	{
		//die( $infoUrl['urlCache'] );
		self::sendHeader( $infoUrl, $opt, $ext );
		readfile( $infoUrl['urlCache'] );
		die();
	}
	public static function sendSource( &$infoUrl, &$opt, $ext)
	{
		
		self::sendHeader( $infoUrl, $opt, $ext );
		readfile( $infoUrl['uri'] );
		die();
	}
	public static function genereCache( &$infoUrl, &$opt )
	{
		switch( $infoUrl['type'] )
		{
			case 'pdf' : self::generePDF(  $infoUrl, $opt ); break;
			case 'img' : self::genereImg(  $infoUrl, $opt ); break;
			case 'html': self::genereHTML( $infoUrl, $opt ); break;
			
		}
	}
	
	public static function generePDF( &$infoUrl, &$opt ) 
	{
		header('X-NicPDF : PDF miniature Générator' );
		//echo "$Fichier -> $urlcache";
		if( $opt['crop'] && $opt['x'] > 0 && $opt['y'] > 0 )
		{
			if( $opt['x'] <= $opt['y'] ) // redimensionne sur le coté demandé le plus petit, pour pouvoir ensuite faire le crop du coté le plus long
				$resize = $opt['x'].'x'; // x plus petit, c'est lui qu'on passe
			else
				$resize = $opt['y']; // y plus petit, c'est lui qu'on passe
			
			$resize .= " -gravity Center -crop $opt[x]x$opt[y]+0+0 -extent $opt[x]x$opt[y] ";
		}
		else
		{
			if( $opt['x'] > 0 && $opt['y'] > 0 )
				$resize = $opt['x']."x".$opt['y'];
			elseif( $opt['x'] > 0 && $opt['y'] == 0 )
				$resize = $opt['x'];
			elseif( $opt['x'] == 0 && $opt['y'] > 0 )
				$resize = "x$opt[y]";
			else
				$resize = "1024x1024";
		}
		//$resize="1024x1024";
		$optpdf = '';
		if( $opt['trim'] ) $optpdf .= ' -trim ';
			
			
		$temp = $infoUrl['dossierCache'].str_replace('.','',uniqid('pdftoimg-',true)).'-'.mt_rand(10000,10000000).".".$opt['ff'];
		if( $opt['ff'] =='jpg' )
			$convert = "/usr/bin/convert -density 150 $optpdf -resize $resize -quality $opt[q]% -format jpg '$infoUrl[uri]"."[0]' '$temp' 2>&1";
		if( $opt['ff'] =='png' )
			$convert = "/usr/bin/convert -density 150 $optpdf -resize $resize -format png '$infoUrl[uri]"."[0]' '$temp' 2>&1";
		//die( $convert );
		
		$out = array();
		//header('X-NicPDFcommande : '.$convert );
		exec( $convert , $out);
		foreach( $out as $k=>$v)
			header('X-NicPDFtoIMG-'.sprintf('%02d',$k).': '.$v );
		if( !file_exists( $temp ) )
			header('X-NicPDFtoIMG-Erreur : Fichier temp introuvable : '.$temp );
		@rename( $temp, $infoUrl['urlCache'] );
	}
	
	public static function genereHTML( &$infoUrl, &$opt ) 
	{
		header('X-NicoHTML : HTML miniature Générator' );
		
		$temp = $infoUrl['dossierCache'].str_replace('.','',uniqid('htmltoimg-',true)).'-'.mt_rand(10000,10000000).".".$opt['ff'];
		
		$convert = "/usr/bin/wkhtmltoimage '$infoUrl[uri]' '$temp' 2>&1";
		//die( $convert );
		
		$out = array();
		//header('X-NicoHTMLcommande : '.$convert );
		exec( $convert , $out);
		foreach( $out as $k=>$v)
			header('X-NicHTMLtoIMG-'.sprintf('%02d',$k).': '.$v );
		if( !file_exists( $temp ) )
			header('X-NicHTMLtoIMG-Erreur : Fichier temp introuvable : '.$temp );
		@rename( $temp, $infoUrl['urlCache'] );
		
		// redim l'image ...
		$infoUrl['uri'] = $infoUrl['urlCache'];
		self::genereImg( $infoUrl, $opt );
		
	}
	
	public static function genereImg( &$infoUrl, &$opt )
	{
		header('X-NicImg: Img Générator' );
		$time_start = microtime(true);
		
		require ("imgRC_gd.php" );

		$Image = new imgRC_gd( "" , $infoUrl['uri'] );
		
		if( $opt['ff'] )
			$Image->Format = $opt['ff']; // force le format final
		
		$Image->Progress = $opt['progress']; 
		
		$Image->MaxWidth  = 1024;
		$Image->MaxHeight = 1024;
		$Image->Compress = $opt['q'];
		if( $opt['bg'] != 'BLANC' )		
			$Image->FondCouleur = self::hex2RGB( $opt['bg'], true );
		$Image->Crop = ( $opt['x'] != 0 && $opt['y'] != 0 ) ? $opt['crop'] : 0; // ne fonctionne que si x et y sont définis
		
		if( $opt['x'] != 0 ) $Image->MaxWidth  = $opt['x'];
		if( $opt['y'] != 0 ) $Image->MaxHeight = $opt['y'];
		if( $opt['maxsize']) $Image->Agrandir = 0; // permet de ne pas agrandir
		
		$Image->ForcerHW = ( $opt['x'] != 0 && $opt['y'] != 0 && !$opt['maxsize'] ) ? 1-$opt['sizeauto'] : 0 ;
		
		if( $opt['gris'] !== false )
			$Image->Gris =  $opt['gris'] ;
		
		if( $opt['resize'] )
			$Image->Methode =  'Resize' ;
		
		$Image->GenereImage( $infoUrl['urlCache'] );
		$infoUrl['dateCache'] = time();
		//die('ok');
		$time = round(( microtime(true) - $time_start ) *1000 ,4) ;
		header('X-NicCache-Generated-in: '.$time.'ms' );
	}
	
	public static function sendRequestImg()
	{
		$opt = self::parseRequestOptions();
		$infoUrl = self::parseRequestUri( $opt );
		$ext = self::defineExtension( $infoUrl, $opt );
		if( !self::verifFichier( $infoUrl['uri'] ) ) self::erreur404();
		self::getDates( $infoUrl );
		if( !empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) ) self::verifModifiedSince( $infoUrl, $opt );
		
		if( $infoUrl['type']=='img' && $opt['x']==0 && $opt['y']==0 ) self::sendSource( $infoUrl, $opt, $ext );
		
		if( !self::checkCache( $infoUrl, $opt ) ) // si le fichier n'est pas caché, on le cache
			self::genereCache( $infoUrl, $opt );
	    
	    header( 'Cache-Control: public');
		
		self::sendCache( $infoUrl, $opt, $ext ); // on envoi le document en cache
		die( "erreur");
	}
	
	
	
	
	
	public static function headerDebug()
	{
		global $nbrRequetes, $time_start;
		
		if( !empty( $nbrRequetes ) )
		{
			header('X-DEBUG-nbrReq: '.$nbrRequetes );
			$time_end = microtime(true);
			$time = $time_end - $time_start;
			header('X-DEBUG-tps: '.$time );
			
		}
		header('X-DEBUG-mem: '.memory_get_usage() );
	}
	
	
	
	
	
	
	
	
	
	
	
		
		/**
	 * Convert a hexa decimal color code to its RGB equivalent
	 *
	 * @param string $hexStr (hexadecimal color value)
	 * @param boolean $returnAsString (if set true, returns the value separated by the separator character. Otherwise returns associative array)
	 * @param string $seperator (to separate RGB values. Applicable only if second parameter is true.)
	 * @return array or string (depending on second parameter. Returns False if invalid hex color value)
	 */                                                                                                
	public static function hex2RGB($hexStr, $returnAsString = false, $seperator = ',') {
		$hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
		$rgbArray = array();
		if (strlen($hexStr) == 8) { //If a proper hex code, convert using bitwise operation. No overhead... faster
			$colorVal = hexdec($hexStr);
			$rgbArray['red'] = 0xFF & ($colorVal >> 0x18);
			$rgbArray['green'] = 0xFF & ($colorVal >> 0x10);
			$rgbArray['blue'] = 0xFF & ($colorVal >> 0x8);
			$rgbArray['trans'] = 0xFF & $colorVal;
			if( $rgbArray['trans'] > 127 ) $rgbArray['trans'] = 127;
		}elseif (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
			$colorVal = hexdec($hexStr);
			$rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
			$rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
			$rgbArray['blue'] = 0xFF & $colorVal;
		} elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
			$rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
			$rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
			$rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
		} else {
			return false; //Invalid hex color code
		}
		//print_r( $rgbArray ); die();
		return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
	}


	public static function cryptOpt( $options )
	{
		return 'c='.self::STRcrypt( $options, self::$salt );
	}
	public static function uncryptOpt( $c )
	{
		return self::STRuncrypt( $c, self::$salt );
	}
	
	private static function STRcrypt( $string, $cle )
	{
		//return $string;
		$crypt = '';
		$n = 0;
		$cle = md5( $cle );
		while ( $n < strlen( $string ) )
		{
			for( $i=0;$i<strlen( $cle); $i++)
			{
				if( $n<strlen( $string ) )
					$crypt .= chr((ord($string[$n]) ^ (ord($cle[$i])+$i) )); 
				$n++;
			}
		}
		return base64_encode( $crypt );
	}
	private static function STRuncrypt( $string, $cle )
	{
		$string = base64_decode( $string );
		$n = 0;
		$cle = md5( $cle );
		$uncrypt = '';
		while ( $n < strlen( $string ) )
		{
			for( $i=0;$i<strlen( $cle); $i++)
			{
				if( $n<strlen( $string ) )
					$uncrypt .= chr((ord($string[$n]) ^ (ord($cle[$i])+$i) )); 
				$n++;
			}
		}
		return $uncrypt;
	}
	
	private static function testOrCreateDir( $url )
	{
		$dir = substr( $url, 0,strrpos($url,'/')) ;
		if( !is_dir( $dir ) )
			mkdir( $dir, 0777,true);	
						
	}
	
	private static function extension( $url, $regex=0 )
	{
		if( $regex == 0 )
			return strtolower(substr( $url, strrpos($url,'.')+1)) ;
		else
			if( preg_match( '#\.([a-z0-9_-]{1,8})$#i',$url, $out ) )
				return $out[1];
		return false;
	}
	
	private function makeurl( $chaine , $tolower=1 )
	{    // retourne une chaine sans accente, sans espace, sans caractères spéciaux et en minuscule pour les URL
		$tofind = utf8_decode("ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñµ°²³+#/\\,; _%&?'\":´…’`»«");
		$replac = 			  "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNnud23-----------------------------";
		$chaine = utf8_decode( $chaine );
		//$tofind = " _%&?'\":";
		//$replac = "-----------"; 
		$chaine = strtr($chaine,$tofind,$replac);
		 if( $tolower ) $chaine = strtolower( $chaine );    
		return(preg_replace("#(-+)#","-",$chaine));
	}

}

?>
