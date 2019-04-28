<?php

//Verif
if(!defined("_INDEX_") or !$_ses->canDo(DROIT_ADM_MBR)){exit;}

require 'page.php';

if(empty($_act))
    $_act = 'admin';
$controleur = new \Zordania\module\Qst($_act);
$_tpl->set($controleur->executerAction());

//if($_act == 'update'){
//    // inserer les nouvelles quetes a parametrer :
//    QstCfg::majAll();
//    
//}else if($_act == 'exp'){
//    // telecharger fichier sql
//    header('Content-Type: text');
//    header('Content-Disposition: attachment; filename="forum.'.FORUM_QUETES.'.sql"');
//    die(SqlAdm::dumpFrm(FORUM_QUETES, SqlAdm::EXP_DATA));
//
//}else if($_act == 'mbr'){
//    // quetes d'un membre
//    $_tpl->set('mbr', Mbr::where('mbr_id' , $_GET['mid']));
//    
//}else if($_act == 'edit'){
//    // editer les parametres de quete
//    $id = request('id', 'uint', 'get');
//    
//    if(!empty($_POST) && isset($_POST['Valider'])){
//        $mbr = Mbr::where('mbr_pseudo' , $_POST['msg_pseudo']);
//        $mid = empty($mbr) ? null : $mbr->mbr_mid;
//        $request = ['cfg_mid' => $mid];
//        if(!empty($_POST['cfg_subject'])){
//            $request['cfg_subject'] = $_POST['cfg_subject'];
//        }
//
//        // traiter les 4 param
//        for($i = 1; $i < 5; $i++){
//            if($_POST["param$i"] != 0){
//                $request["cfg_param$i"] = $_POST["param$i"];
//                $request["cfg_value$i"] = $_POST["value$i"];
//            }else{
//                $request["cfg_param$i"] = null;
//                $request["cfg_value$i"] = null;
//            }
//        }
//        if($_POST['param5'] != 0){
//            $request['cfg_objectif'] = $_POST['param5'];
//            $request['cfg_obj_value'] = $_POST['value5'];
//        }else{
//            $request['cfg_objectif'] = 0; // aucun objectif? devrait faire une erreur
//            $request['cfg_obj_value'] = null;
//        }
//        $_tpl->set('update', QstCfg::where('cfg_id', $id)->update($request));
//    }
//    
//    $qst = QstCfg::get($id);
//    if($qst){
//        
//        $_tpl->set('quete', $qst);
//        
//    }
//}else{
//    $tid = request('tid', 'uint', 'get');
//    if($tid){
//        // liste complete des quetes
//        $qst =  QstCfg::where('cfg_tid', $tid)->get()->toArray();
//        foreach($qst as $row){
//            $ids[] = $row['cfg_pid'];
//        }
//        $psts = FrmPost::whereIn('id', $ids)->get()->toArray();
//        $_tpl->set('qstDetail', QstCfg::join('frm_posts', 'cfg_pid', 'id')->where('cfg_tid', $tid)->get()->toArray());
//    }else{
//        // liste des topics uniquement
//        $_tpl->set('qst', FrmTopic::get(['fid' => FORUM_QUETES, 'select' => 'topic']));
//    }
//}
//
