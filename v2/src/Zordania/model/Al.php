<?php

/**
 * Alliances
 * lien avec le chef d'alliance : al_mid = mbr_id
 * lien avec les autres membres : al_aid = al_mbr.ambr_aid
 */
class Al extends Illuminate\Database\Eloquent\Model {

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    // override table name
    protected $table = 'al';

    static function get($cond) {
        if (is_numeric($cond)) {

            /* get by $al_aid */
            $result = Al::where('al_aid', $cond)->get()->toArray();
            return (empty($result) ? false : $result[0]);
        } else if (is_array($cond)) {

            $limite1 = 0;
            $limite2 = 0;
            $limite = 0;
            $name = 0;

            if (isset($cond['limite1'])) {
                $limite1 = protect($cond['limite1'], "uint");
                $limite++;
            }
            if (isset($cond['limite2'])) {
                $limite2 = protect($cond['limite2'], "uint");
                $limite++;
            }
            if (isset($cond['name']))
                $name = protect($cond['name'], "string");

            $req = Al::select('al_aid', 'al_name', 'al_nb_mbr', 'al_mid', 'mbr_pseudo', 'mbr_race', 'mbr_gid', 'al_points', 'al_open');
            if (!$limite)
                $req->addSelect('al_descr,al_rules');

            $req->join('mbr', 'al.al_mid', 'mbr.mbr_mid');

            if (isset($cond['aid']))
                $req->where('al_aid', isset($cond['aid']));
            if ($name)
                $req->where('al_name', 'LIKE', "%$name%");

            $req->orderBy('al_points', 'desc');


            if ($limite) {
                if ($limite == 2) {
                    $req->offset($limite1)->limit($limite2);
                } else {
                    $req->limit($limite1);
                }
            }

            return $req->get()->toArray();
        }
    }

    /* supprimer une alliance */

    static function del(int $aid) {
        $rows = AlShoot::where('shoot_aid', $aid)->delete();
        $rows += AlRes::where('ares_aid', $aid)->delete();
        $rows += AlResLog::where('arlog_aid', $aid)->delete();
        $rows += AlMbr::del($aid);
        return $rows + Al::where('al_aid', $aid)->delete();
    }

    /**
     * editer une alliance
     * @global type $_user
     * @param int $mid
     * @param array $new
     * @return boolean|int
     */
    static function edit(int $aid, array $edit = []) {

        $request = [];
        if (isset($edit['open'])) {
            $request['al_open'] = $edit['open'];
        }
        if (isset($edit['nb_mbr'])) {
            $request['al_nb_mbr'] = DB::raw("al_nb_mbr + ?", $edit['nb_mbr']);
        }
        if (isset($edit['mid'])) {
            $request['al_mid'] = $edit['mid'];
        }
        if (isset($edit['name'])) {
            $request['al_name'] = $edit['name'];
        }
        if (isset($edit['descr'])) {
            $request['al_descr'] = $edit['descr'];
        }
        if (isset($edit['rules'])) {
            $request['al_rules'] = $edit['rules'];
        }
        if (isset($edit['diplo'])) {
            $request['al_diplo'] = $edit['diplo'];
        }
        Al::where('al_aid', $aid)->update($request);
    }

    /**
     *  création nouvelle alliance + image logo
     * @param int $mid
     * @param string $name
     * @return type
     */
    static function add(int $mid, string $name) {

        $request = ['al_mid' => $mid,
            'al_name' => $name,
            'al_descr' => '',
            'al_rules' => ''];

        $aid = Al::insertGetId($request);

        $im = imagecreatefrompng(ALL_LOGO_DIR . '0.png');
        imagepng($im, ALL_LOGO_DIR . "$aid.png");
        Al::makeThumb($aid, imagesx($im), imagesy($im));

        return $aid;
    }

    /**
     * common alliance
     * @param type $aid
     * @return type
     */
    static function upload(int $alid, array $fichier) {
        $nom = protect($fichier['name'], "string");
        $taille = protect($fichier['size'], "uint");
        $tmp = protect($fichier['tmp_name'], "string");
        $type = protect($fichier['type'], "string");
        $erreur = protect($fichier['error'], "string");

        if ($erreur)
            return $erreur;

        if ($taille > ALL_LOGO_SIZE OR ! strstr(ALL_LOGO_TYPE, $type))
            return false;

        $nom_destination = ALL_LOGO_DIR . $alid . '.png';
        move_uploaded_file($tmp, $nom_destination);
        list($width, $height, $type, $attr) = getimagesize(ALL_LOGO_DIR . $alid . '.png');
        if ($width <= ALL_LOGO_MAX_X_Y AND $height <= ALL_LOGO_MAX_X_Y)
            return Al::makeThumb($alid, $width, $height);
        else {
            $owidth = $width;
            $oheight = $height;
            $rap = $width / $height;
            $width = round(($width == $height) ? ALL_LOGO_MAX_X_Y : (($width > $height) ? ALL_LOGO_MAX_X_Y : ALL_LOGO_MAX_X_Y * $rap));
            $height = round($width / $rap);

            $im1 = imagecreatefrompng($nom_destination);
            $im2 = imagecreatetruecolor($width, $height);
            imagecopyresized($im2, $im1, 0, 0, 0, 0, $width, $height, $owidth, $oheight);
            imagepng($im2, ALL_LOGO_DIR . $alid . '.png');

            return Al::makeThumb($alid, $width, $height);
        }
    }

    static function makeThumb(int $alid, int $owidth, int $oheight) {
        $logo = ALL_LOGO_DIR . $alid . '.png';
        $width = 20;
        $height = 20;

        $image_p = imagecreatetruecolor($width, $height);
        $image = imagecreatefrompng($logo);
        $col = imagecolorallocatealpha($image_p, 255, 255, 255, 255);
        imagecolortransparent($image_p, $col);
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $owidth, $oheight);
        return imagepng($image_p, ALL_LOGO_DIR . $alid . '-thumb.png');
    }

}
