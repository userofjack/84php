<?php
/*
$Version=__VERSION__;
Api::respond([
    'content'=>[
        'data'=>['version'=>$Version]
    ]
]);
*/

echo Tool::send(['url'=>'https://www.baidu.com/s','mode'=>'get','data'=>['wd'=>'aa']]);