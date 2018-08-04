<?php



function maj_all($pid,$pseudo,$tid,$fid,$topic,$mid,$subject,$msg)
{// MAJ suite à ajout d'un topic ou post (pas suite à edit ni delete)
	global $_sql;
	$pseudo = protect($pseudo, 'string');
	$pid = protect($pid, 'uint');
	$mid = protect($mid, 'uint');
	$tid = protect($tid, 'uint');
	$subject = protect($subject, 'string');

	//maj topics
	$sql = "UPDATE ".MYSQL_PREBDD_FRM."topics SET last_post = UNIX_TIMESTAMP(), last_post_id = '$pid', last_poster = '$pseudo' ";
	if(!$topic)
		$sql .= ", num_replies=num_replies+1 ";
	$sql .= "WHERE id = '$tid'";
	$_sql->query($sql);

	//maj forums
	$sql = "UPDATE ".MYSQL_PREBDD_FRM."forums SET num_posts = num_posts + 1, last_post = UNIX_TIMESTAMP(), ";
	if($topic)
		$sql .= " num_topics = num_topics + 1, ";
	$sql .= "last_post_id = '$pid', last_poster = '$pseudo', last_subject = '$subject' WHERE id = '$fid'";
	$_sql->query($sql);

	//maj users
	//$sql = "UPDATE ".MYSQL_PREBDD_FRM."users SET num_posts = num_posts + 1, last_post = '$pid' WHERE id = '$mid'";
	//$_sql->query($sql);

	//maj indexation recherche
	if($topic)
		update_search_index('edit', $pid, $msg, $subject);
	else
		update_search_index('edit', $pid, $msg);

}






/***********************************/
/***  FONCTIONS  DE  RECHERCHE   ***/
/***********************************/

/***********************************************************************
  This file is part of PunBB.
  
 The contents of this file are very much inspired by the file functions_search.php
 from the phpBB Group forum software phpBB2 (http://www.phpbb.com).

 Now modified to work with zordania :p 
************************************************************************/


function search_user_id($id){// retrouver une recherche en cache
	global $_sql, $_user;
	$id = protect($id, 'uint');

	$row = $_sql->make_array('SELECT search_data FROM '.MYSQL_PREBDD_FRM.'search_cache 
	WHERE id='.$id.' AND ident=\''.protect($_user['pseudo'], 'string').'\'');
	if (!empty($row))
		return unserialize($row[0]['search_data']);
	else
		return false;
}

function add_search_user($search, $ident){// ajouter la recherche dans le cache
	global $_sql;
	// vider le cache des anciennes recherches
	//$_sql->query('DELETE FROM '. MYSQL_PREBDD_FRM .'search_cache WHERE ident NOT IN(SELECT ident FROM '. MYSQL_PREBDD_FRM .'online)');
	$_sql->query('DELETE FROM '. MYSQL_PREBDD_FRM .'search_cache WHERE ident NOT IN(SELECT ses_mid FROM '. MYSQL_PREBDD .'ses WHERE ses_mid <> 1)');

	$search_id = mt_rand(1, 2147483647);
	$_sql->query('INSERT INTO '.MYSQL_PREBDD_FRM.'search_cache (id, ident, search_data) 
	VALUES('.$search_id.', \''.protect($ident, 'string').'\', \''.addslashes(serialize($search)).'\')');
	return $search_id;
}


// construire la liste des résultats pour les mots clés recherchés
function search_keywords_results($keywords, $search_in){
	global $_sql, $_tpl;

	$stopwords = (array)file($_tpl->var->tpl->dir2.$_tpl->var->tpl->dir.$_tpl->var->tpl->lang.'/modules/forum/stopwords.txt');
	$stopwords = array_map('trim', $stopwords);

	// filtrer caractères non alphabétiques
	$noise_match = array('^', '$', '&', '(', ')', '<', '>', '`', '\'', '"', '|', ',', '@', '_', '?', '%', '~', '[', ']', '{', '}', ':', '\\', '/', '=', '#', '\'', ';', '!');

	$noise_replace = array(' ', ' ', ' ', ' ', ' ', ' ', ' ', '',  '',   ' ', ' ', ' ', ' ', '',  ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '' ,  ' ', ' ', ' ', ' ',  ' ', ' ');
	$keywords = str_replace($noise_match, $noise_replace, $keywords);

	// supprimer espaces multiples
	$keywords = trim(preg_replace('#\s+#', ' ', $keywords));

	// remplir un tableau des mots clés
	$keywords_array = explode(' ', $keywords);

	if (empty($keywords_array))
		{$_tpl->set('no_hits', true); return false;}

	foreach($keywords_array as $i => $word)
	{
		$num_chars = strlen($word);
		if ($num_chars < 3 || $num_chars > 20 || in_array($word, $stopwords))
			unset($keywords_array[$i]);
	}

	// recherche dans le texte ou uniquement le sujet ?
	$search_in_cond = ($search_in) ? (($search_in > 0) ? ' AND m.subject_match = 0' : ' AND m.subject_match = 1') : '';

	$word_count = 0;
	$match_type = 'and';
	$result_list = array();
	@reset($keywords_array);
	while (list(, $cur_word) = @each($keywords_array))
	{
		switch ($cur_word)
		{
			case 'and':
			case 'or':
			case 'not':
				$match_type = $cur_word;
				break;

			default:
			{
				$cur_word = str_replace('*', '%', $cur_word);
				$sql = 'SELECT m.post_id FROM '.MYSQL_PREBDD_FRM.'search_words AS w INNER JOIN '.MYSQL_PREBDD_FRM.'search_matches AS m ON m.word_id = w.id WHERE w.word LIKE \''.$cur_word.'\''.$search_in_cond;

				$result = $_sql->make_array($sql);

				$row = array();
				foreach($result as $key => $temp)
				{
					$row[$temp['post_id']] = 1;

					if (!$word_count)
						$result_list[$temp['post_id']] = 1;
					else if ($match_type == 'or')
						$result_list[$temp['post_id']] = 1;
					else if ($match_type == 'not')
						$result_list[$temp['post_id']] = 0;
				}

				if ($match_type == 'and' && $word_count)
				{
					@reset($result_list);
					foreach($result_list as $post_id => $value)
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

// construire la liste des résultats pour l'auteur recherché
function search_author_results($author){
	global $_sql;
	$author = protect($author, 'string');

	$result = $_sql->make_array('SELECT id FROM '.MYSQL_PREBDD_FRM.'posts 
	WHERE poster_id IN(SELECT id 
			FROM '.MYSQL_PREBDD_FRM.'users WHERE username LIKE \''.$author.'\')');

	$search_ids = array();
	foreach ($result as $row)
		$author_results[] = $row['id'];
	return $author_results;
}


//
// "Cleans up" a text string and returns an array of unique words
// This function depends on the current locale setting
//
function split_words($text)
{
	global $_tpl;
	static $noise_match, $noise_replace, $stopwords;

	if (empty($noise_match))
	{
		$noise_match = 		array('[quote', '[code', '[url', '[img', '[email', '[color', '[colour', 'quote]', 'code]', 'url]', 'img]', 'email]', 'color]', 'colour]', '^', '$', '&', '(', ')', '<', '>', '`', '\'', '"', '|', ',', '@', '_', '?', '%', '~', '+', '[', ']', '{', '}', ':', '\\', '/', '=', '#', ';', '!', '*');
		$noise_replace =	array('',       '',      '',     '',     '',       '',       '',        '',       '',      '',     '',     '',       '',       '',        ' ', ' ', ' ', ' ', ' ', ' ', ' ', '',  '',   ' ', ' ', ' ', ' ', '',  ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '' ,  ' ', ' ', ' ', ' ', ' ', ' ');

		$stopwords = (array)file($_tpl->var->tpl->dir2.$_tpl->var->tpl->dir.$_tpl->var->tpl->lang.'/modules/forum/stopwords.txt');
		$stopwords = array_map('trim', $stopwords);
	}

	// Clean up
	$patterns[] = '#&[\#a-z0-9]+?;#i';
	$patterns[] = '#\b[\w]+:\/\/[a-z0-9\.\-]+(\/[a-z0-9\?\.%_\-\+=&\/~]+)?#';
	$patterns[] = '#\[\/?[a-z\*=\+\-]+(\:?[0-9a-z]+)?:[a-z0-9]{10,}(\:[a-z0-9]+)?=?.*?\]#';
	$text = preg_replace($patterns, ' ', ' '.strtolower($text).' ');

	// Filter out junk
	$text = str_replace($noise_match, $noise_replace, $text);

	// Strip out extra whitespace between words
	$text = trim(preg_replace('#\s+#', ' ', $text));

	// Fill an array with all the words
	$words = explode(' ', $text);

	if (!empty($words))
	{
		while (list($i, $word) = @each($words))
		{
			$words[$i] = trim($word, '.');
			$num_chars = strlen($word);

			if ($num_chars < 3 || $num_chars > 20 || in_array($word, $stopwords))
				unset($words[$i]);
		}
	}

	return array_unique($words);
}


//
// Updates the search index with the contents of $post_id (and $subject)
//
function update_search_index($mode, $post_id, $message, $subject = null)
{
	global $_sql;

	// Split old and new post/subject to obtain array of 'words'
	$words_message = split_words($message);
	$words_subject = ($subject) ? split_words($subject) : array();

	if ($mode == 'edit')
	{
		$result = $_sql->make_array('SELECT w.id, w.word, m.subject_match FROM '.MYSQL_PREBDD_FRM.'search_words AS w INNER JOIN '.MYSQL_PREBDD_FRM.'search_matches AS m ON w.id=m.word_id WHERE m.post_id='.$post_id);

		// Declare here to stop array_keys() and array_diff() from complaining if not set
		$cur_words['post'] = array();
		$cur_words['subject'] = array();

		foreach ($result as $row)
		{
			$match_in = ($row['subject_match']) ? 'subject' : 'post';
			$cur_words[$match_in][$row['word']] = $row['id'];
		}

		$words['add']['post'] = array_diff($words_message, array_keys($cur_words['post']));
		$words['add']['subject'] = array_diff($words_subject, array_keys($cur_words['subject']));
		$words['del']['post'] = array_diff(array_keys($cur_words['post']), $words_message);
		$words['del']['subject'] = array_diff(array_keys($cur_words['subject']), $words_subject);
	}
	else
	{
		$words['add']['post'] = $words_message;
		$words['add']['subject'] = $words_subject;
		$words['del']['post'] = array();
		$words['del']['subject'] = array();
	}

	unset($words_message);
	unset($words_subject);

	// Get unique words from the above arrays
	$unique_words = array_unique(array_merge($words['add']['post'], $words['add']['subject']));

	if (!empty($unique_words))
	{
		$result = $_sql->make_array('SELECT id, word FROM '.MYSQL_PREBDD_FRM.'search_words WHERE word IN('.implode(',', preg_replace('#^(.*)$#', '\'\1\'', $unique_words)).')');

		$word_ids = array();
		foreach ($result as $row)
			$word_ids[$row['word']] = $row['id'];

		$new_words = array_diff($unique_words, array_keys($word_ids));
		unset($unique_words);

		if (!empty($new_words))
			$_sql->query('INSERT INTO '.MYSQL_PREBDD_FRM.'search_words (word) VALUES'.implode(',', preg_replace('#^(.*)$#', '(\'\1\')', $new_words)));

		unset($new_words);
	}

	// Delete matches (only if editing a post)
	while (list($match_in, $wordlist) = @each($words['del']))
	{
		$subject_match = ($match_in == 'subject') ? 1 : 0;

		if (!empty($wordlist))
		{
			$sql = '';
			while (list(, $word) = @each($wordlist))
				$sql .= (($sql != '') ? ',' : '').$cur_words[$match_in][$word];

			$_sql->query('DELETE FROM '.MYSQL_PREBDD_FRM.'search_matches WHERE word_id IN('.$sql.') AND post_id='.$post_id.' AND subject_match='.$subject_match);
		}
	}

	// Add new matches
	while (list($match_in, $wordlist) = @each($words['add']))
	{
		$subject_match = ($match_in == 'subject') ? 1 : 0;

		if (!empty($wordlist))
			$_sql->query('INSERT INTO '.MYSQL_PREBDD_FRM.'search_matches (post_id, word_id, subject_match) SELECT '.$post_id.', id, '.$subject_match.' FROM '.MYSQL_PREBDD_FRM.'search_words WHERE word IN('.implode(',', preg_replace('#^(.*)$#', '\'\1\'', $wordlist)).')');
	}

	unset($words);
}


//
// Strip search index of indexed words in $post_ids
//
function strip_search_index($post_ids)
{
	global $_sql;

	$result = $_sql->make_array('SELECT word_id FROM '.MYSQL_PREBDD_FRM.'search_matches WHERE post_id IN('.$post_ids.') GROUP BY word_id');

	if (!empty($result))
	{
		$word_ids = '';
		foreach ($result as $row)
			$word_ids .= ($word_ids != '') ? ','.$row['word_id'] : $row['word_id'];

		$result = $_sql->make_array('SELECT word_id FROM '.MYSQL_PREBDD_FRM.'search_matches WHERE word_id IN('.$word_ids.') GROUP BY word_id HAVING COUNT(word_id)=1');

		if (!empty($result))
		{
			$word_ids = '';
			foreach ($result as $row)
				$word_ids .= ($word_ids != '') ? ','.$row['word_id'] : $row['word_id'];

			$_sql->query('DELETE FROM '.MYSQL_PREBDD_FRM.'search_words WHERE id IN('.$word_ids.')');
		}
	}

	$_sql->query('DELETE FROM '.MYSQL_PREBDD_FRM.'search_matches WHERE post_id IN('.$post_ids.')');
}
//*****     FIN DES FONCTIONS dédiées à la recherche     ******

?>
