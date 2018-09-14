<?php
//Verifications
if(!defined("_INDEX_")){ exit; }
if(!$_ses->canDo(DROIT_PLAY))
	$_tpl->set("need_to_be_loged",true); 
else {

}
?>