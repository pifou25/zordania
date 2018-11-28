<?php

//Verif
if(!defined("_INDEX_") or !$_ses->canDo(DROIT_ADM_MBR)){exit;}

$_tpl->set("module_tpl","modules/qst/admin.tpl");

if($_act == 'update'){
    // inserer les nouvelles quetes a parametrer :
    QstCfg::majAll();
}

if($_act == 'edit'){
    $id = request('id', 'uint', 'get');
    
    if(!empty($_POST) && isset($_POST['Valider'])){
        $mbr = $mbr_infos = Mbr::get(array('pseudo' => $_POST['msg_pseudo']));
        $mid = empty($mbr) ? null : $mbr[0]['mbr_mid'];
        $request = ['cfg_mid' => $mid];
        if(!empty($_POST['cfg_subject'])){
            $request['cfg_subject'] = $_POST['cfg_subject'];
        }
        
        if($_POST['param1'] != 0){
            $request['cfg_param1'] = $_POST['param1'];
            $request['cfg_value1'] = $_POST['value1'];
        }else{
            $request['cfg_param1'] = null;
            $request['cfg_value1'] = null;
        }
        if($_POST['param2'] != 0){
            $request['cfg_param2'] = $_POST['param2'];
            $request['cfg_value2'] = $_POST['value2'];
        }else{
            $request['cfg_param2'] = null;
            $request['cfg_value2'] = null;
        }
        if($_POST['param3'] != 0){
            $request['cfg_param3'] = $_POST['param3'];
            $request['cfg_value3'] = $_POST['value3'];
        }else{
            $request['cfg_param3'] = null;
            $request['cfg_value3'] = null;
        }
        if($_POST['param4'] != 0){
            $request['cfg_param4'] = $_POST['param4'];
            $request['cfg_value4'] = $_POST['value4'];
        }else{
            $request['cfg_param4'] = null;
            $request['cfg_value4'] = null;
        }
        if($_POST['param5'] != 0){
            $request['cfg_objectif'] = $_POST['param5'];
            $request['cfg_obj_value'] = $_POST['value5'];
        }else{
            $request['cfg_objectif'] = 0; // aucun objectif? devrait faire une erreur
            $request['cfg_obj_value'] = null;
        }
        $_tpl->set('update', QstCfg::where('cfg_id', $id)->update($request));
    }
    
    $qst = QstCfg::get($id);
    if($qst){
        
        $_tpl->set('quete', $qst);
        
    }
}else{
    $tid = request('tid', 'uint', 'get');
    if($tid){
        // liste complete des quetes
        $qst =  QstCfg::where('cfg_tid', $tid)->get()->toArray();
        foreach($qst as $row){
            $ids[] = $row['cfg_pid'];
        }
        $psts = FrmPost::whereIn('id', $ids)->get()->toArray();
        $_tpl->set('qstDetail', QstCfg::join('frm_posts', 'cfg_pid', 'id')->where('cfg_tid', $tid)->get()->toArray());
    }else{
        // liste des topics uniquement
        $_tpl->set('qst', FrmTopic::get(['fid' => QUETES_FID, 'select' => 'topic']));
    }
}
