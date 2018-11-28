<?php

//Verifications
if(!defined("_INDEX_")){ exit; }
if(!$_ses->canDo(DROIT_PLAY))
	$_tpl->set("need_to_be_loged",true); 
else
{

    if($_act == 'update'){// maj session
        $_ses->update_qst();
    }
    
    $_tpl->set("module_tpl","modules/qst/page.tpl");
        
    if(isset($_ses->get('qst')['qst_id'])){
        if($_act == 'read')
            Qst::where('qst_id', $_ses->get('qst')['qst_id'])->update(['read' => 0]);
        
        $_tpl->set('quete', QstCfg::get($_ses->get('qst')['qst_id']));
    }
    else
        $_tpl->set('quete', QstCfg::get(false));
        
    $_tpl->set('hist', Qst::getAll($_ses->get('mid')));

}