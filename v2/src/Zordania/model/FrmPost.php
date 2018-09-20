<?php

/**
 * forums : les réponses
 */
class FrmPost extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'frm_posts';

    /**
     * select = tid|pid|post (seulement table posts)|mbr (infos membre+ally+grade)
     *           substr (pour la recherche, message tronqué)|first_pid (le 1er post de la discussion)
     *           ou rien (selection posts + infos topic + permissions)
     * @param array $cond
     * @return type
     */
    static function get(array $cond) {// fonction générale, full options
        $select = isset($cond['select']) ? protect($cond['select'], 'string') : '';
        $tid = isset($cond['tid']) ? protect($cond['tid'], 'uint') : 0;
        $tid_list = isset($cond['tid_list']) ? protect($cond['tid_list'], 'array') : array();
        $pid = isset($cond['pid']) ? protect($cond['pid'], 'uint') : 0;
        $pid_list = isset($cond['pid_list']) ? protect($cond['pid_list'], 'array') : array();
        $fid = isset($cond['fid']) ? protect($cond['fid'], 'uint') : 0;
        $user = isset($cond['user']) ? protect($cond['user'], 'uint') : 0;
        $show_unanswered = isset($cond['show_unanswered']) ? protect($cond['show_unanswered'], 'bool') : false;
        $new_posts = isset($cond['show_new']) ? protect($cond['show_new'], 'bool') : false;
        $last_24h = isset($cond['show_24h']) ? protect($cond['show_24h'], 'bool') : false;
        $group = isset($cond['group']) ? protect($cond['group'], 'string') : ''; // utile ??
        $sort_by = isset($cond['sort_by']) ? protect($cond['sort_by'], 'string') : '';
        $sortDir = isset($cond['sort_dir']) && $cond['sort_dir'] == 'DESC' ? 'DESC' : 'ASC';
        $start = isset($cond['start']) ? protect($cond['start'], 'uint') : -1;
        $limite = isset($cond['limit']) ? protect($cond['limit'], 'uint') : LIMIT_PAGE;

        if ($select == 'tid')
            $sql = 'DISTINCT ' . DB::getTablePrefix() . 't.id AS tid ';
        else if ($select == 'pid')
            $sql = DB::getTablePrefix() . 'p.id AS pid ';
        else if ($select == 'substr')
            $sql = DB::getTablePrefix() . 'p.id AS pid, ' . DB::getTablePrefix() . 'p.poster AS pposter, _UDATE_FORMAT(' . DB::getTablePrefix() . 'p.posted) AS pposted,
		' . DB::getTablePrefix() . 'p.poster_id, SUBSTRING(message, 1, 1000) AS message, ' . DB::getTablePrefix() . 't.id AS tid, ' . DB::getTablePrefix() . 't.poster, t.subject, 
		_UDATE_FORMAT(' . DB::getTablePrefix() . 't.last_post) AS last_post, ' . DB::getTablePrefix() . 't.last_post_id, ' . DB::getTablePrefix() . 't.last_poster, ' . DB::getTablePrefix() . 't.num_replies, ' . DB::getTablePrefix() . 't.forum_id ';
        else if ($select == 'post')
            $sql = '' . DB::getTablePrefix() . 'p.id AS pid, ' . DB::getTablePrefix() . 'p.poster, _UDATE_FORMAT(' . DB::getTablePrefix() . 'p.posted) AS posted,
		' . DB::getTablePrefix() . 'p.poster_id, message, topic_id AS tid, _UDATE_FORMAT(edited) AS edited, edited_by ';
        else
            $sql = '' . DB::getTablePrefix() . 'p.id AS pid, ' . DB::getTablePrefix() . 'p.poster AS username, ' . DB::getTablePrefix() . 'p.poster_id, message, 
		_UDATE_FORMAT(' . DB::getTablePrefix() . 'p.posted) AS posted, _UDATE_FORMAT(edited) AS edited, edited_by, topic_id AS tid,
		' . DB::getTablePrefix() . 't.forum_id, subject, sticky, closed ';
        if ($select == 'mbr')
            $sql .= ', al_name, al_aid, mbr_sign, mbr_gid ';
        if ($select == 'first_pid')// le 1er post de la discussion
            $sql .= ', (SELECT min(pp.id) FROM '
                    . DB::getTablePrefix() . 'frm_posts AS pp WHERE pp.topic_id = '
                    . DB::getTablePrefix() . 't.id) AS first_pid ';
        $sql = session::$SES->parseQuery($sql);

        $req = FrmPost::selectRaw($sql)->from('frm_posts AS p');
        if ($select != 'post') {
            $req->join('frm_topics AS t', 'p.topic_id', 't.id');
        }
        if ($select == 'mbr') {
            $req->leftJoin('mbr AS m', 'p.poster_id', 'm.mbr_mid')
                    ->leftJoin('al_mbr AS al', function($join) {
                        $join->on('m.mbr_mid', 'al.ambr_mid')->where('ambr_etat', '<>', ALL_ETAT_DEM);
                    })
                    ->leftJoin('al AS a', 'al.ambr_aid', 'a.al_aid');
        } else if ($select != 'substr' && $select != 'post') {// substr: spécifique pour la recherche
            $req->join('frm_forums AS f', 't.forum_id', 'f.id')
                    ->leftJoin('frm_forum_perms AS fp', function($join) {
                        $join->on('f.id', 'fp.forum_id')->where('fp.group_id', Session::$SES->get('groupe'))
                        ->whereRaw('IFNULL(read_forum, 1) = 1');
                    });
        }

        if ($tid) {
            $req->where('p.topic_id', $tid);
        } else if ($tid_list) {
            $req->whereIn('p.topic_id', $tid_list);
        } else if ($pid) {
            $req->where('p.id', $pid);
        } else if ($pid_list) {
            $req->whereIn('p.id', $pid_list);
        }
        if ($fid) {
            $req->where('t.forum_id', $fid);
        }
        if ($user) {
            $req->where('p.poster_id', $user);
        }
        if ($show_unanswered) {
            $req->where('t.num_replies', 0)->whereNull('t.moved_to');
        }
        if ($new_posts) {
            $req->where('t.last_post', '>', Session::$SES->get('forum_ldate'));
        }
        if ($last_24h) {
            $req->where('t.last_post', '>', time() - 86400);
        }

        if ($group && $group == 'tid') {
            $req->groupBy('t.id');
        }
        if ($select != 'post') {
            switch ($sort_by) {
                case 1:
                    $req->orderBy('p.poster', $sortDir);
                    break;
                case 2:
                    $req->orderBy('t.subject', $sortDir);
                    break;
                case 3:
                    $req->orderBy('t.forum_id', $sortDir);
                    break;
                case 4:
                    $req->orderBy('t.last_post', $sortDir);
                    break;
                case 5:
                    $req->orderBy('p.posted', $sortDir);
                    break;
                default:
                    $req->orderBy('p.id', $sortDir);
                    break;
            }
        }
        return $req;
    }

    static function getLast(int $mid) {
        $pst = FrmPost::get(['select' => 'posts', 'user' => $mid, 'sort_dir' => 'DESC', 'start' => 0, 'limit' => 1])
                        ->get()->toArray();
        return $pst ? $pst[0] : [];
    }

    static function getById(int $pid) {
        $pst = FrmPost::get(['select' => 'post', 'pid' => $pid])->get()->toArray();
        return $pst ? $pst[0] : [];
    }

    static function getLastFromTopic(int $tid) {
        return FrmPost::get(['select' => 'mbr', 'tid' => $tid, 'sort_dir' => 'DESC', 'start' => 0])
                        ->get()->toArray();
    }

    static function getMsg(int $tid) {
        return FrmPost::get(['select' => 'mbr', 'tid' => $tid]);
    }

    static function searchOffset(int $pid, int $tid) {// rechercher à quelle page se trouve $pid dans $tid
        return FrmPost::where('topic_id', $tid)->where('id', '<', $pid)->orderBy('id', 'asc')->count() + 1;
    }

    static function edit(array $cond) { // fonction générale full options
        $pid = isset($cond['pid']) ? protect($cond['pid'], 'uint') : 0;
        $title = isset($cond['title']) ? $cond['title'] : '';
        $closed = isset($cond['closed']) ? protect($cond['closed'], 'uint') : -1;
        $sticky = isset($cond['sticky']) ? protect($cond['sticky'], 'uint') : -1;
        $fid = isset($cond['fid']) ? protect($cond['fid'], 'uint') : 0;
        $tid = isset($cond['tid']) ? protect($cond['tid'], 'uint') : 0;
        $statut = isset($cond['statut']) ? protect($cond['statut'], 'uint') : 0;
	$type = isset($cond['type']) ? protect($cond['type'], 'uint') : 0;

        if (isset($cond['msg']) ) { // éditer le post
            if (!$pid)
                return false;
            $request = ['message' => $cond['msg']];

            if (!isset($cond['silent'])) {
                $request['edited'] = DB::raw('UNIX_TIMESTAMP()');
                $request['edited_by'] = Session::$SES->get('pseudo');
            }
            FrmPost::where('id', $pid)->update($request);
        }

        if ($closed >= 0 || $sticky >= 0 || $title || $fid || $statut >= 0 || $type >= 0) { // éditer le topic
            if (!$tid)
                return false;

            if ($fid) {// /!\ déplacement topic : mettre à jour le nombre de sujets dans chaque forum /!\
                $topic = FrmTopic::where('id', $tid)->get()->toArray()[0];
                $frm = Frm::where('id', $topic['forum_id'])->get()->toArray()[0];

                //on met à jour le forum de départ : le nb de posts / topics
                $request = ['num_topics' => DB::raw('num_topics - 1'),
                    'num_posts' => DB::raw('num_posts - ' . $topic['num_replies'])];

                if ($topic['last_post_id'] == $frm['last_post_id']) {
                    // si le post déplacé était le dernier sur ce forum, rechercher le précédent :
                    $donnees = FrmTopic::select('last_post', 'last_post_id', 'last_poster', 'last_subject AS subject')
                                    ->where('id', '<>', $tid)->where('forum_id', $topic['forum_id'])
                                    ->orderBy('last_post', 'desc')->take(1)->get()->toArray()[0];

                    if ($donnees) {
                        $request = array_merge($request, $donnees);
                    }
                }
                Frm::where('id', $topic['forum_id'])->update($request);

                //on met à jour le forum d'arrivé
                $request = ['num_topics' => DB::raw('num_topics + 1'),
                    'num_posts' => DB::raw('num_posts + ' . $topic['num_replies']),
                    'last_post' => $topic['last_post'],
                    'last_post_id' => $topic['last_post_id'],
                    'last_poster' => $topic['last_poster'],
                    'last_subject' => $topic['subject']];
                Frm::where('id', $fid)->update($request);
            }

            // UPDATE du topic
            $request = [];
            if ($closed >= 0)
                $request['closed'] = $closed;
            if ($sticky >= 0)
                $request['sticky'] = $sticky;
            if ($title)
                $request['subject'] = $title;
            if ($fid)
                $request['forum_id'] = $fid;
            if ($statut)
                $request['statut'] = $statut;
            if($type) $request['report_type'] = $type;

            FrmTopic::where('id', $tid)->update($request);
        }// fin edition topic

        if ($pid) {//indexation pour la recherche!
            if ($tid && $title)
                FrmWord::index('edit', $pid, $cond['msg'], $title);
            else
                FrmWord::index('edit', $pid, $cond['msg']);
        }

        return true;
    }

    static function stick(int $tid, $stick = true) {
        FrmPost::edit(['tid' => $tid, 'sticky' => $stick]);
        return $stick;
    }

    static function close(int $tid, $close = true) {
        FrmPost::edit(['tid' => $tid, 'closed' => $close]);
        return $close;
    }

    static function add(int $mid, string $pseudo, string $ip, string $pst_msg, int $tid) {

        $request = ['poster' => $pseudo,
            'poster_id' => $mid,
            'poster_ip' => $ip,
            'message' => $pst_msg,
            'posted' => DB::raw('UNIX_TIMESTAMP()'),
            'topic_id' => $tid];

        return FrmPost::insertGetId($request);
    }

    /**
     * efface le post; renvoie true si le topic a aussi été supprimé, false sinon
     * @param array $pst
     * @param array $tpc
     * @return bool
     */
    static function del(array $pst, array $tpc): bool {

        // MAJ indexation recherche
        FrmMatch::index($pst['pid']);

        //puis on supprime le post lui-même
        FrmPost::where('id', $pst['pid'])->delete();

        //on regarde si le topic n'avait qu'un seul message
        if ($tpc['num_replies'] == 1) {
            // on suprime le topic
            FrmTopic::where('id', $tpc['tid'])->delete();

            // si c'était aussi le dernier post du forum /!\
            if ($tpc['frm_last_post_id'] == $pst['pid']) {
                $request = ['f.last_post_id' => DB::raw('f.num_topics - 1'),
                    'f.num_posts' => DB::raw('f.num_posts - 1'),
                    'f.last_post' => DB::raw('t.last_post'),
                    'f.last_post_id' => DB::raw('t.last_post_id'),
                    'f.last_poster' => DB::raw('t.last_poster'),
                    'f.last_subject' => DB::raw('t.subject')];
                DB::table('frm_forums AS f')->leftJoin('frm_topics AS t', 'f.id', 't.forum_id')
                        ->where('f.id', $tpc['forum_id'])->whereRaw('(t.id = ('
                                . 'SELECT id FROM ' . DB::getTablePrefix() . 'topics '
                                . 'WHERE forum_id = ? ORDER BY t.last_post ASC LIMIT 0,1)'
                                . ' OR t.id IS NULL)', $tpc['forum_id'])
                        ->update($request);
            } else {
                FrmTopic::where('id', $tpc['forum_id'])
                        ->update(['num_topics' => DB::raw('num_topics - 1'),
                            'num_posts' => DB::raw('num_posts - 1')]);
            }

            return true;
        }
        //si il y avait plusieurs messages : on ne supprime pas le topic
        else {
            //on met à  jour la table topics, en changeant le dernier post si il le faut
            if ($tpc['last_post_id'] == $pst['pid']) {
                $request = ['t.last_poster' => DB::raw('p.poster'),
                    't.last_post' => DB::raw('p.posted'),
                    't.last_post_id' => DB::raw('p.id'),
                    't.num_replies' => DB::raw('t.num_replies - 1')];
                DB::table('frm_posts AS p')->join('frm_topics AS t', 't.id', 'p.topic_id')
                        ->where('t.id', $tpc['tid'])->whereRaw('(p.id = ('
                                . 'SELECT max(id) FROM ' . DB::getTablePrefix() . 'frm_posts '
                                . 'WHERE topic_id = ?', $tpc['tid'])
                        ->update($request);
            } else {
                // on recherche le 1er post de la discussion (c'est p-e celui qu'on a supprimé)
                $minId = FrmPost::where('topic_id', $tpc['tid'])->min('id');

                if ($pst['pid'] < $minId) {// on met à jour le topic avec le nouveau 1er post
                    $request = ['t.poster' => DB::raw('p.poster'),
                        't.posted' => DB::raw('p.posted'),
                        't.num_replies' => DB::raw('t.num_replies - 1')];
                    DB::table('frm_posts AS p')->join('frm_topics AS t', 't.id', 'p.topic_id')
                            ->where('t.id', $tpc['tid'])->where('p.id', $minId)
                            ->update($request);
                } else {
                    FrmTopic::where('id', $tpc['tid'])->decrement('num_replies');
                }
            }
            //on regarde si c'était le dernier message du forum
            if ($tpc['frm_last_post_id'] == $pst['pid']) {
                $request = ['f.last_post_id' => DB::raw('f.num_topics - 1'),
                    'f.num_posts' => DB::raw('f.num_posts - 1'),
                    'f.last_post' => DB::raw('t.last_post'),
                    'f.last_post_id' => DB::raw('t.last_post_id'),
                    'f.last_poster' => DB::raw('t.last_poster'),
                    'f.last_subject' => DB::raw('t.subject')];
                DB::table('frm_forums AS f')->leftJoin('frm_topics AS t', 'f.id', 't.forum_id')
                        ->where('f.id', $tpc['forum_id'])->whereRaw('(t.id = ('
                                . 'SELECT max(id) FROM ' . DB::getTablePrefix() . 'topics '
                                . 'WHERE forum_id = ?) OR t.id IS NULL)', $tpc['forum_id'])
                        ->update($request);
            } else {
                Frm::where('id', $tpc['forum_id'])->decrement('num_posts');
            }

            return false;
        }
    }

    // construire la liste des résultats pour l'auteur recherché
    static function searchFrom(string $author) {
        return FrmPost::select('id')->whereIn('poster_id', function($query) use($author) {
                    $query->select('mbr_mid')->from('mbr')->where('mbr_pseudo', 'LIKE', $author);
                })->get()->toArray();
    }

}
