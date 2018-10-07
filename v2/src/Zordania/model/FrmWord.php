<?php

/**
 * forums : index des mots "recherchable"
 */
class FrmWord extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'frm_search_words';

// construire la liste des résultats pour les mots clés recherchés
    static
            function search_keywords_results($keywords, $search_in) {

        // filtrer caractères non alphabétiques
        $noise_match = array('^', '$', '&', '(', ')', '<', '>', '`', '\'', '"', '|', ',', '@', '_', '?', '%', '~', '[', ']', '{', '}', ':', '\\', '/', '=', '#', '\'', ';', '!');

        $noise_replace = array(' ', ' ', ' ', ' ', ' ', ' ', ' ', '', '', ' ', ' ', ' ', ' ', '', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '', ' ', ' ', ' ', ' ', ' ', ' ');
        $keywords = str_replace($noise_match, $noise_replace, $keywords);

        // supprimer espaces multiples
        $keywords = trim(preg_replace('#\s+#', ' ', $keywords));

        // remplir un tableau des mots clés
        $keywords_array = explode(' ', $keywords);

        if (empty($keywords_array)) {
            $_tpl->set('no_hits', true);
            return false;
        }

        foreach ($keywords_array as $i => $word) {
            $num_chars = strlen($word);
            if ($num_chars < 3 || $num_chars > 20 || in_array($word, self::getStopWords()))
                unset($keywords_array[$i]);
        }

        // recherche dans le texte ou uniquement le sujet ?
        $search_in_cond = ($search_in) ? (($search_in > 0) ? ' AND m.subject_match = 0' : ' AND m.subject_match = 1') : '';

        $word_count = 0;
        $match_type = 'and';
        $result_list = array();
        @reset($keywords_array);
        while (list(, $cur_word) = @each($keywords_array)) {
            switch ($cur_word) {
                case 'and':
                case 'or':
                case 'not':
                    $match_type = $cur_word;
                    break;

                default: {
                        $cur_word = str_replace('*', '%', $cur_word);
                        $req = FrmWord::select('m.post_id')->table('frm_search_words AS w')
                                ->join('frm_search_matches AS m', 'm.word_id', 'w.id')
                                ->where('word', 'LIKE', $cur_word);
                        if ($search_in) {
                            $req->where('m.subject_match', ($search_in > 0 ? 0 : 1));
                        }

                        $result = $req->get()->toArray();

                        $row = array();
                        foreach ($result as $temp) {
                            $row[$temp['post_id']] = 1;

                            if (!$word_count)
                                $result_list[$temp['post_id']] = 1;
                            else if ($match_type == 'or')
                                $result_list[$temp['post_id']] = 1;
                            else if ($match_type == 'not')
                                $result_list[$temp['post_id']] = 0;
                        }

                        if ($match_type == 'and' && $word_count) {
                            @reset($result_list);
                            foreach ($result_list as $post_id => $value)
                                if (!isset($row[$post_id]))
                                    $result_list[$post_id] = 0;
                        }

                        ++$word_count;
                        break;
                    }
            }// fin switch
        }// fin foreach

        @reset($result_list);
        $keyword_results = array();
        while (list($post_id, $matches) = @each($result_list))
            if ($matches)
                $keyword_results[] = $post_id;

        return $keyword_results;
    }

    /**
     * "Cleans up" a text string and returns an array of unique words
     * This function depends on the current locale setting
     * @staticvar string $noise_match
     * @staticvar string $noise_replace
     * @param string $text
     * @return array
     */
    static function split(string $text): array {
        static $noise_match, $noise_replace;

        if (empty($noise_match)) {
            $noise_match = array('[quote', '[code', '[url', '[img', '[email', '[color', '[colour', 'quote]', 'code]', 'url]', 'img]', 'email]', 'color]', 'colour]', '^', '$', '&', '(', ')', '<', '>', '`', '\'', '"', '|', ',', '@', '_', '?', '%', '~', '+', '[', ']', '{', '}', ':', '\\', '/', '=', '#', ';', '!', '*');
            $noise_replace = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '', '', ' ', ' ', ' ', ' ', '', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '', ' ', ' ', ' ', ' ', ' ', ' ');
        }

	// Clean up
	$patterns[] = '#&[\#a-z0-9]+?;#ui';
	$patterns[] = '#\b[\w]+:\/\/[a-z0-9\.\-]+(\/[a-z0-9\?\.%_\-\+=&\/~]+)?#u';
	$patterns[] = '#\[\/?[a-z\*=\+\-]+(\:?[0-9a-z]+)?:[a-z0-9]{10,}(\:[a-z0-9]+)?=?.*?\]#u';
	$text = preg_replace($patterns, ' ', ' '.strtolower($text).' ');

	// Filter out junk
	$text = str_replace($noise_match, $noise_replace, $text);

	// Strip out extra whitespace between words
	$text = trim(preg_replace('#\s+#u', ' ', $text));

        // Fill an array with all the words
        $words = explode(' ', $text);

        if (!empty($words)) {
            while (list($i, $word) = @each($words)) {
                $words[$i] = trim($word, '.');
                $num_chars = strlen($word);

                if ($num_chars < 3 || $num_chars > 20 || in_array($word, self::getStopWords()))
                    unset($words[$i]);
            }
        }

        return array_unique($words);
    }

    private static function getStopWords(){
        global $_tpl;
        static $stopwords;
        if($stopwords == null){
            $stopwords = (array) file($_tpl->var->tpl->dir2 . $_tpl->var->tpl->dir . $_tpl->var->tpl->lang . '/modules/forum/stopwords.txt');
            $stopwords = array_map('trim', $stopwords);
        }
        return $stopwords;
    }
    
//
// Updates the search index with the contents of $post_id (and $subject)
//
    static function index(string $mode, int $post_id, string $message, $subject = null) {
        // Split old and new post/subject to obtain array of 'words'
        $words_message = FrmWord::split($message);
        $words_subject = ($subject) ? FrmWord::split($subject) : array();

        if ($mode == 'edit') {
            $result = FrmWord::from('frm_search_words AS w')
                            ->join('frm_search_matches AS m', 'w.id', 'm.subject_match')
                            ->where('m.post_id', $post_id)->get()->toArray();

            // Declare here to stop array_keys() and array_diff() from complaining if not set
            $cur_words = ['post' => [], 'subject' => []];

            foreach ($result as $row) {
                $match_in = ($row['subject_match']) ? 'subject' : 'post';
                $cur_words[$match_in][$row['word']] = $row['id'];
            }

            $words['add']['post'] = array_diff($words_message, array_keys($cur_words['post']));
            $words['add']['subject'] = array_diff($words_subject, array_keys($cur_words['subject']));
            $words['del']['post'] = array_diff(array_keys($cur_words['post']), $words_message);
            $words['del']['subject'] = array_diff(array_keys($cur_words['subject']), $words_subject);
        } else {
            $words['add']['post'] = $words_message;
            $words['add']['subject'] = $words_subject;
            $words['del']['post'] = array();
            $words['del']['subject'] = array();
        }

        unset($words_message);
        unset($words_subject);

        // Get unique words from the above arrays
        $unique_words = array_unique(array_merge($words['add']['post'], $words['add']['subject']));

        if (!empty($unique_words)) {
            $result = FrmWord::whereIn('word', preg_replace('#^(.*)$#', '\'\1\'', $unique_words))
                            ->get()->toArray();

            $word_ids = [];
            foreach ($result as $row)
                $word_ids[$row['word']] = $row['id'];

            $new_words = array_diff($unique_words, array_keys($word_ids));
            unset($unique_words);

            if (!empty($new_words)) {
                $insert = preg_replace('#^(.*)$#', '(\'\1\')', $new_words);
                foreach ($insert as $value)
                    $request[] = ['word' => $value];
                FrmWord::insert($request);
            }

            unset($new_words);
        }

        // Delete matches (only if editing a post)
        while (list($match_in, $wordlist) = @each($words['del'])) {
            $subject_match = ($match_in == 'subject') ? 1 : 0;

            if (!empty($wordlist)) {
                while (list(, $word) = @each($wordlist))
                    $del[] = $cur_words[$match_in][$word];

                FrmMatch::where('post_id', $post_id)
                        ->where('subject_match', $subject_match)
                        ->whereIn('word_id', $del)->delete();
            }
        }

        // Add new matches
        $sql = 'INSERT INTO ' . DB::getTablePrefix() .
                'frm_search_matches (post_id, word_id, subject_match) SELECT ?' .
                ', id, ? FROM ' . DB::getTablePrefix() .
                'frm_search_words WHERE word IN(?)';
        while (list($match_in, $wordlist) = @each($words['add'])) {
            $subject_match = ($match_in == 'subject') ? 1 : 0;

            if (!empty($wordlist)) {
                DB::insert($sql, [$post_id, $subject_match,
                    preg_replace('#^(.*)$#', '\'\1\'', $wordlist)]);
            }
        }

        unset($words);
    }

}
