<?php

/*
 * helper for Zordania to Eloquent
 */

class DB extends Illuminate\Database\Capsule\Manager {

    private static $queries = [];
    
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
            // keep log of every queries
            $_sql->connection()->enableQueryLog();
            
            // ne marche pas :
            $_sql->connection()->listen(function($sql, $bindings, $time)
            {
                self::$queries[] = ['query' => $sql, 'bindings' => $bindings, 'time' => $time];
                echo "Zordania/class/DB.php : La requete est conservee pour log : $sql";
            });
        }
        
    }
    
    static function sqlLog($msg){

        // log des requetes
        $sqllog = new log(SITE_DIR."logs/mysql/mysqli_".date("d_m_Y").".log");
        $time = 0;
        $sqllog->text('**** '.date("H:i:s d/m/Y")."$msg ***");
        self::$queries = DB::connection()->getQueryLog();
        foreach(self::$queries as $i => $query){
            $text = $i . " | time= " . $query['time'] . " | " . $query['query'] . 
                    "\nBINDINGS = [" . implode(', ', $query['bindings']) . ']';
            $sqllog->text($text, false);
            $time += $query['time'];
        }
        $sqllog->text('*** TOTAL ' . count(DB::connection()->getQueryLog()) . ' requêtes -- TIME = ' . $time ." ms\n", false);
    }
    
    static function getSqlTime(){
        $t = 0;
        foreach(DB::connection()->getQueryLog() as $query){
            $t += $query['time'];
        }
        return $t / 1000;
    }
}
