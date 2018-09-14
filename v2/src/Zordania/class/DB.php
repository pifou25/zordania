<?php

/*
 * helper for Zordania to Eloquent
 */

class DB extends Illuminate\Database\Capsule\Manager {

    /**
     * Convert Eloquent DB::select result into array
     * @param object stdClass $obj
     * @return array
     */
    static function toArray($obj, $key = false) {
        if ($key) {
            foreach ($obj as $val) {
                $arr = (array) $val;
                $result[$arr[$key]] = $arr;
            }
            return $result;
        } else {
            return array_map(function($val) {
                return (array) $val;
            }, $obj);
        }
    }

    /**
     * execute query and convert result into array
     * @param string $sql
     * @return array
     */
    static function sel($sql, $key = false) {
        return self::toArray(parent::select($sql), $key);
    }

    static function init($settings){
        // New database Connection (Eloquent)
        $_sql = new Illuminate\Database\Capsule\Manager();
        $_sql->addConnection($settings);
        // Make this Capsule instance available globally via static methods
        $_sql->setAsGlobal();
        // Setup the Eloquent ORM
        $_sql->bootEloquent();
        if(SITE_DEBUG){
            $_sql->connection()->enableQueryLog();
        }
        
    }
    
    static function sqlLog($msg){

        // log des requetes
        $sqllog = new log(SITE_DIR."logs/mysql/mysqli_".date("d_m_Y").".log");
        $time = 0;
        foreach(DB::connection()->getQueryLog() as $i => $query){
            $text = '**** '.date("H:i:s d/m/Y")."$msg ***\n";
            $text .= $i . " | time= " . $query['time'] . " | " . $query['query'] . 
                    "\nBINDINGS = [" . implode(', ', $query['bindings']) . ']';
            $text .= "\nCALLSTACK:\n".implode("\n", $query['callstack']);
            $sqllog->text($text);
            $time += $query['time'];
        }
        $sqllog->text('*** TOTAL ' . count(DB::connection()->getQueryLog()) . ' requêtes -- TIME = ' . $time );
        
    }
}
