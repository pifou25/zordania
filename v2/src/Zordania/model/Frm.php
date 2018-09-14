<?php

/**
 * forums et catégories
 * lien avec les membres
 * unt_lid = leg_id
 */
class Frm extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'frm_forums';

    /* catégories & forums triés */

    static function getCat(int $cid = 0, int $fid = 0, bool $mbr = false) {
        $result = [];
        foreach (Frm::get($cid, $fid, $mbr) as $value) {
            if (!isset($result[$value['cid']]))
                $result[$value['cid']] = ['cat_name' => $value['cat_name']];
            $result[$value['cid']]['frm'][] = $value;
        }
        return $result;
    }

    /* catégories et forums */

    static function get(int $cid = 0, int $fid = 0, bool $mbr = false) {

        $sql = 'zrd_c.id AS cid, cat_name, zrd_f.id AS fid, forum_name, forum_desc, 
	redirect_url, num_topics, num_posts, _UDATE_FORMAT(last_post) AS last_post, last_post AS last_post_unformat, 
        last_post_id, last_poster, last_subject, sort_by,
	IFNULL(read_forum, 1) AS read_forum, IFNULL(post_replies, 1) AS post_replies, IFNULL(post_topics, 1) AS post_topics ';
        if ($mbr)
            $sql .= ', mbr_mid, mbr_gid ';
        // replace date formatting:
        $sql = session::$SES->parseQuery($sql);

        $req = Frm::selectRaw($sql)->from('frm_forums AS f')
                ->join('frm_categories AS c', 'f.cat_id', 'c.id')
                ->leftJoin('frm_forum_perms AS fp', function($join) {
            $join->on('f.id', 'fp.forum_id')->where('fp.group_id', Session::$SES->get('groupe'));
        });
        if ($mbr) {
            $req->leftJoin('frm_posts AS p', 'p.id', 'f.last_post_id')
                    ->leftJoin('mbr AS m', 'm.mbr_mid', 'p.poster_id');
        }
        $req->whereRaw('(read_forum IS NULL OR read_forum=1)');
        if ($cid) {
            $req->where('c.id', $cid);
        }
        if ($fid) {
            $req->where('f.id', $fid);
        }
        $req->orderBy('c.disp_position', 'asc')->orderBy('c.id', 'asc')->orderBy('f.disp_position', 'asc');
        return $req->get()->toArray();
    }



}
