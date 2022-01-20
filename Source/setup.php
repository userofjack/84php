<?php
use core\Api;

$Version=__VERSION__;
Api::respond([
    'content'=>[
        'data'=>['version'=>$Version]
    ]
]);