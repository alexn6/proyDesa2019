<?php

namespace App\Utils;

use \Datetime;

// Controlador de fechas
class ControlDate
{
    private static $instance;

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function isToday($f1){
        $now = new DateTime();  // fecha actual
        // $diff = date_diff($now, $f1);
        if($f1->format('Y-m-d') == $now->format('Y-m-d')){
            return true;
        }
        return false;
    }

    // verifica q se asegure una cant minima de dias entre las fechas
    public function diffDateCorrect($f1, $f2, $n){
        if($n < 0){
            return false;
        }
        $diff = date_diff($f2, $f1);
        $array_diff = str_split($diff->format("%R%a"));
        if($array_diff[0] == '+'){
            if($array_diff[1] >= $n){
                return true;
            }
        }
        
        return false;
    }

    // verifica q la fecha recibida sea igual o posterior a la fecha actual
    public function datePostCurrent($f1){
        $now = new DateTime();  // fecha actual
        $diff = date_diff($now, $f1);
        if($f1->format('Y-m-d') == $now->format('Y-m-d')){
            return true;
        }
        $array_diff = str_split($diff->format("%R%a"));
        if($array_diff[0] == '+'){
            return true;
        }
        
        return false;
    }

    // analiza si f1 es anterior a f2
    public function datePre($f1, $f2){
        $diff = date_diff($f1, $f2);
        $array_diff = str_split($diff->format("%R%a"));
        if($array_diff[0] == '+'){
            if($array_diff[1] >= 1){
                return true;
            }
        }
        
        return false;
    }
}