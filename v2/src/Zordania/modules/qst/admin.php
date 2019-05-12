<?php

//Verif
if(!defined("_INDEX_") or !$_ses->canDo(DROIT_ADM_MBR)){exit;}

require 'page.php';

if(empty($_act))
    $_act = 'admin';
$controleur = new \Zordania\controller\Qst($_act);
$_tpl->set($controleur->executerAction());
