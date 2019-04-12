<?php

class SqlAdm {

    const TBL_MBR_FULL = [
        MYSQL_PREBDD . 'mbr' => [
            'pk' => 'mbr_mid',
            'rel' => [
                MYSQL_PREBDD . 'al_mbr' => 'ambr_mid',
                MYSQL_PREBDD . 'al_res_log' => 'arlog_mid',
                MYSQL_PREBDD . 'al_shoot' => 'shoot_mid',
                MYSQL_PREBDD . 'atq' => 'atq_mid1',
                MYSQL_PREBDD . 'atq' => 'atq_mid2', // 2x la même table
                MYSQL_PREBDD . 'atq_mbr' => 'atq_mid',
                MYSQL_PREBDD . 'bon' => 'bon_mid',
                MYSQL_PREBDD . 'btc' => 'btc_mid',
                MYSQL_PREBDD . 'hero' => 'hro_mid',
                MYSQL_PREBDD . 'histo' => 'histo_mid',
                MYSQL_PREBDD . 'leg' => [
                    'fk' => 'leg_mid',
                    'pk' => 'leg_cid',
                    'rel' => [
                        MYSQL_PREBDD . 'leg_res' => 'lres_lid',
                        MYSQL_PREBDD . 'unt' => 'unt_lid']],
                MYSQL_PREBDD . 'mbr_log' => 'mlog_mid',
                MYSQL_PREBDD . 'mch' => 'mch_mid',
                MYSQL_PREBDD . 'msg_env' => 'menv_mid',
                MYSQL_PREBDD . 'msg_rec' => 'mrec_mid',
                MYSQL_PREBDD . 'ntes' => 'nte_mid',
                MYSQL_PREBDD . 'rec' => 'rec_mid',
                MYSQL_PREBDD . 'res' => 'res_mid',
                MYSQL_PREBDD . 'res_todo' => 'rtdo_mid',
                MYSQL_PREBDD . 'src' => 'src_mid',
                MYSQL_PREBDD . 'src_todo' => 'stdo_mid',
                MYSQL_PREBDD . 'trn' => 'trn_mid',
                MYSQL_PREBDD . 'unt_todo' => 'utdo_mid',
                MYSQL_PREBDD . 'vld' => 'vld_mid']]
    ];
    const TBL_ALLY = [MYSQL_PREBDD . 'al_mbr' => [
            'fk' => 'ambr_mid',
            'pk' => 'ambr_aid',
            'rel' => [
                MYSQL_PREBDD . 'al' => 'al_aid',
                MYSQL_PREBDD . 'al_res' => 'ares_aid',
                MYSQL_PREBDD . 'al_res_log' => 'arlog_aid',
                MYSQL_PREBDD . 'al_shoot' => 'shoot_aid',
                MYSQL_PREBDD . 'diplo' => 'dpl_al1',
                MYSQL_PREBDD . 'mbr' => 'mbr_mid']]
    ];
    const TBL_FRM = [
        MYSQL_PREBDD . 'frm_forums' => ['pk' => 'id',
            'rel' => [
                MYSQL_PREBDD . 'frm_topics' => [
                    'fk' => 'forum_id',
                    'pk' => 'id',
                    'rel' => [MYSQL_PREBDD . 'frm_posts' => 'topic_id']]
            ]]
    ];
    const EXP_STRUC = 1;
    const EXP_DATA = 2;
    const EXP_DELETE = 4;
    const EXP_ALL = 7;

    static private $errors = [];

    public static function dumpMbr(int $mid, int $mode = self::EXP_ALL) {

        //$listeTables = mysql_query("show tables", $connexion);
        $sql = self::export(SqlAdm::TBL_MBR_FULL, $mid, $mode);
        $entete = self::comment("dump de la base zordania, mid=$mid au " . date("d-M-Y"))
                . "\n" . join("\n", self::$errors);
        return "$entete \n $sql\n";
    }

    /**
     * export du forum
     * @param int $fid
     * @param int $mode
     * @return type
     */
    public static function dumpFrm(int $fid, int $mode = self::EXP_DATA) {

        $sql = self::export(SqlAdm::TBL_FRM, $fid, $mode);
        $entete = self::comment("dump du forum, forum=$fid au " . date("d-M-Y"))
                . "\n" . join("\n", self::$errors);
        return "$entete \n $sql";
    }

    /**
     * export reccursif par les cles etrangeres
     * @param array $listeTables [$table => [ pk => $col, fk => [ idem ]]]
     *   ou [$table => $pk] si pas de cle etrangere
     * @param type $value = valeur de la pk
     * @param int $mode
     * @return type
     */
    public static function export(array $listeTables, $value, int $mode = self::EXP_ALL, $pk1 = '') {
        $creations = "";
        $insertions = "";
        $deletions = "";

        foreach ($listeTables as $table => $cle) {

            // si l'utilisateur a demandé la structure
            if ($mode & self::EXP_STRUC) {
                $creations .= self::comment("creation de la table $table");
                $data = DB::sel("show create table " . $table);
                foreach ($data as $val) {
                    $creations .= $val['Create Table'] . ";\n\n";
                }
                if (is_array($cle) && isset($cle['rel'])) {
                    $creations .= self::export($cle['rel'], $value, self::EXP_STRUC);
                }
            }

            // si l'utilisateur a demandé le delete
            if ($mode & self::EXP_DELETE) {
                $deletions .= self::exportDelete($table, $cle, $value);
            }

            // si l'utilisateur a demandé les données
            if ($mode & self::EXP_DATA) {
                $insertions .= self::exportData($table, $cle, $value, $pk1);
            }
        }


        return "$creations \n $deletions\n $insertions";
    }

    private static function exportDelete(string $table, $cle, $value){
        if (is_array($cle) && isset($cle['pk']) && isset($cle['rel'])) {
            $pk = $cle['pk'];

            if (isset($cle['fk'])) {
                $fk = "(SELECT $pk FROM $table WHERE {$cle['fk']} = $value)";
            } else {
                $fk = $value;
            }
            // $deletions .= "\n--- recursive call for $table -> $pk -> $fk " . count($cle['rel']) . "\n";
            $deletions .= self::export($cle['rel'], $fk, self::EXP_DELETE);
            $cle = $cle['pk'];
        }
        // $deletions .= "\n--- delete de $table -> $cle\n\n";
        return $deletions . "DELETE FROM $table WHERE $cle = $value;\n";
    }
    
    private static function exportData(string $table, $cle, $value, string $pk) {
        $insertions = "";
        if (is_array($cle) && isset($cle['pk']) && isset($cle['rel'])) {
            // si liens: insérer 1 ligne puis récupérer l'auto-incrément puis
            // insérer les lignes des tables associées
            $cle1 = isset($cle['fk']) ? $cle['fk'] : $cle['pk'];

            $fk = isset($cle['fk']) ? $cle['fk'] : '';
            // exporter la table mère 1 ligne par 1 avec l'identifiant FK
            $sql = "SELECT * FROM $table WHERE $cle1 = $value;";
            //echo "<br/>\n$table = " . print_r($cle);
            $datas = self::CreeInsertSQL($sql, $table, $fk, $pk);
            foreach ($datas as $key => $data) {
                // $key est l'ID existant, sera remplacé par @fk
                $insertions .= "INSERT INTO " . MYSQL_BASE . ".$table VALUES $data;\n";
                // auto increment pour avoir @clefk
                $insertions .= self::getAutoIncrement($table, $table.'_'.$cle['pk']);
                // tables liées
                $insertions .= self::export($cle['rel'], $key, self::EXP_DATA, $table.'_'.$cle['pk']);
            }
            return $insertions;
        } else {
            // aucune table liée - export simple avec la valeur de l'auto-incrément
            $sql = "SELECT * FROM $table WHERE $cle = $value;";
            return self::getInserts(self::CreeInsertSQL($sql, $table, $cle, $pk), $table);
        }
    }

    private static function comment(string $s) {
        return "-- -----------------------------\n"
                . "-- $s\n" . "-- -----------------------------\n";
    }

    /**
     * 
     * @param string $sql
     * @param string $table
     * @param array $fk
     * @return array
     */
    private static function CreeInsertSQL(string $sql, string $table, string $pk = '', string $fk = '') {
        // echo "$sql -- $table -- $pk -- $fk</br>\n";
        $result = DB::connection()->getPdo()->query($sql);
        if ($result === false) {
            self::$errors[] = "-- $table en erreur\n-- $sql\n";
            return[];
        }
        if (empty($result)) {
            self::$errors[] = "-- $table vide\n-- $sql\n";
            return[];
        }
        foreach(range(0, $result->columnCount() - 1) as $column_index)
        {
            $meta[] = $result->getColumnMeta($column_index);
        }
        return self::getDatas($result, $meta, $pk, $fk);
    }

    private static function getInserts(array $datas, string $table, int $groupBy = 20) {
        if (empty($datas))
            return'';
        $insert = "INSERT INTO " . MYSQL_BASE . ".$table VALUES";
        $insertions = self::comment("insertions dans la table $table");
        foreach ($datas as $v) {
            $result[] = $v;

            if (count($result) > $groupBy) { // group by 10 insert
                $insertions .= $insert . "\n" . join(",\n", $result) . ";\n\n";
                $result = [];
            }
        }
        return $insertions . (empty($result) ? '' : $insert . "\n" . join(",\n", $result) . ";\n\n");
    }

    /**
     * mettre en forme les données - remplacer le champs $cle par la variable @$fk
     * @param recordset $result
     * @param fieldsInfo $fields
     * @param string $cle
     * @param string $fk
     * @return string
     */
    private static function getDatas($result, $fields, string $cle = '', string $fk = '') {
        $datas = [];
        while ($nuplet = $result->fetch()) { // select * from $table
            $values = [];
            $key = 0;
            foreach ($fields as $i => $finfo) {
                // if ($finfo->flags & 512) { // 512 = AUTO_INCREMENT_FLAG
                if(self::isPk($finfo)){
                    $data = 'NULL'; // self::getAutoIncrement($table);
                    $key = $nuplet[$i];
                } else if ($finfo['name'] == $cle) {                    // foreign key as a SQL variable
                    $data = "@$fk";
                } else {                    // char ou assimilé ou date ( 7 )
                    if ($finfo['pdo_type'] == 2) // type == 253 || $finfo->type == 254 || $finfo->type == 252 || $finfo->type == 7)
                        $sep = "'";
                    else if ($finfo['pdo_type'] == 1) // numeric 
                        $sep = "";
                    else{
                        echo $finfo['name'] . '--' . $finfo['pdo_type'];
                        $sep = "'";
                    }

                    $data = ($nuplet[$i] == NULL ? 'NULL' : $sep . addslashes($nuplet[$i]) . $sep);
                }
                $values[] = $data;
            }

            if ($key == 0) {
                $datas[] = "(" . join(", ", $values) . ")";
            } else {
                $datas[$key] = "(" . join(", ", $values) . ")";
            }
        }

        return $datas;
    }

    private static function isPk($field){
        if(isset($field['flags'])){
            foreach($field['flags'] as $value){
                if($value == 'primary_key')
                    return true;
            }
        }
        return false;
    }
    
    private static function getAutoIncrement(string $table, string $fk, string $db = MYSQL_BASE) {
        return "SELECT @$fk := AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES "
                . "WHERE TABLE_SCHEMA = '$db' AND TABLE_NAME = '$table';\n";
    }

}
