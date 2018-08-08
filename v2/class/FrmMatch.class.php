<?php

/**
 * forums : lien post / mot / topic
 */
class FrmMatch extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'frm_search_matches';

//
// Strip search index of indexed words in $post_ids
//
    static function index($post_ids) {

        $result = FrmMatch::select('word_id')->whereIn('post_id', $post_ids)->distinct()->get()->toArray();

        if (!empty($result)) {
            $word_ids = '';
            foreach ($result as $row)
                $word_ids .= ($word_ids != '') ? ',' . $row['word_id'] : $row['word_id'];

            $result = FrmMatch::select('word_id')->whereIn('word_id', $word_ids)
                            ->groupBy('word_id')->havingRaw('COUNT(word_id) = ?', 1)->get()->toArray();

            if (!empty($result)) {
                $word_ids = '';
                foreach ($result as $row)
                    $word_ids .= ($word_ids != '') ? ',' . $row['word_id'] : $row['word_id'];
                FrmWord::whereIn('id', $word_ids)->delete();
            }
        }
        FrmMatch::whereIn('post_id', $post_ids)->delete();
    }

}
