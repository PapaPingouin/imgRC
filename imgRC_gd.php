<?php
/*
 * imgRC_gd.php
 * 
 * Copyright 2014 PapaPingouin <papapingouin@imw.fr>
 * 
 * 
 * 
 */
 
class imgRC_gd
{
	var $UrlModule  				 = "MRI.php"; // Url du fichier MRI.php (en g�n�ral en relatif donc juste MRI.php
    var $FichierSource       = "";
    var $MaxHeight           = 400;
    var $MaxWidth            = 400;
    var $Height              = 400;
    var $Width               = 400;
    var $ForcerHW            = 1;              //ForcerHW permet de Forcer la dimension de sortie de l'image aux dimensions Max mm si le contenu n'est pas aussi grand
    var $Agrandir			 = 1;						// 0 pour ne pas agrandir une image plus petite que demand�e ( Si ForcerHW=1 alors ce param�tre est inefficace puisque la sortie est forc�e)
    var $ConserverProportion = 1;
    var $Crop                = 0;               // Uniquement valable si ForcerHW=1 et ConserverProportion=1
    var $CropRatio           = 0;               // Permet de recadrer l'image source au rtio souhait� (0 => pas d'effet)
    var $Format              = "auto";          //; Format = jpg, gif, png, auto
    var $Progress            = 0;          //; Définit le jpeg progressif ou non (par défaut 0)
    var $Compress            = 85;            //; Compress, dans le cas du JPEG uniquement = taux de compression (0-100) => 100=meilleure qualit�, mais gros fichiers
    var $TrueColor           = 1;
    var $ImgRemplacement     = "";      // Image de remplacement pour le cas ou le ficheir source n'existe pas !
    var $Methode             = "Resample";
    var $CompressUrl         = 0;     // compression de l'URL de l'image avec ZLib2

    var $FondCouleur         = "BLANC"; // -1 pour transparent
    var $FondImage           = "";

    var $Gris  	 	         = false; // pour griser l'image ( de -10 à 10 ) default : 0 (false = pas de grisé)

    var $TatouageImage       = "";
    var $TatouagePosX        = 0;
    var $TatouagePosY        = 0;

    var $TexteTexte          = "";
    var $TexteCouleur        = "0,0,0";
    var $TexteFond           = "255,255,255";
    var $Bordure             = 2;
    var $TextePolice         = "";
    var $TexteSize           = 10;
    var $TextePosX           = 0;
    var $TextePosY           = 0;

    var $CacheActive         = 0;
    var $CacheDossier        = "";
    var $CacheExpire         = 0;

    var $Eval                = array();

    var $Historique          = array();

    function __construct( $FichierIni = "", $Image = "" )
    {
		
        if ( $FichierIni != "" )
        {
            $Ini = parse_ini_file($FichierIni,TRUE);
            $this->MaxHeight            = isset( $Ini['General']['MaxHeight'])              ? $Ini['General']['MaxHeight']              : 400;
            $this->MaxWidth             = isset( $Ini['General']['MaxWidth'])               ? $Ini['General']['MaxWidth']               : 400;
            $this->ForcerHW             = isset( $Ini['General']['ForcerHW'])               ? $Ini['General']['ForcerHW']               : 1;
            $this->Agrandir             = isset( $Ini['General']['Agrandir'])               ? $Ini['General']['Agrandir']               : 1;
            $this->ConserverProportion  = isset( $Ini['General']['ConserverProportion'])    ? $Ini['General']['ConserverProportion']    : 1;
            $this->Crop                 = isset( $Ini['General']['Crop'])                   ? $Ini['General']['Crop']                   : 0;
            $this->CropRatio            = isset( $Ini['General']['CropRatio'])              ? $Ini['General']['CropRatio']              : 0;
            $this->Format               = isset( $Ini['General']['Format'])                 ? $Ini['General']['Format']                 : "jpg";
            $this->Compress             = isset( $Ini['General']['Compress'])               ? $Ini['General']['Compress']               : 100;
            $this->TrueColor            = isset( $Ini['General']['TrueColor'])              ? $Ini['General']['TrueColor']              : 1;
            $this->ImgRemplacement      = isset( $Ini['General']['ImgRemplacement'])        ? $Ini['General']['ImgRemplacement']        : "";
            $this->Methode              = isset( $Ini['General']['Methode'])                ? $Ini['General']['Methode']                : "Resize";
            $this->CompressUrl          = isset( $Ini['General']['CompressUrl'])            ? $Ini['General']['CompressUrl']            : 0;
            
            $this->FondCouleur          = isset( $Ini['Fond']['Couleur'])                   ? $Ini['Fond']['Couleur']                   : "BLANC";
            $this->FondImage            = isset( $Ini['Fond']['Image'])                     ? $Ini['Fond']['Image']                     : "";
            
            $this->TatouageImage        = isset( $Ini['Tatouage']['Image'])                 ? $Ini['Tatouage']['Image']                 : "";
            $this->TatouagePosX         = isset( $Ini['Tatouage']['PosX'])                  ? $Ini['Tatouage']['PosX']                  : 0;
            $this->TatouagePosY         = isset( $Ini['Tatouage']['PosY'])                  ? $Ini['Tatouage']['PosY']                  : 0;

            $this->TexteTexte           = isset( $Ini['Texte']['Texte'])                    ? $Ini['Texte']['Texte']                    : "";
            $this->TexteCouleur         = isset( $Ini['Texte']['Couleur'])                  ? $Ini['Texte']['Couleur']                  : "0,0,0";
            $this->TexteFond            = isset( $Ini['Texte']['Fond'])                     ? $Ini['Texte']['Fond']                     : "255,255,255";
            $this->TextePolice          = isset( $Ini['Texte']['Police'])                   ? $Ini['Texte']['Police']                   : "";
            $this->TexteSize            = isset( $Ini['Texte']['Size'])                     ? $Ini['Texte']['Size']                     : 10;
            $this->TexteBordure         = isset( $Ini['Texte']['Bordure'])                  ? $Ini['Texte']['Bordure']                  : 2;
            $this->TextePosX            = isset( $Ini['Texte']['PosX'])                     ? $Ini['Texte']['PosX']                     : 0;
            $this->TextePosY            = isset( $Ini['Texte']['PosY'])                     ? $Ini['Texte']['PosY']                     : 0;

            $this->CacheActive          = isset( $Ini['Cache']['Activer'])                  ? $Ini['Cache']['Activer']                  : 0;
            $this->CacheDossier         = isset( $Ini['Cache']['Dossier'])                  ? $Ini['Cache']['Dossier']                  : "";
            $this->CacheExpire          = isset( $Ini['Cache']['Expire'])                   ? $Ini['Cache']['Expire']                   : 0;

            if (  isset( $Ini['Eval'] ) &&  is_array($Ini['Eval'])   )
//                foreach( $Ini['Eval'] as $ligne )
                    $this->Eval = $Ini['Eval'];                                 // Copie toutes les lignes � "�valuer"

            //print_r( $this->Eval );
            $this->Height               = $this->MaxHeight;
            $this->Width                = $this->MaxWidth;
        }    
        if ( $Image != "" )
        {
            //$Image = convert_cyr_string ( $Image,"a","i");
            $this->FichierSource = $Image;

        }
     
    }

    function InitImageSource( $Image )
    {
        $FichierSource = $Image;
    }

    function AfficheImage($Balise="",$AffSize=0,$H = 0, $L = 0,$AT=0 )
    {     // $AT pour AfficheTemps permet d'afficher le temps en ms qu'a mis le module � redimensionner la photo
            // $H et $L ne sont pas utilis�s ; les dimensions de sorties sont fix�es par le fichier ini

        if ( $Balise == "" )
            $Balise = "alt='Module Retouche Image'";

        if ( $AffSize == 1 )
        {
            $Taille = $this->CalculeTailleImageDest();
            $Size="style='width:".$Taille[0]."px; height:".$Taille[1]."px;' ";
        }
        else
            $Size="";

        if ( $this->CompressUrl == 1 )
            $data = base64_encode(bzcompress(serialize( $this )));
        else
            $data = base64_encode(serialize( $this ));
            

        $Fichier = $this->CacheDossier . "/" . md5($data) .".". $this->Format ;
        $Genere = 1;
        if ( $this->CacheActive == 2 )       //---------------------------------------- Gestion de la cache de niveau 2
        {
            //echo "+1$Fichier : $data -";
            if ( is_file( $Fichier ) ) //Si le fichier existe en chache !
            {
                //echo "+2";
                if ( $this->CacheExpire != 0 )       // V�rifie l'expiration du fichier
                {
                    //echo "+3";
                    $Temps = time() - filemtime( $Fichier );
                    if ( $Temps > $this->CacheExpire )
                    {
                        //echo "+4";
                        // Le fichier a expir�,on le supprime,  on le reg�n�re et on le renvoi au navigateur
                        unlink( $Fichier );
                        $Genere = 1;
                    }
                    else
                        $Genere = 0;
                }
                else
                    $Genere = 0;

            }
            else
                $Genere = 1;    // Le ficheir n'existe pas !!!      //------------------ fin de la cache de niveau 2
        }

				if ( $Balise == "no" ) // On ne donne QUE l'url de l'image
				{
				  if ( $Genere == 1 )
	            return $this->UrlModule."?GENERE=1&amp;H=$H&amp;L=$L&amp;AT=$AT&amp;data=$data";     // On reg�n�re
	        else
	            return "$Fichier";     // Cache de niveau 2 ! on affiche l'url de l'image en cache !!!
				}
				else // On g�n�re la balise complete
				{
	        if ( $Genere == 1 )
	            return "<img src='".$this->UrlModule."?GENERE=1&amp;H=$H&amp;L=$L&amp;AT=$AT&amp;data=$data' $Balise $Size/>";     // On reg�n�re
	        else
	            return "<img src='$Fichier' $Balise $Size/>";     // Cache de niveau 2 ! on affiche l'url de l'image en cache !!!
				}
    }

    function Redim( $Sens = "Auto",$H = 0, $L = 0 )
    {     // $Cote = cot� � redimensionner ( Auto, Haut, Larg ) Auto -> redimensionne pour que l'image enti�re tienne dans la destination
            // $H et $L : Hauteur Largeur de sortie ! Limit�es aux dimensiosn de l'image d�finitive !!! MaxHeight et MaxWidth
                
            

    }
    
    function ExecuteHisto()
    {
        foreach ( $this->Historique as $Histo )
            $Histo = $Histo;
            
        $this->Redim();
    }
    
    function CalculeTailleImageDest( $im=""  )
    {     //Retourne un table 0=>width 1=>height
    
        if ( is_file( $this->FichierSource) )
        {  
            if ( $im == "" )
            {
                $Infos = getimagesize( $this->FichierSource );
            }
            else
            {
                $Infos=array();
                $Infos[0] = imagesx( $im );
                $Infos[1] = imagesy( $im );
            }
        
            if ( $this->ForcerHW == 1 )       // On force les dimensions finales aux dimensions demand�e
            {
                $Width  = $this->MaxWidth;
                $Height = $this->MaxHeight;
            }
            else
            {   
            	if ( $this->Agrandir==0 && $Infos[0]<$this->MaxWidth && $Infos[1]<$this->MaxHeight ) // Pas besoin de redimensionner
            	{
            		$Width  = $Infos[0];
            		$Height = $Infos[1];
            	}
            	else // L'image est trop grande ou on doit l'agrandir
            	{
            	    // On redimensionne la sortie sur le cot� le plus grand proportionnellement
                $FacteurX = $Infos[0] / $this->MaxWidth;
                $FacteurY = $Infos[1] / $this->MaxHeight;

                if ( $FacteurX > $FacteurY )    // L'image doit �tre r�duite sur la largeur
                {
                    $Width  = $this->MaxWidth;
                    $Height = $Infos[1] / $FacteurX;
                }
                else    // Ici, on r�duit par rapport � la hauter
                {
                    $Height = $this->MaxHeight;
                    $Width  = $Infos[0] / $FacteurY;
                }
              }
            }
            return array(0=>$Width, 1=>$Height);
        }
        else
            return array(0=>$this->MaxWidth, 1=>$this->MaxHeight);    
    }
    
    function CropRatio( $im, $RatioHW = 1 )
    {           // $RatioHW = ratio Height/Width souhait� ! ($RatioHW=1 => image carr�e )
                // L'image est crop�e sur son cot� le plus petit pour respecter le ratio impos�
        $Width = imagesx( $im );
        $Height = imagesy( $im );
        $FacteurActuel = $Height/$Width;
        if ( $FacteurActuel > $RatioHW )
        {
            $FinalWidth  = $Width;
            $FinalHeight = floor($Width * $RatioHW);
            $X1 = 0;
            $Y1 = floor(($Height - $FinalHeight)/2);
        }
        else
        {
            $FinalHeight = $Height;
            $FinalWidth  = floor($Height / $RatioHW);
            $Y1 = 0;
            $X1 = floor(($Width - $FinalWidth)/2);
        }
        //echo "-> $this->CropRatio - $FacteurActuel - $FinalHeight - $FinalWidth - $X1 - $Y1 <-";
        $im2 = ImageCreateTrueColor( $FinalWidth, $FinalHeight );
        imagecopy ( $im2, $im, 0, 0, $X1, $Y1, $FinalWidth, $FinalHeight );
        $textcolor = imagecolorallocate($im, 0, 0, 255);
        ImageString( $im, 5,20,20,"TEST", $textcolor );
        return $im2;


    }

    function GenereImage($Fichier ="")
    {     // $Fichier : Nom du fichier si enregistrement !!!!
      
        $TimeDebut = microtime( true );    // Calcul temps �P Point de d�part



        if ( is_file( $this->FichierSource) )
            $ImgSrc = $this->ImageCreateFromFile( $this->FichierSource );    // Ouvre le fichier Source
        else
        {
            //echo "***".$this->ImgRemplacement."***";
            if ( $this->ImgRemplacement != "" )
                $ImgSrc = $this->ImageCreateFromFile( $this->ImgRemplacement );       // image de remplacement
            else
            {
                $ImgSrc = imagecreate ( $this->MaxWidth, $this->MaxHeight);
                $C = explode(",", $this->FondCouleur );
                //$CouleurFond = imagecolorallocate($ImgSrc,$C[0],$C[1],$C[2]);
                //print_r( $C );die();
                $CouleurFond = imagecolorallocatealpha($ImgSrc,$C[0],$C[1],$C[2],(isset($C[3])?$C[3]:0) );
                ImageFill($ImgSrc,1,1,$CouleurFond);
                $coul = imagecolorallocate( $ImgSrc,255,0,0);
                imagestring( $ImgSrc,1,0,0, "Impossible ouvrir fichier source", $coul );
                //echo "Erreur, impossible d'ouvrir $this->FichierSource";
            }
        }
            
        
        if ( !$ImgSrc )
            die( "ERREUR Image source $this->FichierSource<br />" );


        if ( ( isset( $this->Eval  ) && is_array($this->Eval) ) )
            $this->imgEval( $ImgSrc , 0, 0,"A" );

        if ( $this->CropRatio != 0 )
        {
            $ImgSrc = $this->CropRatio( $ImgSrc, $this->CropRatio );

        }

        $Width=0;
        $Height=0;


        $Taille = $this->CalculeTailleImageDest( $ImgSrc );
        $Width = $Taille[0];
        $Height = $Taille[1];
        if ( $Width < 1 ) $Width = 1;
        if ( $Height < 1 ) $Height = 1;

        // Initialisation image de sortie
        if ( $this->TrueColor == 1 )
            $ImgDest = imagecreatetruecolor ( $Width, $Height);
        else
            $ImgDest = imagecreate ( $Width, $Height);

        

        
        $this->Height = $Height;
        $this->Width  = $Width;

        //echo "** $this->Height - $this->Width **";
		//header("X-NicoDebug: $this->FondCouleur");
		
        if ( empty($this->FondImage) )      // Pas fond, on rempli avec une couleur unie
        {
            if ( !empty($this->FondCouleur) )
            {
            	if( $this->FondCouleur == '-1' ) // Fond transparent
            	{
            		//$imtr = imagecreatefromstring( base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9kEEhABMaKWTJgAAAAIdEVYdENvbW1lbnQA9syWvwAAAA1JREFUCNdjYGBgYAAAAAUAAV7zKjoAAAAASUVORK5CYII=' ) );
            		
            		imagealphablending($ImgDest, false); // Obligé 
            		imagesavealpha($ImgDest, true);
					$trans_colour = imagecolorallocatealpha($ImgDest, 0, 255, 0, 127);
					//$trans_colour = imagecolorallocate($ImgDest, 255, 255, 255);
					imagefill($ImgDest, 1, 1, $trans_colour);
					//imagecolortransparent ( $ImgDest, $trans_colour );
					//imagefilledrectangle  ( $ImgDest  , 0  , 0  , $Width  , $Height  , $trans_colour  );
					
            		
				}
            	else
            	{
            		// FondCouleur = BLANC dans le cas ou aucune initialisation humaine (pour différencier du -1 automatique)
					$C = ( $this->FondCouleur=='BLANC' || $this->FondCouleur=='-1' ) ? array(255,255,255) : explode(",", $this->FondCouleur );
					if( count( $C ) > 2 ) // on a au moins 3 couleurs (rvb)
					{
						if( count( $C ) > 3 ) // uniquement si on a du transparent
						{
							imagealphablending($ImgDest, false); // Obligé 
							imagesavealpha($ImgDest, true);
						}
						//$CouleurFond = imagecolorallocate($ImgDest,$C[0],$C[1],$C[2]);
						//print_r( $C ) ; die();
						$CouleurFond = imagecolorallocatealpha($ImgDest,$C[0],$C[1],$C[2],(isset($C[3])?$C[3]:127) );
						ImageFill($ImgDest,1,1,$CouleurFond);
					}
				}
            }
        }
        else    // Sinon, on applique la texture du fond !
        {
            $Fond = $this->ImageCreateFromFile( $this->FondImage );
            $V = imagesettile ( $ImgDest,$Fond);
            ImageFill($ImgDest,1,1,IMG_COLOR_TILED);
        }



        if ( $this->ConserverProportion == 1 )
        {
            $FacteurX = ImageSX( $ImgSrc ) / $Width;
            $FacteurY = ImageSY( $ImgSrc ) / $Height;

            if ( ($FacteurX>$FacteurY && $this->Crop==0) || ($FacteurX<$FacteurY && $this->Crop==1) )    // L'image doit �tre r�duite sur la largeur
            {                                                                           // Le Crop inverse le calcul !!! on se fie � la dimension la plus petite au lieu de la plus grande.
                $X1 = 0;
                $Y1 = ($Height - (ImageSY($ImgSrc)/$FacteurX))/2;
                $X2 = $Width;
                $Y2 = ImageSY($ImgSrc)/$FacteurX;
            }
            else    // Ici, on r�duit par rapport � la hauter
            {
                $Y1 = 0;
                $X1 = ($Width - (ImageSX($ImgSrc)/$FacteurY))/2;
                $Y2 = $Height;
                $X2 = ImageSX($ImgSrc)/$FacteurY;
            }

        }
        else
        {
            $X1 = 0;    // L'image est d�form�e pour coller aux dimensions finales
            $Y1 = 0;
            $X2 = $Width;
            $Y2 = $Height;
        }


        if ( $X1>0 && $X1<1 ) $X1=1;  // V�rifie les cas d'une image trop petite ! et la remet � au moins 1x1 pixel !!!
        if ( $Y1>0 && $Y1<1 ) $Y1=1;
        if ( $X2>0 && $X2<1 ) $X2=1;
        if ( $Y2>0 && $Y2<1 ) $Y2=1;

        if ( strtolower($this->Methode) == "resample" )
            imagecopyresampled($ImgDest,$ImgSrc,$X1,$Y1,0,0,$X2,$Y2,ImageSX($ImgSrc),ImageSY($ImgSrc) );
        else    // $this->Methode = "resize"
            imagecopyresized($ImgDest,$ImgSrc,$X1,$Y1,0,0,$X2,$Y2,ImageSX($ImgSrc),ImageSY($ImgSrc) );

        if ( ( isset( $this->Eval  ) && is_array($this->Eval) ) )
            $this->imgEval( $ImgDest , $Width, $Height,"B" );

        
        $this->Tatouage( $ImgDest );

        $this->Texte( $ImgDest );

        if ( ( isset( $this->Eval  ) && is_array($this->Eval) ) )
            $this->imgEval( $ImgDest , $Width, $Height,"C" );


		if( $this->Gris !== false )
			$this->Gris( $ImgDest );

                //----- Fin du calcul du temps ! affichage si n�cessaire
        $TimeFin = microtime( true );
        if ( isset($_REQUEST['AT']) && $_REQUEST['AT'] == 1 )
        {
            $TimeTotal = ($TimeFin - $TimeDebut)*1000;
            $coul = imagecolorallocate( $ImgDest,255,0,0);
            imagestring( $ImgDest,1,0,0, $TimeTotal, $coul );
        }

        $this->ExportImage($ImgDest, $Fichier);
        
    }

	function Gris( &$im )
	{
		// imagefilter( $im, IMG_FILTER_COLORIZE, $this->Gris*2.5, $this->Gris*2.5, $this->Gris*2.5 );
		imagefilter( $im, IMG_FILTER_GRAYSCALE );
		imagefilter( $im, IMG_FILTER_BRIGHTNESS, intval($this->Gris*25.5) );
		
	}

    function imgEval( &$im, $sizeX, $sizeY,$instant="C" )     // Traite les lignes à évaluer, sur image destination
    {           // instant permet de définir à quel moment du traitement, on évalue les lignes
                // -> A => au début, sur l'image source
                // -> B => au milieu (après redimensionnement et avant tatouage et texte)
                // -> C ou rien => à la fin, juste avant anevoi au navigateur

          //var $x1, $x2, $y1, $Y2, $c1, $c2, $v1, $v2, $v3, $v4, $res;
        //print_r( $this->Eval );
        foreach( $this->Eval as $ligne )
        {
            if ( substr( $ligne,1,1) == " " )
            {
                $lettre = substr( $ligne,0,1);
                if ( $lettre==$instant )
                    eval( substr($ligne,2) );
            }
            else
                if ( $instant == "C" )
                    eval( $ligne );
        }
    }

    
    // -- Ouvre une image depuis un fichiers !!!
    function ImageCreateFromFile($Fichier)
    {
        $Infos = getimagesize( $Fichier );
        if ( $this->Format == "auto" ) // Attribution automatique en fonction du format original 
        {
        	$fmt='jpg';
        	$LstFormat = array( 1=>"gif","jpg","png" );
        	if ( isset( $LstFormat[$Infos[2]] )) $this->Format = $LstFormat[$Infos[2]];
        }
        switch( $Infos[2] )
        {
            case '1' :
                return imagecreatefromgif( $Fichier );
                break;
            case '2' :
                return ImageCreateFromJPEG( $Fichier );
                break;
            case '3' :
            	if( $this->FondCouleur=='BLANC' ) // Si on passe une couleur, on doit passer les 3 codes couleurs. BLANC est la variable par défaut (hors initialisation humaine)
            		$this->FondCouleur = '-1'; // Si aucune couleur de fond précisée, on active la transparence par défaut 
                return imagecreatefrompng( $Fichier );
                break;
            case '5' :
                return "PSD";
                break;
            case '6' :
                return "BMP";
                break;
            case '7' :
                return "TIFF (Ordre des octets Intel)";
                break;
            case '8' :
                return "TIFF (Ordre des octets Motorola)";
                break;
            case '9' :
                return "JPC";
                break;
            case '10' :
                return "JP2";
                break;
            case '11' :
                return "JPX";
                break;
            case '12' :
                return "JB2";
                break;
            case '13' :
                return "SWC";
                break;
            case '14' :
                return "IFF";
                break;
        }
        
    }

    function ExportImage(&$Im, $Fichier="",$Header=1 )
    {
		imageinterlace ( $Im , $this->Progress );
		
        switch( $this->Format )
        {
            case 'jpg' :
                if ( $Fichier != "" )
                    return imagejpeg($Im,$Fichier,$this->Compress);
                else
                {
                    if ($Header) header("Content-type: image/jpeg");
                    return imagejpeg($Im,"",$this->Compress);
                }
                break;
            case 'gif' :
                if ( $Fichier != "" )
                    return imagegif($Im,$Fichier);
                else
                {
                    if ($Header) header("Content-type: image/gif");
                    return imagegif($Im);
                }
                break;
            case 'png' :
                 if ( $Fichier != "" )
                    return imagepng($Im,$Fichier);
                else
                {
                    if ($Header) header("Content-type: image/png");    
                    return imagepng($Im);
                }
                break;
        }
    }

    function ExportImageCache($FichierCache, $Header=1 )
    {
        $Donnee = file_get_contents( $FichierCache );     // R�cup�re le contenu du fichier !

        switch( $this->Format )
        {
            case 'jpg' :
                    if ($Header) header("Content-type: image/jpeg");
                    echo $Donnee;
                    return ;
                break;
            case 'gif' :
                    if ($Header) header("Content-type: image/gif");
                    echo $Donnee;
                break;
            case 'png' :
                    if ($Header) header("Content-type: image/png");
                    echo $Donnee;
                break;
        }
    }
    
    function Tatouage( &$Im )
    {
        if ( $this->TatouageImage != "" )
        {
            $X = $this->TatouagePosX;
            $Y = $this->TatouagePosY;
            if ( $X < 0 )
                $X = ImageSX( $Im ) + $X;
            if ( $Y < 0 )
                $Y = ImageSY( $Im ) + $Y;
            
            $Tatou = $this->ImageCreateFromFile( $this->TatouageImage );
            imagecopy($Im,$Tatou,$X,$Y,0,0,ImageSX($Tatou),ImageSY($Tatou) );
        }
    }

    function Texte( &$Im )
    {
        //echo "Toto";
        if ( $this->TexteTexte != "" )
        {

            $X = $this->TextePosX;
            $Y = $this->TextePosY;
            if ( $X < 0 ) $X = ImageSX( $Im ) + $X;
            if ( $Y < 0 ) $Y = ImageSY( $Im ) + $Y;
            
            $Box = imagettfbbox ( $this->TexteSize, 0, $this->TextePolice, $this->TexteTexte );
            $x1 = $Box[6]+$X;
            $y1 = -$Box[3]+$Y;
            $x2 = $Box[2]+$X;
            $y2 = -$Box[7]+$Y;

            //echo "$x1 - $y1 - $x2 - $y2 + $X + $Y + $this->TexteBordure";
            if ( $this->TexteFond != "" )
            {
                $C = explode(",", $this->TexteFond );
                $CouleurFond = imagecolorallocate($Im,$C[0],$C[1],$C[2]);
                imagefilledrectangle ( $Im, $x1-$this->TexteBordure, $y1-$this->TexteBordure, $x2+$this->TexteBordure, $y2+$this->TexteBordure, $CouleurFond );
            }

            $C = explode(",", $this->TexteCouleur );
            $TexteCouleur = imagecolorallocate($Im,$C[0],$C[1],$C[2]);
            imagettftext( $Im, $this->TexteSize,0,$x1,$y2, $TexteCouleur, $this->TextePolice, $this->TexteTexte );
            //imagestring( $Im,2,0,0,$this->TexteTexte, $TexteCouleur );



        }
    }
     
}


?>
