<?php
/*****************************************************/
/*****************************************************/
/*                                                   */
/*               84PHP-www.84php.com                 */
/*                                                   */
/*****************************************************/
/*****************************************************/

/*
  本框架为免费开源、遵循Apache2开源协议的框架，但不得删除此文件的版权信息，违者必究。
  This framework is free and open source, following the framework of Apache2 open source protocol, but the copyright information of this file is not allowed to be deleted,violators will be prosecuted to the maximum extent possible.

  ©2017-2020 Bux. All rights reserved.

  框架版本号：4.0.1
*/

require(RootPath.'/Config/Vcode.php');

class Vcode{

	//颜色转换
	private function HexRGB($HexColor){
		$Hex=hexdec($HexColor);
		return array("red"=>0xFF&($Hex>>0x10),"green"=>0xFF&($Hex>>0x8),"blue"=>0xFF&$Hex);
	}
	//验证码
	public function Base($UnionData=array()){
		$Width=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'width','宽度',FALSE,80);
		$Height=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'height','高度',FALSE,30);
		$Scale=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'scale','缩放',FALSE,1.0);
		$Word=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'word','文字',FALSE,NULL);
		$WordColor=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'word_color','文字颜色',FALSE,'#000000');
		$Dot=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'dot','文字',FALSE,15);
		$Line=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'line','文字',FALSE,2);
		$NoiseHexColor=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'noise_color','噪点颜色',FALSE,'#ff6600');
		
		$Font=AddRootPath($_SERVER['84PHP_CONFIG']['Vcode']['FontFile']);
		if(!file_exists($Font)){
			Wrong::Report(__FILE__,__LINE__,'Error#M.10.0');
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
		if(!isset($_SESSION)){
			session_start();
		}
		$_SESSION['Vcode']=$Vcode;
		$FontSize=$Height*0.5;
		$NewImg=imagecreate($Width, $Height);
		$BgColor=imagecolorallocate($NewImg,250,250,250);
		$WordRGBColor=$this->HexRGB($WordColor);
		$NoiseRGBColor=$this->HexRGB($NoiseHexColor);
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
		return TRUE;
	}
	
	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}