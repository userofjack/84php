<?php
namespace core;

/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.0.0
*/

class Api
{
    public static function respond($UnionData)
    {
        $Content=Common::quickParameter($UnionData,'content','内容',FALSE,[]);
        $Log=Common::quickParameter($UnionData,'log','日志',FALSE,FALSE);
        $HttpCode=Common::quickParameter($UnionData,'http','响应码',FALSE,200);
        
        $Style=$_SERVER['84PHP']['Config']['Api']['template'];
        
        foreach ($Content as $Key => $Val) {
            $Style[$Key]=$Val;
        }

        $Respond=json_encode($Style);
 
        if ($Log) {
            Log::add(['level'=>'info','info'=>'[API Respond] '.$Respond]);
        }
        
        ob_clean();
        http_response_code(intval($HttpCode));
        header('content-type:application/json');

        echo $Respond;
    }
    
    public static function wrong($UnionData)
    {
        $Detail=Common::quickParameter($UnionData,'detail','详情');
        $Code=Common::quickParameter($UnionData,'code','状态码',FALSE,0);
        $Stack=Common::quickParameter($UnionData,'stack','堆栈',FALSE,FALSE);
        $Log=Common::quickParameter($UnionData,'log','日志',FALSE,TRUE);
        $HttpCode=Common::quickParameter($UnionData,'http','响应码',FALSE,200);
        $Level=strtoupper(Common::quickParameter($UnionData,'level','级别'));
        
        $Config=$_SERVER['84PHP']['Config']['Api'];
        
        foreach ($Config['wrong']['ignore'] as $Val) {
            if(strstr($Detail,$Val)){
                return TRUE;
            }
        }
        
        if (isset($Config['wrong']['replace'][$Code])) {
            $Code=$Config['wrong']['replace'][$Code];
        }

        $WrongInfo=['level'=>'unknown','detail'=>str_replace('\\','/',$Detail),'stack'=>[],'time'=>microtime(TRUE)];

        if (strtoupper($Level)=='S') {
            $WrongInfo['level']='script';
        }
        elseif (strtoupper($Level)=='F') {
            $WrongInfo['level']='framework';
        }
        elseif (strtoupper($Level)=='A') {
            $WrongInfo['level']='application';
        }
        elseif (strtoupper($Level)=='U') {
            $WrongInfo['level']='user';
        }
        
        if ($Stack||$WrongInfo['level']=='script'||$WrongInfo['level']=='framework') {
            $StackArray=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            foreach ($StackArray as $Key => $Val) {
                $StackInfo=' ';
                if (isset($Val['class'])) {
                    $StackInfo.=$Val['class'].$Val['type'];
                }
                if (isset($Val['function'])) {
                    if ($Val['function']=='{closure}') {
                        $StackInfo.='{closure}';
                    }
                    else {
                        $StackInfo.=$Val['function'].'()';
                    }
                }
                if (isset($Val['file'])&&isset($Val['line'])) {
                    $StackInfo.=' at ['.str_replace('\\','/',$Val['file']).':'.$Val['line'].'].';
                }
                $WrongInfo['stack']['#'.$Key]=$StackInfo;
            }
        }
        
        
        if (__DEBUG__||stristr($Config['wrong']['respond'],$Level)!==FALSE) {
            foreach ($Config['wrong']['style'] as $Key => $Val) {
                $Config['wrong']['style'][$Key]=str_replace(['{code}','{info}','{time}'],[$Code,$WrongInfo['detail'],$WrongInfo['time']],$Val);
                if($Val=='{stack}'){
                    $Config['wrong']['style'][$Key]=$WrongInfo['stack'];
                }
            }
        }
        else {
            foreach ($Config['wrong']['style'] as $Key => $Val) {
                $Config['wrong']['style'][$Key]=str_replace(['{code}','{info}','{time}'],['M.4.12','Error#M.4.12',$WrongInfo['time']],$Val);
            }
        }
        self::respond(['content'=>$Config['wrong']['style'],'http'=>$HttpCode]);
       
        if (stristr($Config['wrong']['log'],$Level)!==FALSE&&$Log) {
            $WrongLog='['.$WrongInfo['level'].'] '.$WrongInfo['detail'];
            
            foreach ($WrongInfo['stack'] as $Key => $Val) {
                $WrongLog.="\r\n    ".$Key.' '.$Val;
            }
            
            $WrongLog.="\r\n    ".'Occurred on '.$WrongInfo['time'];
            
            Log::add(['level'=>'error','info'=>$WrongLog]);
            
            Log::output();
        }
        exit;
    }
}