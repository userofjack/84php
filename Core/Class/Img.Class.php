<?php
/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.0.0
*/

require(__ROOT__.'/Config/Img.php');

class Img
{

    //颜色转换
    private static function hexRGB($HexColor)
    {
        $Hex=hexdec(str_replace('#','',$HexColor));
        return ["red"=>0xFF&($Hex>>0x10),"green"=>0xFF&($Hex>>0x8),"blue"=>0xFF&$Hex];
    }
    
    //图片支持检测
    private static function mIMECheck($MIME)
    {
        
        $FileType=str_replace('image/','',$MIME);
        $Support=array_key_exists($FileType,['bmp'=>'','gd2'=>'','gd'=>'','gif'=>'','jpeg'=>'','png'=>'','vnd.wap.wbmp'=>'','webp'=>'','xbm'=>'']);
        
        if ($MIME=='vnd.wap.wbmp') {
            $MIME='wbmp';
        }
        
        $FunExists=function_exists('imagecreatefrom'.$FileType);
        
        if (!$Support||!$FunExists) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.2.0','code'=>'M.2.0']);
        }
        
    }
    
    //打开图片
    private static function getImage($From,$DataType)
    {
        if ($DataType=='path') {
            if (!file_exists($From)) {
                Api::wrong(['level'=>'F','detail'=>'Error#M.2.1','code'=>'M.2.1']);
            }
            $Exp=explode('.',$From);
            $MIME=end($Exp);
            if (strtolower($MIME)=='wbmp') {
                $MIME='vnd.wap.wbmp';
            }
            if (strtolower($MIME)=='jpg') {
                $MIME='jpeg';
            }
            self::mIMECheck(strtolower($MIME));
            if ($MIME=='vnd.wap.wbmp') {
                $MIME='wbmp';
            }

            $ImgInfo=@getimagesize($From);
            
            $ImgData=call_user_func('imagecreatefrom'.$MIME,$From);
        }
        else {
            $ImgInfo=@getimagesizefromstring($From);
            $ImgData=call_user_func('imagecreatefromstring',$From);
        }

        if (!$ImgInfo||$ImgData===FALSE) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.2.2','code'=>'M.2.2']);
        }
        
        $Return=$ImgInfo;
        $Return['Data']=$ImgData;
        
        return $Return;
        
    }
    
    //输出图片
    private static function outputImage($ImgData,$To,$Quality,$MIME)
    {
        if (empty($To)) {
            $To=NULL;
            header('Content-Type: image/'.$MIME);
        }
        else {
            if (!is_dir(dirname($To))) {
                die (dirname($To));
                mkdir(dirname($To),0777,TRUE);
            }
            $Exp=explode('.',$To);
            $MIME=end($Exp);
            if (strtolower($MIME)=='wbmp') {
                $MIME='vnd.wap.wbmp';
            }
            if (strtolower($MIME)=='jpg') {
                $MIME='jpeg';
            }
        }
        self::mIMECheck(strtolower($MIME));
        if ($MIME=='png') {
            $Quality=intval($Quality/10);
        }
        
        if (array_key_exists($MIME,['jpeg'=>'','png'=>'','webp'=>''])) {
            $OutPut=call_user_func('image'.$MIME,$ImgData,$To,$Quality);
        }
        
        if (array_key_exists($MIME,['bmp'=>'','gd2'=>'','gd'=>'','gif'=>'','vnd.wap.wbmp'=>'','xbm'=>''])) {
            if ($MIME=='vnd.wap.wbmp') {
                $MIME='wbmp';
            }
            $OutPut=call_user_func('image'.$MIME,$ImgData,$To);
        }
        
        if (!$OutPut) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.2.3','code'=>'M.2.3']);
        }
    }
    
    //伸缩和水印
    public static function change($UnionData=[])
    {
        $From=quickParamet($UnionData,'image','源图片');
        $DataType=strtolower(quickParamet($UnionData,'data_type','资源类型',FALSE,'path'));
        $To=quickParamet($UnionData,'to','目标路径',FALSE,NULL);
        $Width=quickParamet($UnionData,'width','宽度',FALSE,NULL);
        $Height=quickParamet($UnionData,'height','高度',FALSE,NULL);
        $Scale=quickParamet($UnionData,'scale','缩放',FALSE,1.0);
        $Word=quickParamet($UnionData,'word','文字',FALSE,NULL);
        $WordSize=quickParamet($UnionData,'word_size','文字大小',FALSE,NULL);
        $WordColor=quickParamet($UnionData,'word_color','文字颜色',FALSE,'#333333');
        $WordMarginX=quickParamet($UnionData,'word_margin_x','文字左边距',FALSE,0);
        $WordMarginY=quickParamet($UnionData,'word_margin_y','文字顶边距',FALSE,0);
        $Quality=quickParamet($UnionData,'quality','质量',FALSE,75);
        $DataType=strtolower(quickParamet($UnionData,'data_type','资源类型',FALSE,'path'));
        $MIME=strtolower(quickParamet($UnionData,'mime','图片格式',FALSE,'jpeg'));
        
        if ($DataType!='path') {
            $DataType='string';
        }
        else {
            $From=diskPath($From);
        }

        if (!empty($To)) {
            $To=diskPath($To);
        }
        
        $WordColorArray=["red"=>80,"green"=>80,"blue"=>80];
        $ImgInfo=self::getImage($From,$DataType);
        
        if (empty($Width)&&empty($Height)) {
            $NewWidth=round($ImgInfo[0]*$Scale);
            $NewHeight=round($ImgInfo[1]*$Scale);
        }
        else {
            if (empty($Width)) {
            $NewWidth=round($ImgInfo[0] * ($Height/$ImgInfo[1]));
            }
            else {
                $NewWidth=$Width;
            }
            if (empty($Height)) {
                $NewHeight=round($ImgInfo[1] * ($Width/$ImgInfo[0]));
            }
            else {
                $NewHeight=$Height;
            }
        }
        $NewImg=imagecreatetruecolor($NewWidth,$NewHeight);
        if (!$NewImg) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.2.4','code'=>'M.2.4']);
        }
        imagecopyresampled($NewImg,$ImgInfo['Data'],0,0,0,0,$NewWidth,$NewHeight,$ImgInfo[0],$ImgInfo[1]);
        if (!empty($Word)) {
            if (empty($WordSize)) {
                $WordSize=$NewHeight*0.12;
            }
            if ($WordColor!=NULL) {
                $WordColorArray=self::hexRGB($WordColor);
            }
            if (!imagettftext($NewImg,$FontSize,0,$WordMarginX,$WordMarginY,$textcolor1,diskPath($_SERVER['84PHP']['Config']['Img']['FontFile']),$Word)) {
                Api::wrong(['level'=>'F','detail'=>'Error#M.2.5','code'=>'M.2.5']);
            }
        }
        
        self::outputImage($NewImg,$To,$Quality,$MIME);
        
        imagedestroy($ImgInfo['Data']);
        imagedestroy($NewImg);
    }
    
    //合并图片
    public static function merge($UnionData=[])
    {
        $Background=quickParamet($UnionData,'background','背景');
        $Foreground=quickParamet($UnionData,'foreground','前景');
        $DataType=strtolower(quickParamet($UnionData,'data_type','资源类型',FALSE,'path'));
        $To=quickParamet($UnionData,'to','目标路径',FALSE,NULL);
        $ImageX=quickParamet($UnionData,'image_x','起始X',FALSE,0);
        $ImageY=quickParamet($UnionData,'image_y','起始Y',FALSE,0);
        $Scale=quickParamet($UnionData,'scale','缩放',FALSE,1.0);
        $Quality=quickParamet($UnionData,'quality','质量',FALSE,75);
        $MIME=strtolower(quickParamet($UnionData,'mime','图片类型',FALSE,'jpeg'));
        
        if (!empty($To)) {
            $To=diskPath($To);
        }
        
        if ($DataType!='path') {
            $DataType='string';
        }
        else {
            $Background=diskPath($Background);
            $Foreground=diskPath($Foreground);
        }

        
        $BgImageInfo=self::getImage($Background,$DataType);
        $FgImageInfo=self::getImage($Foreground,$DataType);

        imagecopyresampled($BgImageInfo['Data'],$FgImageInfo['Data'],$ImageX,$ImageY,0,0,intval($FgImageInfo[0]*$Scale),intval($FgImageInfo[1]*$Scale),$FgImageInfo[0],$FgImageInfo[1]);
        
        self::outputImage($BgImageInfo['Data'],$To,$Quality,$MIME);
        
        imagedestroy($BgImageInfo['Data']);
        imagedestroy($FgImageInfo['Data']);
    }
    
    //调用方法不存在
    public static function __callStatic($Method,$Parameters)
    {
        unknownStaticMethod(__CLASS__,$Method);
    }
}