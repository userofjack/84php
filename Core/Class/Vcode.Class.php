<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.1.0
*/

require(RootPath.'/Config/Vcode.php');

class Vcode{

	//颜色转换
	private static function HexRGB($HexColor){
		$Hex=hexdec(str_replace('#','',$HexColor));
		return ["red"=>0xFF&($Hex>>0x10),"green"=>0xFF&($Hex>>0x8),"blue"=>0xFF&$Hex];
	}
	//验证码
	public static function Base($UnionData=[]){
		$Width=QuickParamet($UnionData,'width','宽度',FALSE,80);
		$Height=QuickParamet($UnionData,'height','高度',FALSE,30);
		$Word=QuickParamet($UnionData,'word','文字',FALSE,NULL);
		$WordColor=QuickParamet($UnionData,'word_color','文字颜色',FALSE,'#000000');
		$Dot=QuickParamet($UnionData,'dot','点',FALSE,15);
		$Line=QuickParamet($UnionData,'line','线',FALSE,2);
		$NoiseHexColor=QuickParamet($UnionData,'noise_color','噪点颜色',FALSE,'#ff6600');
		
		$Font=DiskPath($_SERVER['84PHP_CONFIG']['Vcode']['FontFile']);
		if(!file_exists($Font)){
			Wrong::Report(['detail'=>'Error#M.10.0','code'=>'M.10.0']);
		}
		$PossibleLetters='0123456789bcdfghjkmnpqrstvwxyz';
		$Vcode=NULL;
		if(!empty($Word)){
			$Vcode=$Word;
		}
		else{
			$i=0;
			while ($i<5) {
				$Vcode.=substr($PossibleLetters,mt_rand(0,strlen($PossibleLetters)-1),1);
				$i++;
			}
		}
		if(isset($_SESSION)){
			$_SESSION['Vcode']=$Vcode;
		}
		$FontSize=$Height*0.5;
		$NewImg=imagecreate($Width, $Height);
		$BgColor=imagecolorallocate($NewImg,250,250,250);
		$WordRGBColor=self::HexRGB($WordColor);
		$NoiseRGBColor=self::HexRGB($NoiseHexColor);
		$TextColor=imagecolorallocate($NewImg,$WordRGBColor['red'],$WordRGBColor['green'],$WordRGBColor['blue']);
		$NoiseColor=imagecolorallocate($NewImg, $NoiseRGBColor['red'],$NoiseRGBColor['green'],$NoiseRGBColor['blue']);
		for($i=0;$i<$Dot;$i++){
			imagefilledellipse($NewImg,mt_rand(0,$Width),
			mt_rand(0,$Height),2,3,$NoiseColor);
		}
		for($i=0;$i<$Line;$i++){
			imageline($NewImg,mt_rand(0,$Width),mt_rand(0,$Height),mt_rand(0,$Width),mt_rand(0,$Height),$NoiseColor);
		}
		$AllText=imagettfbbox($FontSize,0,$Font,$Vcode);
		$X=($Width-$AllText[4])/2;
		$Y=($Height-$AllText[5])/2;
		imagettftext($NewImg,$FontSize,0,$X,$Y,$TextColor,$Font,$Vcode);
		@ob_clean();
		header('Content-Type: image/jpeg');
		header('Cache-Control: no-cache,must-revalidate');   
		header('Pragma: no-cache');   
		header("Expires: -1"); 
		header('Last-Modified: '.gmdate('D, d M Y 00:00:00',Runtime).' GMT');
		imagejpeg($NewImg);
		imagedestroy($NewImg);
		return $Vcode;
	}
	
	//调用方法不存在
	public static function __callStatic($Method,$Parameters){
		UnknownStaticMethod(__CLASS__,$Method);
	}
}