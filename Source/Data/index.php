<?php
$Version='5.0.0';

function CacheTime(){
    return time();
}
echo Data::Get([
    'key'=>'time',
    'set'=>function(){
        $Time = CacheTime();
        Data::Set([
            'key'=>'time',
            'value'=>$Time,
            'time'=>-1
        ]);
        return $Time;
    }
]);
exit;
?> 