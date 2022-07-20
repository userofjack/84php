<?php
namespace core;

use core\Common;
use core\Api;

/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.0.0
*/

class Cache
{
    
    //写入缓存
    private static function writeCache($Context,$FilePath)
    {

        $Handle=@fopen($FilePath,'w');
        if (!$Handle) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.11.2','code'=>'M.11.2']);
        }
        if (fwrite($Handle,$Context)===FALSE) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.11.3','code'=>'M.11.3']);
        };
        fclose($Handle);
    }

    
    //模板编译
    private static function templateTranslate($From,$To,$CacheChanged)
    {
        $Cache=NULL;

        if ($CacheChanged&&file_exists($From)) {
            if (filesize($From)>0) {
                $Cache=file_get_contents($From);
                if ($Cache===FALSE) {
                    Api::wrong(['level'=>'F','detail'=>'Error#M.11.0','code'=>'M.11.0']);
                }
                $Cache=str_replace([';;'],[';'],$Cache);
                $Cache=preg_replace(['/(?:^|\n|\s+)\/\/.*/',"/\/\*(.|\r\n)*\*\//"],['',"\r\n"],$Cache);
                $Cache=preg_replace(['/(?:^|\n|\s+)#.*/','/\?>(\s\r\n)*/'],'',$Cache);
                $Cache=preg_replace("/(\?>(\\s*<\?php)+)/","\r\n",$Cache);
                $Cache=preg_replace("/(<\?(\\s*\r?\n)+)/","<?php\r\n",$Cache);
                $Cache=preg_replace("/(\r?\n(\\s*\r?\n)+)/","\r\n",$Cache);
                self::writeCache($Cache,$To);
            }
        }
    }
    
    //文件信息
    private static function fileInfo($FilePath)
    {
        $ReturnArray=[
            'path'=>$FilePath,
            'exist'=>FALSE,
            'time'=>0
        ];
        if (file_exists($FilePath)) {
            $ReturnArray['exist']=TRUE;
            $ReturnArray['time']=@filemtime($FilePath);
            if ($ReturnArray['time']===FALSE) {
                $ReturnArray['time']=0;
            }
        }
        return $ReturnArray;
    }
    
    //编译
    public static function compile($UnionData=[])
    {
        $Path=Common::quickParamet($UnionData,'path','路径');
        $Force=Common::quickParamet($UnionData,'force','强制编译',FALSE,FALSE);
        
        if (__DEBUG__) {
            $Force=TRUE;
        }

        $CacheChanged=FALSE;
        $CacheDir=__ROOT__.'/Temp/Cache';
                
        $CacheFile=self::fileInfo($CacheDir.$Path.'.php');
        
        if (!__DEBUG__&&!$Force&&$CacheFile['exist']&&$CacheFile['time']+$_SERVER['84PHP']['Config']['Cache']['expTime']>__TIME__) {
            return FALSE;
        }

        $SourcePath=self::fileInfo(__ROOT__.'/Source'.$Path.'.php');

        if (!is_dir($CacheDir)&&!@mkdir($CacheDir,0777,TRUE)) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.11.5'."\r\n\r\n @ ".$CacheDir,'code'=>'M.11.5']);
        };


        if (!$SourcePath['exist']) {
            if ($CacheFile['exist']) {
                @unlink($CacheFile['path']);
            }
            $CheckPath=dirname($CacheDir.$Path.'/xxx');
            while (TRUE) {
                if (strlen($CheckPath)>strlen($CacheDir)) {
                    if (is_dir($CheckPath)) {
                        if (count(scandir($CheckPath))==2) {
                            rmdir($CheckPath);
                        }
                        else {
                            break;
                        }
                    }
                    $CheckPath=dirname($CheckPath);
                }
                else {
                    break;
                }
            }
            return FALSE;
        }

        if (!$CacheFile['exist']||$CacheFile['time']>__TIME__) {
            if ($SourcePath['exist']) {
                $CacheChanged=TRUE;
            }
        }

        if ($SourcePath['exist']&&$CacheFile['exist']) {
            if ($SourcePath['time']>$CacheFile['time']||$SourcePath['time']>__TIME__||$Force) {
                if ($SourcePath['time']>__TIME__) {
                    touch($SourcePath['path']);
                }
                $CacheChanged=TRUE;
            }
        }        
        if (!__DEBUG__&&!$Force&&$CacheFile['exist']&&$CacheFile['time']+$_SERVER['84PHP']['Config']['Cache']['expTime']<=__TIME__&&!$CacheChanged) {
            touch($CacheFile['path']);
            return FALSE;
        }

        if (!is_dir(dirname($CacheFile['path']))&&$CacheChanged) {
            if (!mkdir(dirname($CacheFile['path']),0777,TRUE)) {
                Api::wrong(['level'=>'F','detail'=>'Error#M.11.5'."\r\n\r\n @ ".dirname($CacheFile['path']),'code'=>'M.11.5']);
            }
        }

        self::templateTranslate($SourcePath['path'],$CacheFile['path'],$CacheChanged);
    }
    
    //重建所有缓存
    public static function reBuild($Path='')
    {
        $SourceDir=__ROOT__.'/Source';
        $DirHandle=@opendir($SourceDir.$Path);
        while ($SourceFile=readdir($DirHandle)) {
            if ($SourceFile!='.'&&$SourceFile!='..') {
                $AllFile=$Path.'/'.$SourceFile;
                $Exp=explode('.',$AllFile);
                if (is_dir($AllFile)) {
                    self::reBuild($AllFile);
                }
                else if (strtoupper(end($Exp))=='PHP') {
                    self::compile([
                        'path'=>substr(str_replace($SourceDir,'',$AllFile),0,-4)
                        ,TRUE]);
                }
            }
        }
        closedir($DirHandle);
    }
    
    //调用方法不存在
    public static function __callStatic($Method,$Parameters)
    {
        Common::unknownStaticMethod(__CLASS__,$Method);
    }
}