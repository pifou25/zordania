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
            $request['cfg_objectif'] = null;
            $request['cfg_obj_value'] = null;
        }
        $_tpl->set('update', QstCfg::where('cfg_id', $id)->update($request));
    }
    
    $qst = QstCfg::get($id);
    if($qst){
        
        $_tpl->set('quete', $qst);
        
    }
}else{
    $_tpl->set('qst', QstCfg::join('frm_topics', 'cfg_tid', 'id')->get()->toArray());
}
