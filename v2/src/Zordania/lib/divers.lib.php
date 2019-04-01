<?php

/* fonction de chargement automatique pour les classes */
spl_autoload_register(function ($classname) {

    if (is_file(SITE_DIR ."src/Zordania/class/$classname.php"))
		require_once SITE_DIR ."src/Zordania/class/$classname.php";
	else if (is_file(SITE_DIR ."src/Zordania/model/$classname.php"))
		require_once SITE_DIR ."src/Zordania/model/$classname.php";
	else if (is_file(SITE_DIR ."src/$classname.php"))
		require_once SITE_DIR ."src/$classname.php";
        else{
            //echo "no file for class $classname -- ";
            return false;
        }
        
});

/* Envoie un mail */
function mailto($from, $to, $sujet, $message, $html=FALSE)
{
	if($html) {
	  		$from ="From: Zordania <".$from."> \n"; 
	  		$from .= "MIME-Version: 1.0\n";
			$from .= "Content-type: text/html; charset=utf-8\n";
	 }else
		$from="From: $from <$from>";

	return mail($to,$sujet,$message,$from);
}


/* Vérifie que y'a des char corrects - v2: que des lettres + espace et apostrophe */
function strverif($str)
{
	return preg_match("!^[a-zA-Z 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜàâãäåçèéêëìíîïòóôõöùúûüÿ]*$!i",$str);
	//return preg_match("!^[a-zA-Z0-9_\-' éêèëàêôöäüï]*$!i",$str);
}

function array_utf8_encode($data) {
  if (is_array($data)) {
    foreach ($data as & $value) {
      $value = array_utf8_encode($value);
    }
    return $data;
  } else if (is_string($data))
    return utf8_encode($data);
  else
    return $data;
}

function safe_unserialize($str)
{
  $str64 = base64_decode($str, true);
  $data = false;
  if ($str64 !== false)
	  $data = @unserialize($str64);
  if (!$data or $str64 === false){ /* wasn't base64 data */
	//echo "<pre>$str\n</pre>";
    $data = @unserialize($str);
  }
  if (!$data) {
    $str = utf8_decode($str);
    $data = @unserialize($str);
    $data = array_utf8_encode($data);
  }
  return $data;
}

function calc_key($str1, $str2, $len = GEN_LENGHT) // ex: $_file + pseudo
{
	return substr(md5($str1 . $str2), 0, $len);
}

	
function genstring($longueur) //genere une chaine a x caracteres aleatoirement
{
	$str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; //²&"~`%*$^|
	$gen = '';

	for ($i=0;$i<$longueur;$i++)
	$gen.= substr($str, mt_rand(0, strlen($str) - 1), 1);
	
	return $gen;
}
	
/* protège une chaine avant de la mettre dans mysql */
function protect($var, $type = "unknown")
{
	
	if(is_array($type)){
		if(is_array($var))
			foreach($var as $key => $value)
				$var[$key] = protect($value, $type[0]);
		else $var = array();
	} else {
		switch($type) {
		case "bool":
			$var = (bool) $var;
			break;
		case "int":
			$var = (int) $var;
			break;
		case "uint":
			$var = (int) $var;
			if($var < 0) $var = 0;
			break;
		case "float":
			$var = (float) $var;
			break;
		case "array":
			if(!is_array($var)) $var = array();
			break;
		case "serialize":
			if(!is_array($var)) $var = array();
			$var = base64_encode(serialize($var));
			break;
		case "string":
			$var = htmlspecialchars($var);
			break;
		case "bbcode": /* Rien a faire */
			break;
		}
	}

	/* Protection si ce n'est pas un entier */
//	if (is_string($var)) {
//		$var = $_sql->escape($var);
//	}
	return $var;
}

/* Permet de prendre des trucs dans GET et POST même si ils y sont pas, avec une var par defaut .. */
function request($name, $type, $method, $default = false)
{
	$def = array("bool" => false, "uint" =>0, "int" => 0, "float" => 0,
			"array" => array(),"string" => "");

	if($method == 'get' && isset($_GET[$name])) 
		$var = $_GET[$name]; 
	elseif($method == 'post' && isset($_POST[$name])) 
		$var = $_POST[$name]; 
	elseif($method == 'cookie' && isset($_COOKIE[$name])) 
		$var = $_COOKIE[$name];
	elseif($method == 'session' && isset($_SESSION[$name])) 
		$var = $_SESSION[$name];
	elseif($method == 'files' && isset($_FILES[$name]))
		$var = $_FILES[$name];
	elseif($method == 'server' && isset($_SERVER[$name]))
		$var = $_SERVER[$name];
	elseif($default)
		return $default;
	elseif(is_array($type))
		return $def['array'];
	else
		return $def[$type];

	if(is_array($type)){// récupérer un array[string]
		if(!is_array($var))
			$var = array();
		else switch($type[0]){
		case 'string':
			foreach($var as $key => $value){
				$var[$key] = (string) $value;
				if(get_magic_quotes_gpc())
					$var[$key] = stripslashes($var[$key]);
			}
			break;
		}
	}else{
		switch($type) {
		case "bool":
			$var = (bool) $var;
			break;
		case "int":
			$var = (int) $var;
			break;
		case "uint":
			/*$var = abs((int) $var);*/
			$var = (int) $var;
			if($var < 0) $var = $def['uint'];
			break;
		case "float":
			$var = (float) $var;
			break;
		case "array":
			if($method == "cookie" && is_string($var)) $var = @safe_unserialize($var);
			if(!is_array($var)) $var = array();
			break;
		case "string":
			$var = (string) $var;
			if(get_magic_quotes_gpc())
				$var = stripslashes($var);
			break;
		case "raw":
		default:
			break;
		}
	}
	return $var;
}

/* Index un array a partir d'une de ses valeurs */
function index_array(& $array, $key) {
	$array = protect($array, "array");
	$key = protect($key, "string");
	$tmp = array();

	foreach($array as $value) {
		if(isset($value[$key]))
			$tmp[$value[$key]] = $value;
	}

	return $tmp;
}

/* cumuler les arrays par clé */
function array_ksum(&$arr1, $arr2, $factor = 1) {
	foreach($arr2 as $key => $value)
		if(isset($arr1[$key]))
			$arr1[$key] += $value * $factor;
		else
			$arr1[$key] = $value * $factor;
}

function get_flip_const($prefix){ // retrouver les constantes, inverser clé/valeur
	$const = get_defined_constants(true);
	$const = $const['user'];

	$return = array();
	foreach($const as $key => $value)
		if(strpos($key, $prefix) === 0)
			$return[$value] = $key;
	return $return;
}

//$page est la page courante
//$nb_page le nombre de pages totales
//$nb le nombre de page à retourner à droite et à gauche
function get_list_page($page, $nb_page, $nb = 3)
{
	$list_page = array();
	for ($i=1;$i <= $nb_page;$i++){
		if (($i <= $nb) OR ($i >= $nb_page - $nb) OR (($i < $page + $nb) AND ($i > $page -$nb)))
			$list_page[] = $i;
		else{
			if ($i >= $nb AND $i <= $page - $nb)
				$i = $page - $nb;
			elseif ($i >= $page + $nb AND $i <= $nb_page - $nb)
				$i = $nb_page - $nb;
			$list_page[] = '...';
		}
	}
	return $list_page;
}
