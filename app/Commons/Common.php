<?php

namespace App\Commons;
use DB;
class Common
{
    public static function logip($ip, $screen, $status){
        $values = array('ip' => $ip,'screen' => $screen, 'status' => $status, 'created_at'=>now());
        DB::table('log_ips')->insert($values);
    }
    
}