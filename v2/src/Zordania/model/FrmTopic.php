<?php

/**
 * forums : les sujets
 */
class FrmTopic extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'frm_topics';

    /* les topics du forum */

    static function get(array $cond) {
        $select = isset($cond['select']) ? protect($cond['select'], 'string') : '';
        $fid = isset($cond['fid']) ? protect($cond['fid'], 'uint') : 0;
        $tid = isset($cond['tid']) ? protect($cond['tid'], 'uint') : 0;
        $tid_list = isset($cond['tid_list']) ? protect($cond['tid_list'], 'array') : array();
        $start = isset($cond['start']) ? protect($cond['start'], 'uint') : -1;
        $limit = isset($cond['limit']) ? protect($cond['limit'], 'uint') : LIMIT_PAGE;
        $order = isset($cond['order']) ? protect($cond['order'], 'string') : false;

        if ($select == 'topic') {// optimisé: table 'topic' seulement
            // posted_unformat: pour la comparaison de date
            $sql = ' _UDATE_FORMAT(posted) AS posted, last_post AS posted_unformat, '
                    . '_UDATE_FORMAT(last_post) AS last_post, t.id AS tid, poster, subject, '
                    . 'last_post_id, last_poster, num_views, num_replies, closed, sticky, '
                    . 'forum_id, statut, report_type, id ';
            $sql = session::$SES->parseQuery($sql);

            $req = FrmTopic::selectRaw($sql);
        } else {
            $sql = ' _UDATE_FORMAT(' . DB::getTablePrefix() . 't.posted) AS posted, ' . DB::getTablePrefix() . 't.last_post AS posted_unformat, '
                    . '_UDATE_FORMAT(' . DB::getTablePrefix() . 't.last_post) AS last_post, ' . DB::getTablePrefix() . 't.id AS tid, ' . DB::getTablePrefix() . 't.poster, subject, '
                    . DB::getTablePrefix() . 't.last_post_id, ' . DB::getTablePrefix() . 't.last_poster, num_views, num_replies, num_replies, '
                    . 'closed, sticky, moved_to, ' . DB::getTablePrefix() . 't.forum_id, statut, report_type,'
                    . DB::getTablePrefix() . 't.id, forum_name, ' . DB::getTablePrefix() . 'f.last_post_id AS frm_last_post_id, ' . DB::getTablePrefix() . 'c.id AS cid, cat_name, '
                    . 'IFNULL(read_forum, 1) AS read_forum, IFNULL(post_replies, 1) AS post_replies, '
                    . 'IFNULL(post_topics, 1) AS post_topics ';
            if ($select == 'mbr') {
                $sql .= ', ' . DB::getTablePrefix() . 'm2.mbr_mid AS auth_mid,  ' . DB::getTablePrefix() . 'm2.mbr_gid AS auth_gid, '
                        . DB::getTablePrefix() . 'm1.mbr_mid AS last_poster_mid, '
                        . DB::getTablePrefix() . 'm1.mbr_gid AS last_poster_gid ';
            } elseif ($select == 'first_pid') {// le 1er post de la discussion
                $sql .= ', (SELECT min(pp.id) FROM ' . DB::getTablePrefix() . 'frm_posts AS pp WHERE pp.topic_id = ' . DB::getTablePrefix() . 't.id) AS first_pid ';
            }
            $sql = session::$SES->parseQuery($sql);

            $req = FrmTopic::selectRaw($sql)->from('frm_topics AS t');
            $req->join('frm_forums AS f', 't.forum_id', 'f.id')
                    ->join('frm_categories AS c', 'f.cat_id', 'c.id')
                    ->leftJoin('frm_forum_perms AS fp', function($join) {
                        $join->on('f.id', 'fp.forum_id')->where('fp.group_id', Session::$SES->get('groupe'));
                    });

            if ($select == 'mbr') {// récup mid et groupe pour l'auteur et le dernier posteur
                $req->join('frm_posts AS p1', 't.last_post_id', 'p1.id')
                        ->leftJoin('mbr AS m1', 'p1.poster_id', 'm1.mbr_mid')
                        ->join('frm_posts AS p2', 'p2.id', DB::raw('(SELECT min( p3.id ) FROM ' . DB::getTablePrefix() . 'frm_posts AS p3 '
                                        . 'WHERE p3.topic_id = ' . DB::getTablePrefix() . 't.id)'))
                        ->leftJoin('mbr AS m2', 'p2.poster_id', 'm2.mbr_mid');
            }
        }

        $req->whereRaw('(read_forum IS NULL OR read_forum=1)'); // droit de lecture au minimum!
        if ($fid) {
            $req->where('t.forum_id', $fid);
        }
        if ($tid) {
            $req->where('t.id', $tid);
        } else if ($tid_list) {
            $req->whereIn('t.id', $tid_list);
        }

        if ($select != 'topic') {
            if ($order) {
                $req->orderBy('t.posted', 'desc');
            } else {
                $req->orderBy('t.sticky', 'desc')->orderBy('t.last_post', 'desc');
            }
            return $req->offset($start)->take($limit); // for paginator
        } else {
            //echo $req->toSql();
            return $req->get()->toArray();
        }
    }

    static function getInfo(int $tid, int $group) {
        $result = FrmTopic::get(['tid' => $tid, 'group' => $group])->get()->toArray();
        if ($result)
            return $result[0];
        else
            return array();
    }

    static function view(int $tid) {
        FrmTopic::where('id', $tid)->increment('num_views');
    }

    static function add(string $pseudo, string $pst_titre, int $fid, $closed = 0, $sticky = 0) {

        $request = ['poster' => $pseudo,
            'subject' => $pst_titre,
            'posted' => DB::raw('UNIX_TIMESTAMP()'),
            'last_post' => DB::raw('UNIX_TIMESTAMP()'),
            'last_poster' => $pseudo,
            'forum_id' => $fid,
            'closed' => $closed,
            'sticky' => $sticky];

        return FrmTopic::insertGetId($request);
    }

    /**
     * MAJ du topic et du forum suite à ajout d'un message
     * @param int $pid
     * @param string $pseudo
     * @param int $tid
     * @param int $fid
     * @param bool $topic
     * @param int $mid
     * @param string $subject
     * @param string $msg
     */
    static function maj(int $pid, string $pseudo, int $tid, int $fid, bool $topic, string $subject, string $msg) {// MAJ suite à ajout d'un topic ou post (pas suite à edit ni delete)
        //maj topics
        $rqt1 = ['last_post' => DB::raw('UNIX_TIMESTAMP()'),
            'last_post_id' => $pid,
            'last_poster' => $pseudo];
        if (!$topic) {
            $rqt1['num_replies'] = DB::raw('num_replies + 1');
        }
        FrmTopic::where('id', $tid)->update($rqt1);

        //maj forums
        $rqt2 = ['last_post' => DB::raw('UNIX_TIMESTAMP()'),
            'last_post_id' => $pid,
            'last_poster' => $pseudo,
            'num_posts' => DB::raw('num_posts + 1'),
            'last_subject' => $subject];
        if (!$topic) {
            $rqt2['num_topics'] = DB::raw('num_topics + 1');
        }
        Frm::where('id', $fid)->update($rqt2);

        //maj indexation recherche
        if ($topic)
            FrmWord::index('edit', $pid, $msg, $subject);
        else
            FrmWord::index('edit', $pid, $msg);
    }

}
