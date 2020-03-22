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

require(RootPath.'/Config/Img.php');

class Img{

	//颜色转换
	private function HexRGB($HexColor){
		$Hex=hexdec($HexColor);
		return array("red"=>0xFF&($Hex>>0x10),"green"=>0xFF&($Hex>>0x8),"blue"=>0xFF&$Hex);
	}
	
	//图片支持检测
	private function MIMECheck($MIME){
		
		$FileType=str_replace('image/','',$MIME);
		$Support=array_key_exists($FileType,array('bmp'=>'','gd2'=>'','gd'=>'','gif'=>'','jpeg'=>'','png'=>'','vnd.wap.wbmp'=>'','webp'=>'','xbm'=>''));
		
		if($MIME=='vnd.wap.wbmp'){
			$MIME='wbmp';
		}
		
		$FunExists=function_exists('imagecreatefrom'.$FileType);
		
		if(!$Support||!$FunExists){
			Wrong::Report(__FILE__,__LINE__,'Error#M.2.0');
		}
		
	}
	
	//打开图片
	private function GetImage($From,$DataType){
		if($DataType=='path'){
			if(!file_exists($From)){
				Wrong::Report(__FILE__,__LINE__,'Error#M.2.1');
			}
			$Exp=explode('.',$From);
			$MIME=end($Exp);
			if(strtolower($MIME)=='wbmp'){
				$MIME='vnd.wap.wbmp';
			}
			if(strtolower($MIME)=='jpg'){
				$MIME='jpeg';
			}
			$this->MIMECheck(strtolower($MIME));
			if($MIME=='vnd.wap.wbmp'){
				$MIME='wbmp';
			}

			$ImgInfo=@getimagesize($From);
			
			$ImgData=call_user_func('imagecreatefrom'.$MIME,$From);
		}
		else{
			$ImgInfo=@getimagesizefromstring($From);
			$ImgData=call_user_func('imagecreatefromstring',$From);
		}

		if(!$ImgInfo||$ImgData===FALSE){
			Wrong::Report(__FILE__,__LINE__,'Error#M.2.2');
		}
		
		$Return=$ImgInfo;
		$Return['Data']=$ImgData;
		
		return $Return;
		
	}
	
	//输出图片
	private function OutputImage($ImgData,$To,$Quality,$MIME){
		if(empty($To)){
			$To=NULL;
			header('Content-Type: image/'.$MIME);
		}
		else{
			if(!is_dir(dirname($To))){
				die (dirname($To));
				mkdir(dirname($To),0777,TRUE);
			}
			$Exp=explode('.',$To);
			$MIME=end($Exp);
			if(strtolower($MIME)=='wbmp'){
				$MIME='vnd.wap.wbmp';
			}
			if(strtolower($MIME)=='jpg'){
				$MIME='jpeg';
			}
		}
		$this->MIMECheck(strtolower($MIME));
		if($MIME=='png'){
			$Quality=intval($Quality/10);
		}
		
		if(array_key_exists($MIME,array('jpeg'=>'','png'=>'','webp'=>''))){
			$OutPut=call_user_func('image'.$MIME,$ImgData,$To,$Quality);
		}
		
		if(array_key_exists($MIME,array('bmp'=>'','gd2'=>'','gd'=>'','gif'=>'','vnd.wap.wbmp'=>'','xbm'=>''))){
			if($MIME=='vnd.wap.wbmp'){
				$MIME='wbmp';
			}
			$OutPut=call_user_func('image'.$MIME,$ImgData,$To);
		}
		
		if(!$OutPut){
			Wrong::Report(__FILE__,__LINE__,'Error#M.2.3');
		}
	}
	
	//伸缩和水印
	public function Change($UnionData=array()){
		$From=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'image','源图片');
		$DataType=strtolower(QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'data_type','资源类型',FALSE,'path'));
		$To=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'to','目标路径',FALSE,NULL);
		$Width=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'width','宽度',FALSE,NULL);
		$Height=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'height','高度',FALSE,NULL);
		$Scale=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'scale','缩放',FALSE,1.0);
		$Word=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'word','文字',FALSE,NULL);
		$WordSize=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'word_size','文字大小',FALSE,NULL);
		$WordColor=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'word_color','文字颜色',FALSE,'#333333');
		$WordMarginX=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'word_margin_x','文字左边距',FALSE,0);
		$WordMarginY=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'word_margin_y','文字顶边距',FALSE,0);
		$Quality=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'quality','质量',FALSE,75);
		$DataType=strtolower(QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'data_type','资源类型',FALSE,'path'));
		$MIME=strtolower(QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'mime','图片格式',FALSE,'jpeg'));
		
		if($DataType!='path'){
			$DataType='string';
		}
		else{
			$From=AddRootPath($From);
		}

		if(!empty($To)){
			$To=AddRootPath($To);
		}
		
		$WordColorArray=array("red"=>80,"green"=>80,"blue"=>80);
		$ImgInfo=$this->GetImage($From,$DataType);
		
		if(empty($Width)&&empty($Height)){
			$NewWidth=round($ImgInfo[0]*$Scale);
			$NewHeight=round($ImgInfo[1]*$Scale);
		}
		else{
			if(empty($Width)){
			$NewWidth=round($ImgInfo[0] * ($Height/$ImgInfo[1]));
			}
			else{
				$NewWidth=$Width;
			}
			if(empty($Height)){
				$NewHeight=round($ImgInfo[1] * ($Width/$ImgInfo[0]));
			}
			else{
				$NewHeight=$Height;
			}
		}
		$NewImg=imagecreatetruecolor($NewWidth,$NewHeight);
		if(!$NewImg){
			Wrong::Report(__FILE__,__LINE__,'Error#M.2.4');
		}
		imagecopyresampled($NewImg,$ImgInfo['Data'],0,0,0,0,$NewWidth,$NewHeight,$ImgInfo[0],$ImgInfo[1]);
		if(!empty($Word)){
			if(empty($WordSize)){
				$WordSize=$NewHeight*0.12;
			}
			if($WordColor!=NULL){
				$WordColorArray=$this->HexRGB($WordColor);
			}
			if(!imagettftext($NewImg,$FontSize,0,$WordMarginX,$WordMarginY,$textcolor1,AddRootPath($_SERVER['84PHP_CONFIG']['Img']['FontFile']),$Word)){
				Wrong::Report(__FILE__,__LINE__,'Error#M.2.5');
			}
		}
		
		$this->OutputImage($NewImg,$To,$Quality,$MIME);
		
		imagedestroy($ImgInfo['Data']);
		imagedestroy($NewImg);
	}
	
	//合并图片
	public function Merge($UnionData=array()){
		$Background=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'background','背景');
		$Foreground=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'foreground','前景');
		$DataType=strtolower(QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'data_type','资源类型',FALSE,'path'));
		$To=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'to','目标路径',FALSE,NULL);
		$ImageX=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'image_x','起始X',FALSE,0);
		$ImageY=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'image_y','起始Y',FALSE,0);
		$Scale=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'scale','缩放',FALSE,1.0);
		$Quality=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'quality','质量',FALSE,75);
		$MIME=strtolower(QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'mime','图片类型',FALSE,'jpeg'));
		
		if(!empty($To)){
			$To=AddRootPath($To);
		}
		
		if($DataType!='path'){
			$DataType='string';
		}
		else{
			$Background=AddRootPath($Background);
			$Foreground=AddRootPath($Foreground);
		}

		
		$BgImageInfo=$this->GetImage($Background,$DataType);
		$FgImageInfo=$this->GetImage($Foreground,$DataType);

		imagecopyresampled($BgImageInfo['Data'],$FgImageInfo['Data'],$ImageX,$ImageY,0,0,intval($FgImageInfo[0]*$Scale),intval($FgImageInfo[1]*$Scale),$FgImageInfo[0],$FgImageInfo[1]);
		
		$this->OutputImage($BgImageInfo['Data'],$To,$Quality,$MIME);
		
		imagedestroy($BgImageInfo['Data']);
		imagedestroy($FgImageInfo['Data']);
	}
	
	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}