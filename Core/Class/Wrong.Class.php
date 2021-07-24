<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.1.0
*/

require(RootPath.'/Config/Wrong.php');
class Wrong{

	public static function Report($UnionData){
		$Detail=QuickParamet($UnionData,'detail','详情');
		$Code=QuickParamet($UnionData,'code','状态码',FALSE,500);
		$Hide=QuickParamet($UnionData,'hide','隐藏',FALSE,TRUE);
		$Log=QuickParamet($UnionData,'log','日志',FALSE,TRUE);
		
		if(isset($_SERVER['84PHP_CONFIG']['Wrong']['CodeReplace'][$Code])){
			$Code=$_SERVER['84PHP_CONFIG']['Wrong']['CodeReplace'][$Code];
		}

		ob_clean();
		$ByAjax=
			(isset($_SERVER["HTTP_X_REQUESTED_WITH"])&&strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=='xmlhttprequest')||
			(isset($_SERVER["HTTP_ACCEPT"])&&stristr($_SERVER["HTTP_ACCEPT"],'application/json'));
		$StyleType=strtoupper($_SERVER['84PHP_CONFIG']['Wrong']['Style']);
		
		if(($StyleType=='AUTO'&&$ByAjax)||$StyleType=='JSON'){
			$Style=file_get_contents(RootPath.'/Config/ErrorJsonStyle.php');
		}
		else{
			$Style=file_get_contents(RootPath.'/Config/ErrorHtmlStyle.php');
		}
		if($Style==FALSE){
			die('Error#M.13.0');
		}
		if(!FrameworkConfig['Debug']&&$Hide){
			$Style=str_replace('{$ErrorInfo}','Error#C.0.4',$Style);
			$Code='C.0.4';
		}
		if(FrameworkConfig['Debug']){
			$StackArray=debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			$Stack='';
			foreach($StackArray as $Key => $Val){
				$Stack.='#'.$Key.' ';
				if(isset($Val['class'])){
					$Stack.=$Val['class'].$Val['type'];
				}
				if(isset($Val['function'])){
					if($Val['function']=='{closure}'){
						$Stack.='{closure}';
					}
					else{
						$Stack.=$Val['function'].'()';
					}
				}
				if(isset($Val['file'])&&isset($Val['line'])){
					$Stack.=' at ['.$Val['file'].':'.$Val['line'].'].';
				}
				$Stack.="\r\n";
			}
			
			$Detail.="\r\n\r\n".' *** Stack ***'."\r\n\r\n".$Stack;
		}
		$Detail=str_replace('\\','/',$Detail);
		if($_SERVER['84PHP_CONFIG']['Wrong']['Log']&&$Log){
			Log::Add(['level'=>'e','info'=>$Detail]);
			Log::Output();
		}
		if($ByAjax){
			$Detail=substr(substr(json_encode(['*'=>$Detail],320),6),0,-2);
		}
		$Style=str_replace('{$ErrorInfo}',$Detail,$Style);
		$Style=str_replace('{$Code}',$Code,$Style);
		$Style=str_replace('{$Version}',FrameworkVersion,$Style);
		
		die($Style);
	}
}