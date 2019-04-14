<?php

namespace Zordania\module;

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
            throw new \Exception("Action '{$this->act}' non définie dans la classe $classeControleur");
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
        return $this->act;
    }
    
    function update(){
        // maj session
        \session::instance()->update_qst();
        return $this->index();
    }
    
    function read(){
        if(isset(\session::instance()->get('qst')['qst_id'])){
            // maj vue quete
            Qst::where('qst_id', \session::instance()->get('qst')['qst_id'])->update(['read' => 0]);

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
//            foreach($qst as $row){
//                $ids[] = $row['cfg_pid'];
//            }
//            $psts = \FrmPost::whereIn('id', $ids)->get()->toArray();
//            $this->var['qstDetail'] = QstCfg::join('frm_posts', 'cfg_pid', 'id')->where('cfg_tid', $tid)->get()->toArray();
            $this->var['qstDetail'] = $qst;
        }else{
            // liste des topics uniquement
            $this->var['qst'] = \FrmTopic::get(['fid' => FORUM_QUETES, 'select' => 'topic']);
        }
        return 'admin';
    }
    
    function config(){
        // inserer les nouvelles quetes a parametrer :
        QstCfg::majAll();
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
        $this->var['mbr'] = \Mbr::where('mbr_id' , $_GET['mid']);
        return $this->admin();
    }
    
    function edit(){
        // editer les parametres de quete
        $id = request('id', 'uint', 'get');

        if(!empty($_POST) && isset($_POST['Valider'])){
            $mbr = \Mbr::where('mbr_pseudo' , $_POST['msg_pseudo'])->first();
            $mid = empty($mbr) ? null : $mbr->mbr_mid;
            $request = ['cfg_mid' => $mid];
            if(!empty($_POST['cfg_subject'])){
                $request['cfg_subject'] = $_POST['cfg_subject'];
            }

            // traiter les 4 param
            for($i = 1; $i < 5; $i++){
                if($_POST["param$i"] != 0){
                    $request["cfg_param$i"] = $_POST["param$i"];
                    $request["cfg_value$i"] = $_POST["value$i"];
                }else{
                    $request["cfg_param$i"] = null;
                    $request["cfg_value$i"] = null;
                }
            }
            if($_POST['param5'] != 0){
                $request['cfg_objectif'] = $_POST['param5'];
                $request['cfg_obj_value'] = $_POST['value5'];
            }else{
                $request['cfg_objectif'] = 0; // aucun objectif? devrait faire une erreur
                $request['cfg_obj_value'] = null;
            }
             $this->var['update'] = QstCfg::where('cfg_id', $id)->update($request);
        }

        $qst = QstCfg::where('cfg_id', $id)->first(); // QstCfg::get($id);
        if($qst){
             $this->var['quete'] = $qst;
        }else{
            return $this->admin();
        }
        return 'edit';

    }
}

//Verifications
if(!defined("_INDEX_")){ exit; }

$controleur = new \Zordania\module\Qst($_act);
$_tpl->set($controleur->executerAction());
