<?php

//Verifications
if (!defined("_INDEX_")) {
    exit;
}
if (!session::$SES->canDo(DROIT_PLAY))
    $_tpl->set("need_to_be_loged", true);
else {
    $_tpl->set('module_tpl', 'modules/recap/recap.tpl');

//quelle liste afficher?	
    $fid = request('fid', 'uint', 'get');
//type pour le tri
    $type = request('type', 'uint', 'get');

    $recap_array = FrmTopic::where('forum_id', $fid)->where('report_type', $type)
                    ->where('statut', '<>', REPORT_STATUT_DUBL)
                    ->orderBy('last_post', 'desc')->get()->toArray();
    $_tpl->set('recap_array', $recap_array);
    $_tpl->set('fid_get', $fid);

    $_tpl->set('tp', $type);
}
