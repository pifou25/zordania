<?php
/* 
 * update forum: refresh word cache forum
 */

error_reporting (E_ALL | E_STRICT | E_RECOVERABLE_ERROR);
define('PROTOCOL', 'https');

require_once("../../../conf/conf.inc.php");
require_once SITE_DIR . 'vendor/autoload.php';
require_once(SITE_DIR . 'src/Zordania/lib/divers.lib.php');

DB::init($settings['database']);

$i = 0;
foreach(FrmWord::all() as $word){
    if($i % 100 == 0)
        echo "$word $i\n";
    $word->php_soundex = soundex($word->word);
    $word->metaphone = metaphone($word->word);
    $word->save();
    $i++;
}