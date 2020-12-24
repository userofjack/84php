<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.0.0
*/

require(RootPath.'/Config/Data.php');

class Data{

	//设置
	public static function Set($UnionData=[]){
		$K=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'key','键');
		$V=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'value','值');
		$Time=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'time','时间',FALSE,0);
	}
	
	//获取
	public static function Get($UnionData=[]){
		$K=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'key','键');		
	}

	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}