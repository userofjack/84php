<?php
namespace rpc;

use core\Common;

class Common
{
    public static function auth($UnionData=[]){
        $Key=Common::quickParamet($UnionData,'key','key',TRUE,'');
        if($Key!==$_SERVER['84PHP']['Config']['Rpc']['key']['native']){
            Log::add(['level'=>'error','info'=>'Rpc auth error.']);
            Log::output();
            return FALSE;
        }
        else{
            return TURE;
        }
    }
}