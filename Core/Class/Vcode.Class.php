<?php
/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.0.0
*/

require(__ROOT__.'/Config/Vcode.php');

class Vcode
{

    //颜色转换
    private static function hexRGB($HexColor)
    {
        $Hex=hexdec(str_replace('#','',$HexColor));
        return ["red"=>0xFF&($Hex>>0x10),"green"=>0xFF&($Hex>>0x8),"blue"=>0xFF&$Hex];
    }
    //验证码
    public static function create($UnionData=[])
    {
        $Word=quickParamet($UnionData,'word','文字',TRUE);
        $Base64=quickParamet($UnionData,'base64','base64',FALSE,FALSE);
        $Width=quickParamet($UnionData,'width','宽度',FALSE,80);
        $Height=quickParamet($UnionData,'height','高度',FALSE,30);
        $WordColor=quickParamet($UnionData,'word_color','文字颜色',FALSE,'#000000');
        $Dot=quickParamet($UnionData,'dot','点',FALSE,15);
        $Line=quickParamet($UnionData,'line','线',FALSE,2);
        $NoiseHexColor=quickParamet($UnionData,'noise_color','噪点颜色',FALSE,'#ff6600');
        
        $Font=diskPath($_SERVER['84PHP']['Config']['Vcode']['FontFile']);
                
        if (!file_exists($Font)) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.10.0','code'=>'M.10.0']);
        }

        $FontSize=$Height*0.5;
        $NewImg=imagecreate($Width, $Height);
        $BgColor=imagecolorallocate($NewImg,250,250,250);
        $WordRGBColor=self::hexRGB($WordColor);
        $NoiseRGBColor=self::hexRGB($NoiseHexColor);
        $TextColor=imagecolorallocate($NewImg,$WordRGBColor['red'],$WordRGBColor['green'],$WordRGBColor['blue']);
        $NoiseColor=imagecolorallocate($NewImg, $NoiseRGBColor['red'],$NoiseRGBColor['green'],$NoiseRGBColor['blue']);
        for ($i=0;$i<$Dot;$i++) {
            imagefilledellipse($NewImg,mt_rand(0,$Width),
            mt_rand(0,$Height),2,3,$NoiseColor);
        }
        for ($i=0;$i<$Line;$i++) {
            imageline($NewImg,mt_rand(0,$Width),mt_rand(0,$Height),mt_rand(0,$Width),mt_rand(0,$Height),$NoiseColor);
        }
        $AllText=imagettfbbox($FontSize,0,$Font,$Word);
        $X=($Width-$AllText[4])/2;
        $Y=($Height-$AllText[5])/2;
        imagettftext($NewImg,$FontSize,0,$X,$Y,$TextColor,$Font,$Word);
        @ob_clean();
        
        if (!$Base64) {
            header('Content-Type: image/jpeg');
            header('Cache-Control: no-cache,must-revalidate');   
            header('Pragma: no-cache');   
            header("Expires: -1"); 
            header('Last-Modified: '.gmdate('D, d M Y 00:00:00',__TIME__).' GMT');
            imagejpeg($NewImg);
            imagedestroy($NewImg);
        }
        else {
            imagejpeg($NewImg);
            $ImgData = ob_get_contents();
            ob_end_clean();
            return 'data:image/jpeg;base64,'.chunk_split(base64_encode($ImgData));
        }
    }
    
    //调用方法不存在
    public static function __callStatic($Method,$Parameters)
    {
        unknownStaticMethod(__CLASS__,$Method);
    }
}