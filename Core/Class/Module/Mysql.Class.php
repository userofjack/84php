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

  框架版本号：4.0.0
*/

require(RootPath.'/Config/Mysql.php');

class Mysql{
	private $Mysqli;
	private $NowDb;
	
	public function __construct(){
		if($_SERVER['84PHP_CONFIG']['Mysql']['Log']){
			LoadModule('Log','Base');
		}

		if($_SERVER['84PHP_CONFIG']['Mysql']['RW_Splitting']){
			$this->NowDb=$this->RandomDb();
		}
		$this->Connect();
	}
	
	//读写分离随机选择数据库
	private function RandomDb(){
		$AllSql=array();
		foreach($_SERVER['84PHP_CONFIG']['Mysql']['DbInfo'] as $Key => $Val){
			$AllSql[]=$Key;
		}
		return $AllSql[mt_rand(1,(count($AllSql)-1))];
	}
	
	//选择数据库
	public function Choose($ChooseDb){
		$this->Mysqli->close();
		if(empty($_SERVER['84PHP_CONFIG']['Mysql']['DbInfo'][$ChooseDb])){
			Wrong::Report(__FILE__,__LINE__,'Error#M.6.0');
		}
		$this->NowDb=$ChooseDb;
		$this->Connect();
	}
	
	//连接数据库
	private function Connect(){
		if(empty($this->NowDb)){
			$this->NowDb='default';
		}
		$this->Mysqli=@new mysqli($_SERVER['84PHP_CONFIG']['Mysql']['DbInfo'][$this->NowDb]['address'],$_SERVER['84PHP_CONFIG']['Mysql']['DbInfo'][$this->NowDb]['username'],$_SERVER['84PHP_CONFIG']['Mysql']['DbInfo'][$this->NowDb]['password'],$_SERVER['84PHP_CONFIG']['Mysql']['DbInfo'][$this->NowDb]['dbname'],$_SERVER['84PHP_CONFIG']['Mysql']['DbInfo'][$this->NowDb]['port']);
		if($this->Mysqli->connect_errno){
			Wrong::Report(__FILE__,__LINE__,'Error#M.6.1 @ Detail: '.$this->Mysqli->connect_error);
		}
	}
	
	//写入日志
	private function SqlLog($SqL){
		if($_SERVER['84PHP_CONFIG']['Mysql']['Log']){
			$_SERVER['84PHP_LOG'].='[sql] '.$SqL.' <'.strval((intval(microtime(TRUE)*1000)-intval(Runtime*1000))/1000)."s>\r\n";
		}
	}

	//字段名解析
	private function SplitField($FieldName){
		$FieldName=str_replace(' ','',$FieldName);
		$Return=explode('*',$FieldName);
		if(!empty($Return[2])){
			Wrong::Report(__FILE__,__LINE__,'Error#M.6.6');
		}
		if(empty($Return[1])){
			return $Return[0];
		}
		else{
			return $Return[0].'`.`'.$Return[1];
		}
	}
	
	//获取表列表
	private function GetTableList($TableData){
		$TableList='';
		if(is_array($TableData)){
			foreach($TableData as $Val){
				$TableList.=' `'.$Val.'` ,';
			}
			$TableList=substr($TableList,0,-1);
			return $TableList;
		}
		else{
			return ' `'.$TableData.'`';
		}
	}
	
	//获取字段列表
	private function GetFieldList($FieldData,$Default){
		$FieldList='';
		if(!empty($FieldData)){
			if(is_string($FieldData)){
				return ' `'.$this->SplitField($FieldData).'`';
			}
			else if(is_array($FieldData)){
				foreach($FieldData as $Val){
					$FieldList.=' `'.$this->SplitField($Val).'` ,';
				}
				$FieldList=substr($FieldList,0,-1);
				return $FieldList;
			}
		}
		return $Default;
	}

	//查询条件转SQL语句
	private function QueryToSql($OtherSql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,$GroupBy){
		if(empty($Condition)){
			$Condition='=';
		}

		$WhereSql='';
		if((!is_array($Field)&&!is_array($Value))&&!empty($Field)){
			$WhereSql=' WHERE `'.$this->SplitField($Field).'`'.$Condition.'\''.$Value.'\'';
		}
		else if(is_array($Field)&&is_array($Value)){
			$WhereSql=' WHERE';
			foreach($Field as $Key => $Val){
				if(!is_array($Condition)||empty($Condition[$Key])){
					$TempCo=array('=','AND');
					$WhereSql.=' `'.$this->SplitField($Val).'` '.$TempCo[0].' \''.$Value[$Key].'\'';
					if($Key<(count($Field)-1)){
						$WhereSql.=' '.$TempCo[1];
					}
				}
				else if(!is_array($Condition[$Key])){
					if(strpos($Condition[$Key],',')===FALSE){
						$Condition[$Key]=str_replace(' ','',$Condition[$Key]);
						$TempCo=array($Condition[$Key],'AND');
					}
					else{
						$Condition[$Key]=str_replace(' ','',$Condition[$Key]);
						$TempCo=explode(',',$Condition[$Key]);
						if(empty($TempCo[1])){
							$TempCo[1]='AND';
						}
					}
					$WhereSql.=' `'.$this->SplitField($Val).'` '.$TempCo[0].' \''.$Value[$Key].'\'';
					if($Key<(count($Field)-1)){
						$WhereSql.=' '.$TempCo[1];
					}
				}
				else{
					if(empty($Condition[$Key][0])){
						$TempCo=array('=','AND');
					}
					else if(strpos($Condition[$Key][0],',')===FALSE){
						$Condition[$Key][0]=str_replace(' ','',$Condition[$Key][0]);
						$TempCo=array($Condition[$Key][0],'AND');
					}
					else{
						$Condition[$Key][0]=str_replace(' ','',$Condition[$Key][0]);
						$TempCo=explode(',',$Condition[$Key][0]);
						if(empty($TempCo[1])){
							$TempCo[1]='AND';
						}
					}
					$TempBeforeTag='';
					$TempAfterTag='';
					if(empty($Condition[$Key][1])){
					}
					else if(strpos($Condition[$Key][1],',')===FALSE){
						$Condition[$Key][1]=str_replace(' ','',$Condition[$Key][1]);
						$TempBeforeTag=$Condition[$Key][1]; 
					}
					else{
						$Condition[$Key][1]=str_replace(' ','',$Condition[$Key][1]);
						$TempTag=explode(',',$Condition[$Key][1]);
						$TempBeforeTag=$TempTag[0];
						$TempAfterTag=$TempTag[1];
					}

					$WhereSql.=' '.$TempBeforeTag.'`'.$this->SplitField($Val).'` '.$TempCo[0].' \''.$Value[$Key].'\''.$TempAfterTag;
					if($Key<(count($Field)-1)){
						$WhereSql.=' '.$TempCo[1];
					}
				}
			}
		}
		if(is_string($Order)){
			$OrderSql=' ORDER BY `'.$this->SplitField($Order).'`';
			if($Desc){
				$OrderSql.=' DESC';
			}
		}
		else if(is_array($Order)){
			foreach($Order as $Key => $Val){
				if(!empty($Val)){
					$OrderSql.=' ORDER BY `'.$this->SplitField($Val).'`';
					if($Desc||(isset($Desc[$Key])&&$Desc[$Key])){
						$OrderSql.=' DESC';
					}
					$OrderSql.=',';
				}
			}
			$OrderSql=substr($OrderSql,0,-1);
		}
		else{
			$OrderSql='';
		}
		if(!empty($Index)){
			$IndexSql=' FORCE INDEX(`'.$this->SplitField($Index).'`)';
		}
		else{
			$IndexSql='';
		}
		if(is_array($Limit)){
			if(!empty($Limit[1])){
				$LimitSql=' LIMIT '.$Limit[0].','.$Limit[1];
			}
			else if(isset($Limit[0])){
				$LimitSql=' LIMIT 0,'.$Limit[0];
			}
		}
		else{
			$LimitSql='';
		}
		
		if(!empty($GroupBy)){
			$GroupBySql='GROUP BY '.$this->GetFieldList($GroupBy,'');
		}
		else{
			$GroupBySql='';
		}
		
		return $WhereSql.$OrderSql.$LimitSql.$IndexSql.$GroupBySql.' '.$OtherSql;
	}
	
	//查询一条数据
	public function Select($UnionData=array()){
		$Table=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'table','表');
		$Field=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field','字段',FALSE,NULL);
		$Value=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'value','值',FALSE,NULL);
		$Condition=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'order','顺序',FALSE,NULL);
		$Desc=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'desc','降序',FALSE,FALSE);
		$Index=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'sql','sql',FALSE,NULL);

		$FieldLimit=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field_limit','字段限制',FALSE,NULL);		
		$QueryString=$this->QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,array(1),$Index,NULL);

		if($_SERVER['84PHP_CONFIG']['Mysql']['RW_Splitting']&&$this->NowDb=='default'){
			$this->Mysqli->close();
			$this->NowDb=$this->RandomDb();
			$this->Connect();
		}
		
		$QueryString='SELECT '.$this->GetFieldList($FieldLimit,'*').' FROM'.$this->GetTableList($Table).$QueryString;

		$this->SqlLog($QueryString);
		$Result=$this->Mysqli->query($QueryString);
		if(!$Result){
			$ModuleError='Detail: '.$this->Mysqli->error.' | SQL String: '.$QueryString.' | errno:'.$this->Mysqli->errno;
			Wrong::Report(__FILE__,__LINE__,'Error#M.6.2 @ Detail: '.$ModuleError);
		}
		$Return=$Result->fetch_assoc();
		$Result->free();
		if(empty($Return)){
			$Return=array();
			return $Return;
		}
		return $Return;
	}
	
	//查询多条数据
	public function SelectMore($UnionData=array()){
		$Table=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'table','表');
		$Field=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field','字段',FALSE,NULL);
		$Value=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'value','值',FALSE,NULL);
		$Condition=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'order','顺序',FALSE,NULL);
		$Desc=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'desc','降序',FALSE,FALSE);
		$Limit=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'limit','限制',FALSE,NULL);
		$Index=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'sql','sql',FALSE,NULL);

		$FieldLimit=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field_limit','字段限制',FALSE,NULL);		
		$GroupBy=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'group_by','分组',FALSE,NULL);
		$QueryString=$this->QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,$GroupBy);
		
		if($_SERVER['84PHP_CONFIG']['Mysql']['RW_Splitting']&&$this->NowDb=='default'){
			$this->Mysqli->close();
			$this->NowDb=$this->RandomDb();
			$this->Connect();
		}

		$QueryString='SELECT '.$this->GetFieldList($FieldLimit,'*').' FROM'.$this->GetTableList($Table).$QueryString;

		$this->SqlLog($QueryString);
		$Result=$this->Mysqli->query($QueryString,MYSQLI_USE_RESULT);

		if(!$Result){
			$ModuleError='Detail: '.$this->Mysqli->error.' | SQL String: '.$QueryString.' | errno:'.$this->Mysqli->errno;
			Wrong::Report(__FILE__,__LINE__,'Error#M.6.2 @ Detail: '.$ModuleError);
		}
		
		$Return=$Result->fetch_all(MYSQLI_ASSOC);
		$Result->free();
		if(empty($Return)){
			$Return=array();
		}
		return $Return;
	}
		
	//记录总数
	public function Total($UnionData=array()){
		$Table=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'table','表');
		$Field=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field','字段',FALSE,NULL);
		$Value=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'value','值',FALSE,NULL);
		$Condition=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'order','顺序',FALSE,'Total');
		$Desc=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'desc','降序',FALSE,FALSE);
		$Limit=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'limit','限制',FALSE,NULL);
		$Index=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'sql','sql',FALSE,NULL);

		$GroupBy=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'group_by','分组',FALSE,NULL);
		$QueryString=$this->QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,$GroupBy);
						
		if($_SERVER['84PHP_CONFIG']['Mysql']['RW_Splitting']&&$this->NowDb=='default'){
			$this->Mysqli->close();
			$this->NowDb=$this->RandomDb();
			$this->Connect();
		}
		
		$FieldLimit='';
		if(!empty($GroupBy)){
			$FieldLimit.=$this->GetFieldList($GroupBy,'').',';
		}
		
		$QueryString='SELECT '.$FieldLimit.' COUNT(*) AS `Total` FROM'.$this->GetTableList($Table).$QueryString;
		
		$this->SqlLog($QueryString);
		$Result=$this->Mysqli->query($QueryString,MYSQLI_USE_RESULT);

		if(!$Result){
			$ModuleError='Detail: '.$this->Mysqli->error.' | SQL String: '.$QueryString.' | errno:'.$this->Mysqli->errno;
			Wrong::Report(__FILE__,__LINE__,'Error#M.6.2 @ Detail: '.$ModuleError);
		}
		
		$Return=$Result->fetch_all(MYSQLI_ASSOC);
		$Result->free();
		if(!empty($GroupBy)){
			return $Return;
		}
		else{
			return $Return[0]['Total'];
		}
			
	}
	
	//求和
	public function Sum($UnionData=array()){
		$Table=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'table','表');
		$Field=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field','字段',FALSE,NULL);
		$Value=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'value','值',FALSE,NULL);
		$Condition=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'order','顺序',FALSE,NULL);
		$Desc=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'desc','降序',FALSE,FALSE);
		$Limit=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'limit','限制',FALSE,NULL);
		$Index=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'sql','sql',FALSE,NULL);
		
		$SumField=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'sum','合计');		
		$QueryString=$this->QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,NULL);
		
		$SumSql='';
		if(empty($SumField)){
			return array();
		}
		if(is_string($SumField)){
			$SumSql=' SUM(`'.$this->SplitField($SumField).'`) AS `'.$SumResult.'`';
		}
		else if(is_array($SumField)){
			foreach($SumField as $Key => $Val){
				$SumSql.=' SUM(`'.$this->SplitField($Val).'`)'.' AS `'.$Val.'`,';
			}
			$SumSql=substr($SumSql,0,-1);
		}

		$QueryString=$this->QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,NULL);
		
		if($_SERVER['84PHP_CONFIG']['Mysql']['RW_Splitting']&&$this->NowDb=='default'){
			$this->Mysqli->close();
			$this->NowDb=$this->RandomDb();
			$this->Connect();
		}

		$QueryString='SELECT'.$SumSql.' FROM'.$this->GetTableList($Table).$QueryString;

		$this->SqlLog($QueryString);
		$Result=$this->Mysqli->query($QueryString,MYSQLI_USE_RESULT);

		if(!$Result){
			$ModuleError='Detail: '.$this->Mysqli->error.' | SQL String: '.$QueryString.' | errno:'.$this->Mysqli->errno;
			Wrong::Report(__FILE__,__LINE__,'Error#M.6.2 @ Detail: '.$ModuleError);
		}
		
		$Return=$Result->fetch_assoc();
		$Result->free();
		if(empty($Return)){
			$Return=array();
		}
		else{
			foreach($Return as $Key => $Val){
				if(empty($Val)){
					$Return[$Key]=0;
				}
			}
		}
		return $Return;
	}
	
	//插入数据
	public function Insert($UnionData=array()){
		$Table=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'table','表');
		$Data=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'data','数据');
		$Sql=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'sql','sql',FALSE,NULL);

		$InsertField=NULL;
		$InsertValue=NULL;
		
		foreach ($Data as $Key => $Val) {
			$InsertField.='`'.$this->SplitField($Key).'`,';
			$InsertValue.='\''.$Val.'\',';
		}
		$InsertField=substr($InsertField,0,-1);
		$InsertValue=substr($InsertValue,0,-1);
		
		if($_SERVER['84PHP_CONFIG']['Mysql']['RW_Splitting']&&$this->NowDb!='default'){
			$this->Mysqli->close();
			$this->NowDb='default';
			$this->Connect();
		}
		
		$QueryString='INSERT INTO'.$this->GetTableList($Table).' ( '.$InsertField.' ) VALUES ( '.$InsertValue.' )'.' '.$Sql;

		$this->SqlLog($QueryString);
		$Result=$this->Mysqli->query($QueryString,MYSQLI_USE_RESULT);

		if(!$Result){
			$ModuleError='Detail: '.$this->Mysqli->error.' | SQL String: '.$QueryString.' | errno:'.$this->Mysqli->errno;
			Wrong::Report(__FILE__,__LINE__,'Error#M.6.3 @ Detail: '.$ModuleError);
		}

		$Result=$this->Mysqli->insert_id;
		return $Result;
	}
	
	//删除数据
	public function Delete($UnionData=array()){
		$Table=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'table','表');
		$Field=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field','字段',FALSE,NULL);
		$Value=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'value','值',FALSE,NULL);
		$Condition=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'order','顺序',FALSE,NULL);
		$Desc=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'desc','降序',FALSE,FALSE);
		$Limit=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'limit','限制',FALSE,NULL);
		$Index=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'sql','sql',FALSE,NULL);
		$QueryString=$this->QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,NULL);
		
		if($_SERVER['84PHP_CONFIG']['Mysql']['RW_Splitting']&&$this->NowDb!='default'){
			$this->Mysqli->close();
			$this->NowDb='default';
			$this->Connect();
		}
		
		$QueryString='DELETE FROM'.$this->GetTableList($Table).$QueryString;

		$this->SqlLog($QueryString);
		$Result=$this->Mysqli->query($QueryString,MYSQLI_USE_RESULT);

		if(!$Result){
			$ModuleError='Detail: '.$this->Mysqli->error.' | SQL String: '.$QueryString.' | errno:'.$this->Mysqli->errno;
			Wrong::Report(__FILE__,__LINE__,'Error#M.6.2 @ Detail: '.$ModuleError);
		}
	}
	
	//更新数据
	public function Update($UnionData=array()){
		$Table=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'table','表');
		$Field=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'field','字段',FALSE,NULL);
		$Value=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'value','值',FALSE,NULL);
		$Condition=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'order','顺序',FALSE,NULL);
		$Desc=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'desc','降序',FALSE,FALSE);
		$Limit=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'limit','限制',FALSE,NULL);
		$Index=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'sql','sql',FALSE,NULL);

		$Data=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'data','数据');
		$AutoOP=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'auto_operate','自动操作',FALSE,NULL);

		$QueryString=$this->QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,NULL);

		$DataSql=NULL;
		$AutoOPNumber=0;

		foreach ($Data as $Key => $Val){
			
			if(!empty($AutoOP[$AutoOPNumber])){
				$DataSql.='`'.$this->SplitField($Key).'`='.$Key.' '.$AutoOP[$AutoOPNumber];
			}
			else{
				$DataSql.='`'.$this->SplitField($Key).'`=\''.$Val.'\'';
			}
			$DataSql.=',';
			$AutoOPNumber++;
		}
		$DataSql=substr($DataSql,0,-1);
		
		if($_SERVER['84PHP_CONFIG']['Mysql']['RW_Splitting']&&$this->NowDb!='default'){
			$this->Mysqli->close();
			$this->NowDb='default';
			$this->Connect();
		}
		
		$QueryString='UPDATE'.$this->GetTableList($Table).' SET '.$DataSql.$QueryString;

		$this->SqlLog($QueryString);
		$Result=$this->Mysqli->query($QueryString,MYSQLI_USE_RESULT);

		if(!$Result){
			$ModuleError='Detail: '.$this->Mysqli->error.' | SQL String: '.$QueryString.' | errno:'.$this->Mysqli->errno;
			Wrong::Report(__FILE__,__LINE__,'Error#M.6.4 @ Detail: '.$ModuleError);
		}
	}
	
	//查询自定义语句
	public function Other($UnionData=array()){
		$Sql=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'sql','sql',FALSE,NULL);
		$Fetch=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'fetch_result','取回结果',FALSE,FALSE);

		if($_SERVER['84PHP_CONFIG']['Mysql']['RW_Splitting']&&$this->NowDb=='default'){
			$this->Mysqli->close();
			$this->NowDb=$this->RandomDb();
			$this->Connect();
		}

		$this->SqlLog($Sql);
		$Result=$this->Mysqli->query($Sql,MYSQLI_USE_RESULT);

		if(!$Result){
			$ModuleError='Detail: '.$this->Mysqli->error.' | SQL String: '.$Sql.' | errno:'.$this->Mysqli->errno;
			Wrong::Report(__FILE__,__LINE__,'Error#M.6.5 @ Detail: '.$ModuleError);
		}
		
		if($Fetch){
			$Return=$Result->fetch_all(MYSQLI_ASSOC);
			$Result->free();
			if(empty($Return)){
				$Return=array();
			}
		}
		else{
			$Return=$Result;
		}
		return $Return;
	}
	
	//备份
	public function BackUp($UnionData=array()){
		$Path=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'path','路径');

		$Path=AddRootPath($Path);
		
		if(!file_exists($Path)){
			mkdir($Path,0777,TRUE);
		}
		
		$FilePath=$Path.'/'.md5(date("YmdHis").mt_rand(1000000, 9999999).$_SERVER['REMOTE_ADDR']).'.sql';
		
		$Handle=@fopen($FilePath,'w');
		if(!$Handle){
			Wrong::Report(__FILE__,__LINE__,'Error#M.6.7');
		}
		
		$this->Mysqli->query('set names \'utf8\'');
		$SQLContext='set charset utf8;'."\r\n";
		$AllTables=$this->Mysqli->query('show tables');
		while ($Result=$AllTables->fetch_array()){
			$Table=$Result[0];
			$TableField=$this->Mysqli->query("show create table `$Table`");
			$Sql=$TableField->fetch_array();
			$SQLContext.=$Sql['Create Table'].';'."\r\n";
			$TableField->free();
			$TableData=$this->Mysqli->query("select * from `$Table`");
			
			while ($Data=$TableData->fetch_assoc()){
				$Key=array_keys($Data);
				$Key=array_map('addslashes',$Key);
				$Key=join('`,`',$Key);
				$Key='`'.$Key.'`';
				$Val=array_values($Data);
				$Val=array_map('addslashes',$Val);
				$Val=join('\',\'',$Val);
				$Val='\''.$Val.'\'';
				$SQLContext.='insert into `'.$Table.'`('.$Key.') values('.$Val.');'."\r\n";
			}
			$TableData->free();
		}
				
		if(!fwrite($Handle,$SQLContext)){
			Wrong::Report(__FILE__,__LINE__,'Error#M.6.8');
		};
		fclose($Handle);
		$AllTables->free();
	}

	//关闭连接
	public function __destruct(){
		if(!$this->Mysqli->connect_errno){
			$this->Mysqli->close();
		}
	}
	
	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}