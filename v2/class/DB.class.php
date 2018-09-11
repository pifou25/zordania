<?php

/*
 * helper for Eloquent
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

}
