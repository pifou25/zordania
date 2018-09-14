<?php

class parser {
    /* Smileys */

// Tous les Smileys
    const SMILEY = [
        's01.png' => array(':)', ':-)'),
        's02.png' => ':p',
        's03.png' => ':??:',
        's04.png' => ':!!:',
        's05.png' => ':garde:',
        's06.png' => array('^^', '^_^'),
        's07.png' => ':&gt;&lt;:',
        's08.png' => ':chevalier:',
        's09.png' => array(';)', ';o)'),
        's10.png' => array(':/', ':|'),
        's11.png' => array(':-°', ':-*'),
        's12.png' => array(':(', ':\'('),
        's13.png' => array('&lt;_&lt;', '-_-'),
        's14.png' => ':D',
        's15.png' => ':lol:',
        's16.png' => ':humain:',
        's17.png' => ':nain:',
        's18.png' => ':orc:',
        's19.png' => ':elfe:',
        's20.png' => ':drow:',
        's26.png' => ':gob:',
        's21.png' => ':love:',
        's22.png' => ':songeur:',
        's23.png' => ':gné:',
        's24.png' => 'Oo',
        's25.png' => ':o:',
        's27.png' => ':zord:',
    ];
// Les images des Smileys de base
    const SMILEY_BASE = [
        's01.png',
        's02.png',
        's03.png',
        's06.png',
        's08.png',
        's09.png',
        's12.png',
    ];

    /* parser bbcode vers HTML et vice versa */

    static public function parse(string $txt, bool $light = false, bool $html = false) {

        if (!$txt)
            return "";

        if (!$html)
            $txt = htmlspecialchars($txt);

        if (!$light)
            $txt = preg_replace_callback("#\[(code)\](.+?)\[/code\]#is", function($m) {
                return self::regex_code_tag($m[2], $m[1]);
            }, $txt);

        $txt = preg_replace_callback("#(^|\s)((http|https|news|ftp)://\w+[^\s\[\]]+)#i", function($m) {
            return self::regex_build_url(array('html' => $m[2], 'show' => $m[2], 'st' => $m[1]));
        }, $txt);

        $txt = preg_replace_callback("#\[url\](\S+?)\[/url\]#i", function($m) {
            return self::regex_build_url(array('html' => $m[1], 'show' => $m[1]));
        }, $txt);

        $txt = preg_replace_callback("#\[url\s*=\s*\&quot\;\s*(\S+?)\s*\&quot\;\s*\](.*?)\[\/url\]#i", function($m) {
            return self::regex_build_url(array('html' => $m[1], 'show' => $m[2]));
        }, $txt);

        $txt = preg_replace_callback("#\[url\s*=\s*(\S+?)\s*\](.*?)\[\/url\]#i", function($m) {
            return self::regex_build_url(array('html' => $m[1], 'show' => $m[2]));
        }, $txt);

        $txt = preg_replace("/\[b\](.*?)\[\/b\]/si", "<strong>$1</strong>", $txt);
        $txt = preg_replace("/\[i\](.*?)\[\/i\]/si", "<em>$1</em>", $txt);
        $txt = preg_replace("/\[u\](.*?)\[\/u\]/si", "<span class=\"souligne\">$1</span>", $txt);
        $txt = preg_replace("/\[center\](.*?)\[\/center\]/si", "<span class=\"center\">$1</span>", $txt);

        if (!$light) {
            $txt = preg_replace("#\[modo\](.*?)\[/modo\]#is", "[color=#1E90FF]\\1[/color]", $txt);

            while (preg_match("#\[color=([^\]]+)\](.+?)\[/color\]#ies", $txt))
                $txt = preg_replace_callback("#\[color=([^\]]+)\](.+?)\[/color\]#is", function($m) {
                    return self::regex_font_attr(array('s' => 'col', '1' => $m[1], '2' => $m[2]));
                }, $txt);

            while (preg_match("#\n?\[list\](.+?)\[/list\]\n?#ies", $txt))
                $txt = preg_replace_callback("#\n?\[list\](.+?)\[/list\]\n?#is", function($m) {
                    return self::regex_list($m[1]);
                }, $txt);

            while (preg_match("#\n?\[list=(a|A|i|I|1)\](.+?)\[/list\]\n?#ies", $txt))
                $txt = preg_replace_callback("#\n?\[list=(a|A|i|I|1)\](.+?)\[/list\]\n?#is", function($m) {
                    return self::regex_list($m[2], $m[1]);
                }, $txt);

            $txt = preg_replace_callback("#\[quote=(.*?)\](.*?)\[/quote\]#is", function($m) {
                return self::regex_parse_quotes($m[2], $m[1]);
            }, $txt);

            $txt = preg_replace_callback("#\[quote\](.*?)\[/quote\]#is", function($m) {
                return self::regex_parse_quotes($m[1]);
            }, $txt);

            $txt = preg_replace_callback("#\[img\](.+?)\[/img\]#i", function($m) {
                return self::regex_check_image($m[1]);
            }, $txt);


            $txt = preg_replace("#\[!\](.*?)\[/!\]#is", "[color=#FFC800]\\1[/color]", $txt);
        }

        foreach (self::SMILEY as $file => $sign) {
            if (is_array($sign)) {
                foreach ($sign as $val) {
                    $txt = str_replace(" " . $val, ' <img alt="' . $val . '" src="img/smile/' . $file . '" />', $txt);
                }
            } else {
                $txt = str_replace(" " . $sign, ' <img alt="' . $sign . '" src="img/smile/' . $file . '" />', $txt);
            }
        }

        $txt = nl2br($txt);
        $txt .= " ";

        return $txt;
    }

    static public function unparse(string $txt, bool $clean = false) {
        if (!$txt)
            return "";

        $txt = preg_replace("#<div class=\"quote\"><strong>(.*?)</strong><br/>(.*?)</div>#s", "[QUOTE=\\1]\\2[/QUOTE]", $txt);
        $txt = preg_replace("#<div class=\"quote\">(.*?)</div>#s", "[QUOTE]\\1[/QUOTE]", $txt);

        $txt = preg_replace("#<div class=\"code\"><strong>Code</strong><br/>(.*?)</div>#s", "[CODE]\\1[/CODE]", $txt);

        $txt = preg_replace("/<strong>(.*?)\<\/strong>/si", "[b]$1[/b]", $txt);
        $txt = preg_replace("/<em>(.*?)\<\/em>/si", "[i]$1[/i]", $txt);
        $txt = preg_replace("/<span class=\"souligne\">(.*?)\<\/span>/si", "[u]$1[/u]", $txt);
        $txt = preg_replace("/<span class=\"center\">(.*?)\<\/span>/si", "[center]$1[/center]", $txt);
        $txt = preg_replace("#(\n){0,}<ul>#", "\\1[LIST]", $txt);
        $txt = preg_replace("#(\n){0,}<ol type=\"(a|A|i|I|1)\">#", "\\1[LIST=\\2]\n", $txt);
        $txt = preg_replace("#(\n){0,}<li>#", "\n[*]", $txt);
        $txt = preg_replace("#(\n){0,}</ul>(\n){0,}#", "\n[/LIST]\\2", $txt);
        $txt = preg_replace("#(\n){0,}</ol>(\n){0,}#", "\n[/LIST]\\2", $txt);
        $txt = preg_replace("#<a href=[\"'](http://|https://|ftp://|news://)?(\S+?)['\"]>(.+?)</a>#", "[URL=\\1\\2]\\3[/URL]", $txt);
        $txt = str_replace("</li>", "", $txt);
        $txt = preg_replace("#<img src=[\"'](\S+?)['\"].+?" . ">#", "[IMG]\\1[/IMG]", $txt);

        while (preg_match("#<span style=['\"]color:(.+?)['\"]>(.+?)</span>#is", $txt))
            $txt = preg_replace("#<span style=['\"]color:(.+?)['\"]>(.+?)</span>#is", "[color=\\1]\\2[/color]", $txt);

        $txt = str_replace("<br />", "", $txt);
        $txt = str_replace("\n\n", "", $txt);

        foreach (self::SMILEY as $file => $sign) {
            if (is_array($sign)) {
                foreach ($sign as $val) {
                    $txt = str_replace('<img alt="' . $val . '" src="img/smile/' . $file . '" />', $val, $txt);
                }
            } else {
                $txt = str_replace('<img alt="' . $sign . '" src="img/smile/' . $file . '" />', $sign, $txt);
            }
        }

        // clean pour nettoyer d'autres balises HTML
        if ($clean) {
            $tag = array('#<p>#', '#</p>#', '#<div(.*)>#si', '#</div>#');
            $txt = preg_replace($tag, '', $txt);
        }

        return trim($txt);
    }

    static private function regex_build_url(array $url = []) {
        $skip_it = 0;
        if (!isset($url['st']))
            $url['st'] = "";
        if (!isset($url['end']))
            $url['end'] = "";

        /* Le dernier charactere ne doit pas être une ponctuation */
        if (preg_match("/([\.,\?]|&#33;)$/", $url['html'], $match)) {
            $url['end'] .= $match[1];
            $url['html'] = preg_replace("/([\.,\?]|&#33;)$/", "", $url['html']);
            $url['show'] = preg_replace("/([\.,\?]|&#33;)$/", "", $url['show']);
        }

        /* Ne doit pas être utilisé n'importe ou */
        if (preg_match("/\[\/(quote|code)/i", $url['html']))
            return $url['html'];

        /* Nettoyage */
        $url['html'] = str_replace("&amp;", "&", $url['html']);
        $url['html'] = str_replace("[", "%5b", $url['html']);
        $url['html'] = str_replace("]", "%5d", $url['html']);

        /* Le javascript, çasux */
        $url['html'] = preg_replace("/javascript:/i", "java script&#58; ", $url['html']);

        /* On n'aurise pas n'importe quoi */
        if (!preg_match("#^(http|news|https|ftp|irc)://#", $url['html']))
            $url['html'] = 'http://' . $url['html'];

        /* Affichage nettoyé */
        if (preg_match("/^<img src/i", $url['show']))
            $skip_it = 1;

        $url['show'] = preg_replace("/&amp;/", "&", $url['show']);
        $url['show'] = preg_replace("/javascript:/i", "javascript&#58; ", $url['show']);

        /* Pas de trop gros truc */
        if ((strlen($url['show']) - 58 ) < 3)
            $skip_it = 1;

        /* url propre ? */
        if (!preg_match("/^(http|ftp|https|news):\/\//i", $url['show']))
            $skip_it = 1;

        $show = $url['show'];

        if (!$skip_it) {
            $stripped = preg_replace("#^(http|ftp|https|news|irc)://(\S+)$#i", "\\2", $url['show']);
            $uri_type = preg_replace("#^(http|ftp|https|news|irc)://(\S+)$#i", "\\1", $url['show']);

            $show = $uri_type . '://' . substr($stripped, 0, 35) . '...' . substr($stripped, -15);
        }

        return $url['st'] . "<a href=\"" . $url['html'] . "\">" . $show . "</a>" . $url['end'];
    }

    static private function regex_list($txt = "", $type = "") {
        if (!$txt)
            return;
        $txt = stripslashes($txt);

        if (!$type)
            return "<ul>" . self::regex_list_item($txt) . "</ul>";
        else
            return "<ol type=\"$type\">" . self::regex_list_item($txt) . "</ol>";
    }

    static private function regex_list_item($txt) {
        $txt = preg_replace("#\[\*\]#", "</li><li>", trim($txt));

        $txt = preg_replace("#^</?li>#", "", $txt);

        return str_replace("\n</li>", "</li>", $txt . "</li>");
    }

    static private function regex_check_image($url = "") {

        if (!$url)
            return;

        $url = trim($url);

        $default = "[img]" . $url . "[/img]";

        $ext = array("gif", "bmp", "png", "jpg");
        $proto = array("http", "ftp", "https");

        // Normal url ?
        $url_array = @parse_url($url);
        if (!$url_array)
            return $default;
        if (!isset($url_array['scheme']) || !in_array($url_array['scheme'], $proto))
            return $default;

        // Is the img extension allowed to be posted?		
        $file = basename($url_array['path']);
        $extension = explode(".", $file);
        $extension = $extension[count($extension) - 1];

        if (!in_array($extension, $ext))
            return $default;

        // If we are still here....
        $url = $url_array['scheme'] . '://' . $url_array['host'] . $url_array['path'];
        $url = str_replace(" ", "%20", $url);

        return "<img src=\"$url\" alt=\"Image Postée\"/>";
    }

    static private function regex_parse_quotes($txt = "", $from = "") {
        $txt = stripslashes($txt);
        $default = "\[quote\]$txt\[/quote\]";

        if (!$txt)
            return;

        if (preg_match("/\[(quote|code)\].+?\[(quote|code)\].+?\[(quote|code)\].+?\[(quote|code)\].+?\[(quote|code)\].+?\[(quote|code)\].+?\[(quote|code)\]/i", $txt))
            return $default;

        $return = '<div class="quote">';
        if ($from)
            $return .= '<strong>' . $from . '</strong><br/>';

        $return .= $txt;
        $return .= '</div>';

        return $return;
    }

    static private function regex_code_tag($txt = "", $code = 'code') {
        $txt = stripslashes($txt);
        $default = "\[$code\]$txt\[/$code\]";

        if (!$txt)
            return;

        if (preg_match("/\[(quote|code)\].+?\[(quote|code)\].+?\[(quote|code)\].+?\[(quote|code)\].+?\[(quote|code)\].+?\[(quote|code)\].+?\[(quote|code)\]/i", $txt))
            return $default;

        //$txt = str_replace( "&" , "&amp;", $txt );
        $txt = preg_replace("#&lt;#", "&#60;", $txt);
        $txt = preg_replace("#&gt;#", "&#62;", $txt);
        $txt = preg_replace("#&quot;#", "&#34;", $txt);
        $txt = preg_replace("#:#", "&#58;", $txt);
        $txt = preg_replace("#\[#", "&#91;", $txt);
        $txt = preg_replace("#\]#", "&#93;", $txt);
        $txt = preg_replace("#\)#", "&#41;", $txt);
        $txt = preg_replace("#\(#", "&#40;", $txt);
        //$txt = preg_replace( "#\r#"     , "<br />", $txt );
        //$txt = preg_replace( "#\n#"     , "<br />", $txt );
        $txt = preg_replace("#\s{1};#", "&#59;", $txt);

        //$txt = preg_replace( "#\s{2}#", " &nbsp;", $txt );

        $return = '<div class="code">';
        $return .= '<strong>Code</strong><br/>';
        $return .= $txt;
        $return .= '</div>';

        return $return;
    }

    static private function regex_font_attr($in) {
        if (!is_array($in) || !$in)
            return "";


        if (preg_match("/;/", $in['1'])) {
            $attr = explode(";", stripslashes($in['1']));
            $in['1'] = $attr[0];
        }

        $in['1'] = preg_replace("/[&\(\)\.\%]/", "", $in['1']);

        if ($in['s'] == 'size') {
            $in['1'] = $in['1'] + 7;

            if ($in['1'] > 30)
                $in['1'] = 30;

            return "<span style=\"font-size:" . $in['1'] . "pt;line-height:100%\">" . stripslashes($in['2']) . "</span>";
        } else if ($in['s'] == 'col')
            return "<span style=\"color:" . $in['1'] . "\">" . stripslashes($in['2']) . "</span>";
        else if ($in['s'] == 'font')
            return "<span style=\"font-family:" . $in['1'] . "\">" . stripslashes($in['2']) . "</span>";
    }

    static public function getSmileysBase() {

        $smileys_base = array();
        foreach (self::SMILEY_BASE as $img) {
            $smileys_base[$img] = (!is_array(self::SMILEY[$img])) ? self::SMILEY[$img] : self::SMILEY[$img][0];
        }
        return $smileys_base;
    }

    static public function getSmileysMore() {

        $smileys_more = array();
        $smileys_base = parser::getSmileysBase();

        foreach (self::SMILEY as $img => $val) {
            if (is_array($val)) {
                $smileys_more[$img] = $val[0];
            } else {
                $smileys_more[$img] = $val;
            }
        }
        foreach ($smileys_base as $key => $val) {
            unset($smileys_more[$key]);
        }
        return $smileys_more;
    }

}
