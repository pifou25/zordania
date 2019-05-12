<?php

namespace Zordania\controller;

use \Zordania\model\QstCfg;

/**
 * the Qst controler
 * manage both user and admin actions
 */
class Qst {
    
    // action is the module and template name
    protected $act;
    
    // variables for the template
    protected $var = [];
    
    public function __construct($act){
        if(empty($act))
            $act = 'index';
        $this->act = $act;
    }

    public function getVar(){return $this->var;}
    
    /**
     * obtenir la 'route' : le nom du template
     * @return string
     */
    public function executerAction() {
        // check droit joueur
        if(!\session::instance()->canDo(DROIT_PLAY))
            return ['module_tpl' => 'modules/need_to_be_loged.tpl'];
        
        $classeControleur = explode('\\', get_class($this));
        if (method_exists($this, $this->act)) {
            $tpl = ['module_tpl' => mb_strtolower('modules/'. array_pop($classeControleur) .'/'. $this->{$this->act}() .'.tpl')];
            return array_merge( $tpl, $this->var);
        }
        else {
            throw new \Exception("Action '{$this->act}' non définie dans la classe ". array_pop($classeControleur) );
        }
    }
    
    public function executerAdmin(){
        // check droit admin
        if (!\session::instance()->canDo(DROIT_ADM_MBR))
            return ['module_tpl' => 'modules/need_to_be_admin.tpl'];

        return $this->executerAction();
    }
    
    /**
     * les differentes actions sont des methodes
     * 
     */
    function index(){
        if(isset(\session::instance()->get('qst')['qst_id']))
            $this->var['quete'] = QstCfg::get(\session::instance()->get('qst')['qst_id']);
        else
            $this->var['quete'] = QstCfg::get(false);

        $this->var['hist'] = \Zordania\model\Qst::getAll(\session::instance()->get('mid'));
        return 'index';
    }
    
    function update(){
        // maj session
        \session::instance()->update_qst();
        return $this->index();
    }
    
    function view(){
        if(!empty($_GET['qst'])){
            $qst = \Zordania\model\Qst::where('qst_id', $_GET['qst']);
            if(!empty($qst) && $qst->count() == 1){
                $hqst = $qst->first();
                $this->var['hqst'] = $hqst;
                $this->var['quete'] = QstCfg::where('cfg_id', $hqst->qst_cfg_id)->first();
                return $this->act;
            }
        }
        return $this->index();
    }
    
    /**
     * admin actions
     */
    function admin(){
        $tid = request('tid', 'uint', 'get');
        if($tid){
            // liste complete des quetes
            $qst =  QstCfg::where('cfg_tid', $tid)->get();
            $this->var['qstDetail'] = $qst;
        }else{
            // liste des topics uniquement
            $this->var['qst'] = \FrmTopic::get(['fid' => FORUM_QUETES, 'select' => 'topic']);
        }
        return 'admin';
    }
    
    function config(){
        // inserer les nouvelles quetes a parametrer :
        $this->tpl['update'] = QstCfg::majAll();
        return $this->admin();

    }
    
    function exp(){
        // export : telecharger fichier sql
        header('Content-Type: text');
        header('Content-Disposition: attachment; filename="forum.'.FORUM_QUETES.'.sql"');
        die(\SqlAdm::dumpFrm(FORUM_QUETES, SqlAdm::EXP_DATA));

    }
    
    function mbr(){
        // quetes d'un membre
        $this->var['mbr'] = \Mbr::find($_GET['mid']);
        return $this->admin();
    }
    
    function edit(){
        // editer les parametres de quete
        $id = request('id', 'uint', 'get');
        $qstCfg = QstCfg::find($id);

        $valid = array_unset($_POST, 'Valider');
        if($valid != NULL){
            $pseudo = array_unset($_POST, 'msg_pseudo');
            $mbr = \Mbr::where('mbr_pseudo' , $pseudo)->first();
            if(!empty($mbr)){
                $qstCfg->cfg_mid = $mbr->mbr_mid;
            }
            // every other POST values update the model
            foreach($_POST as $key => $value){
                if(!empty($value)){
                    $qstCfg->$key = $value;
                }
            }
            $this->var['update'] = $qstCfg->save();
        }
        if(!empty($qstCfg)){
            $this->var['quete'] = $qstCfg;
            return 'edit';
        }

        return $this->admin();

    }
}

//Verifications
if(!defined("_INDEX_")){ exit; }

$controleur = new Qst($_act);
$_tpl->set($controleur->executerAction());
