<?php

if(!defined("_INDEX_") || !can_d(DROIT_ADM_MBR)){ exit; }

// admin paramétrage des quêtes

require_once('lib/forum.lib.php');
require_once('lib/parser.lib.php');

$_tpl->set("module_tpl","modules/qst/admin.tpl");

if($_act == 'exp'){

    // telecharger fichier sql
    header('Content-Type: text');
    header('Content-Disposition: attachment; filename="forum.'.FORUM_QUETES.'.sql"');
    die(SqlAdm::dumpFrm($fid, SqlAdm::EXP_DATA));

}else if(is_numeric($_act)){
    
    // get post $_act as a quest
    $post = get_post($_act);
    if(!empty($post)){
        $_tpl->set('post', $post);
        
        // get quest
    }
    
}else{
        
    // liste des topics de la section dédiée
    $_tpl->set('topics', get_topic(['fid'=>FORUM_QUETES, 'select'=>'mbr']));

}
