<?php
namespace core;

/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.1.0
*/

class Dir
{
    //目录文件属性
    public static function state($UnionData=[]): array
    {
        $Path=Common::quickParameter($UnionData,'path','路径');
        
        if (!is_array($Path)) {
            $PathArray=[$Path];
        }
        else {
            $PathArray=$Path;
        }

        clearstatcache();
        $Return=[];
        foreach ($PathArray as $Val) {
            $TempArray=[];

            if (file_exists(Common::diskPath($Val))) {
                if (is_readable(Common::diskPath($Val))) {
                    $TempArray['R']='Y';
                }
                else {
                    $TempArray['R']='N';
                }
                
                if (is_writable(Common::diskPath($Val))) {
                    $TempArray['W']='Y';
                }
                else {
                    $TempArray['W']='N';
                }
                
                if (is_dir(Common::diskPath($Val))) {
                    if (is_executable(Common::diskPath($Val))) {
                        $TempArray['Ex']='Y';
                    }
                    else {
                        $TempArray['Ex']='N';
                    }
                }
            }
            $Return[$Val]=$TempArray;
        }
        return $Return;
    }
    
    //目录大小调用
    private static function sizeCall($Path)
    {
        $DirSize=0;
        if (file_exists($Path)&&$DirHandle=@opendir($Path)) {
            while ($FileName=readdir($DirHandle)) {
                if ($FileName!="."&&$FileName!="..") {
                    $SubFile=$Path."/".$FileName;
                    if (is_dir($SubFile))
                        $DirSize+=self::sizeCall($SubFile);
                    if (is_file($SubFile))
                        $DirSize+=filesize($SubFile);
                }
            }
            closedir($DirHandle);
            return $DirSize;
        }
        else {
            Api::wrong(['level'=>'F','detail'=>'Error#M.0.0','code'=>'M.0.0']);
        }
        return TRUE;
    }
    
    //目录大小
    public static function size($UnionData=[])
    {
        $Path=Common::quickParameter($UnionData,'path','路径');
        $Unit=Common::quickParameter($UnionData,'unit','单位',FALSE);

        $DirSize=self::sizeCall(Common::diskPath($Path));
        
        if ($Unit=='KB') {
            $DirSize=round($DirSize/pow(1024,1),2);
        }
        elseif ($Unit=='MB') {
            $DirSize=round($DirSize/pow(1024,2),2);
        }
        elseif ($Unit=='GB') {
            $DirSize=round($DirSize/pow(1024,3),2);
        }
        return $DirSize;
    }
    
    //删除目录调用
    private static function deleteCall($Dir)
    {
        if (file_exists($Dir)) {
            if ($DirHandle=@opendir($Dir)) {
                while ($FileName=readdir($DirHandle)) {
                    if ($FileName!="."&&$FileName!="..") {
                        $SubFile=$Dir."/".$FileName;
                        if (is_dir($SubFile)) {
                            self::deleteCall($SubFile);
                        }
                        if (is_file($SubFile)) {
                            @unlink($SubFile);
                        }
                    }
                }
                closedir($DirHandle);
                rmdir($Dir);
            }
            else {
                Api::wrong(['level'=>'F','detail'=>'Error#M.0.1','code'=>'M.0.1']);
            }
        }
    }
    
    //删除目录
    public static function delete($UnionData=[])
    {
        $Path=Common::quickParameter($UnionData,'path','路径');

        if (!is_array($Path)) {
            self::deleteCall(Common::diskPath($Path));
        }
        else {
            foreach ($Path as $ignored) {
                self::deleteCall(Common::diskPath($Path));
            }
        }
    }
    
    //复制目录调用
    private static function copyCall($From,$To)
    {
        if (!file_exists($From)) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.0.0','code'=>'M.0.0']);
        }
        if (is_file($To)) {
            exit;
        }
        if (!file_exists($To)) {
            mkdir($To,0777,TRUE);
        }
        if ($DirHandle=@opendir($From)) {
            while ($FileName=readdir($DirHandle)) {
                if ($FileName!="." && $FileName!="..") {
                    $FromPath=$From."/".$FileName;
                    $ToPath=$To."/".$FileName;
                    if (is_dir($FromPath)) {
                        self::copyCall($FromPath,$ToPath);
                    }
                    if (is_file($FromPath)) {
                        copy($FromPath,$ToPath);
                    }
                }
            }
            closedir($DirHandle);
        }
        else {
            Api::wrong(['level'=>'F','detail'=>'Error#M.0.1','code'=>'M.0.1']);
        }
    }
    
    //复制目录
    public static function copy($UnionData=[])
    {
        $From=Common::quickParameter($UnionData,'from','源路径');
        $To=Common::quickParameter($UnionData,'to','目标路径');

        self::copyCall(Common::diskPath($From), Common::diskPath($To));
    }
    
    //调用方法不存在
    public static function __callStatic($Method,$Parameters)
    {
        Common::unknownStaticMethod(__CLASS__,$Method);
    }
}