<?php
namespace core;

use Exception;
use PDO;
use PDOException;


/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.1.0
*/

class Db
{
    private static $DbHandle;
    private static $NowDb;
    private static $Stmts;
    
    private static function initial(): bool
    {
        if(!empty($_SERVER['84PHP']['Runtime']['Db']['initial'])){
            return TRUE;
        }

        self::connect();
        
        $_SERVER['84PHP']['Runtime']['Db']['initial']=1;
        return TRUE;
    }
    
    //选择数据库
    public static function choose($ChooseDb)
    {
        self::$DbHandle=null;
        self::$NowDb=$ChooseDb;
        self::connect();
    }
    
    //连接数据库
    private static function connect()
    {
        if (empty(self::$NowDb)) {
            self::$NowDb='default';
        }
        self::$Stmts=[];

        if (empty($_SERVER['84PHP']['Config']['Db']['dbInfo'][self::$NowDb])) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.8.0','code'=>'M.8.0']);
        }
        $Dsn=$_SERVER['84PHP']['Config']['Db']['dbInfo'][self::$NowDb]['type'].
            ':host='.$_SERVER['84PHP']['Config']['Db']['dbInfo'][self::$NowDb]['address'].
            ';port='.$_SERVER['84PHP']['Config']['Db']['dbInfo'][self::$NowDb]['port'].
            ';dbname='.$_SERVER['84PHP']['Config']['Db']['dbInfo'][self::$NowDb]['dbname'].
            ';charset='.$_SERVER['84PHP']['Config']['Db']['dbInfo'][self::$NowDb]['charset'];
        try {
            self::$DbHandle=@new PDO($Dsn,$_SERVER['84PHP']['Config']['Db']['dbInfo'][self::$NowDb]['username'],$_SERVER['84PHP']['Config']['Db']['dbInfo'][self::$NowDb]['password']);
            self::$DbHandle->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $Error) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.8.1'."\r\n\r\n @ ".'ErrorInfo: ('.$Error->getCode().') '.$Error->getMessage(),'code'=>'M.8.1']);
        }
    }

    //参数检查
    private static function parameterCheck($UnionData,$Extra=[]): array
    {
        $Parameters=[
            'table'=>Common::quickParameter($UnionData,'table','表'),
            'field'=>Common::quickParameter($UnionData,'field','字段',FALSE,[]),
            'value'=>Common::quickParameter($UnionData,'value','值',FALSE,[]),
            'condition'=>Common::quickParameter($UnionData,'condition','条件',FALSE,'='),
            'order'=>Common::quickParameter($UnionData,'order','顺序',FALSE),
            'desc'=>Common::quickParameter($UnionData,'desc','降序',FALSE,FALSE),
            'limit'=>Common::quickParameter($UnionData,'limit','限制',FALSE),
            'index'=>Common::quickParameter($UnionData,'index','索引',FALSE),
            'sql'=>Common::quickParameter($UnionData,'sql','sql',FALSE),
            'bind'=>Common::quickParameter($UnionData,'bind','绑定',FALSE,[]),
        ];

        $ExtraParameters=[
            'data'=>['data','数据',TRUE,NULL],
            'sumField'=>['sum','合计',TRUE,NULL],
            'fieldLimit'=>['field_limit','字段限制',FALSE,NULL],
            'rowCount'=>['row_count','行数统计',FALSE,FALSE],
            'autoOp'=>['auto_operate','自动操作',FALSE,NULL],
            'groupBy'=>['group_by','分组',FALSE,NULL],
        ];

        foreach($ExtraParameters as $Key =>$Val){
            if(in_array($Key,$Extra)){
                $Parameters[$Key]=Common::quickParameter($UnionData,$Val[0],$Val[1],$Val[2],$Val[3]);
            }
        }

        return $Parameters;
    }

    //创建绑定
    private static function createBind($PreSql): string
    {
        $StmtKey=md5($PreSql);
        if (empty(self::$Stmts[$StmtKey])) {
            self::$Stmts[$StmtKey]=self::$DbHandle->prepare($PreSql);
        }
        return $StmtKey;
    }

    //绑定参数
    private static function bindData($StmtKey,$Field,$Data,$Tag='',$Mix=FALSE)
    {
        if (!$Mix) {
            foreach ($Field as $Key => $Val) {
                self::$Stmts[$StmtKey]->bindValue(':'.$Tag.$Val, $Data[$Key]);
            }
        }
        else {
            foreach ($Data as $Key => $Val) {
                self::$Stmts[$StmtKey]->bindValue(':'.$Tag.$Key, $Val);
            }

        }
    }

    //执行预处理
    private static function execBind($StmtKey,$PreSql,$Action='')
    {
        self::sqlLog($PreSql);

        try {
            self::$Stmts[$StmtKey]->execute();
        }
        catch(PDOException $Error) {
            $ModuleError='Detail: '.$Error->getMessage().' | SQL String: '.$PreSql.' | errno:'.$Error->getCode();
            Api::wrong(['level'=>'F','detail'=>'Error#M.8.2'."\r\n\r\n @ ".$ModuleError,'code'=>'M.8.2']);
        }

        if ($Action=='Fetch') {
            return self::$Stmts[$StmtKey]->fetch(PDO::FETCH_ASSOC);
        }
        elseif ($Action=='FetchAll') {
            return self::$Stmts[$StmtKey]->fetchAll(PDO::FETCH_ASSOC);
        }
        elseif ($Action=='InsertId') {
            return self::$DbHandle->lastInsertId();
        }
        elseif ($Action=='RowCount') {
            return self::$Stmts[$StmtKey]->rowCount();
        }
        else {
            return NULL;
        }

    }

    //写入日志
    private static function sqlLog($Sql)
    {
        if ($_SERVER['84PHP']['Config']['Db']['log']) {
            Log::add(['level'=>'debug','info'=>'[SQL] '.$Sql]);
        }
    }

    //获取表列表
    private static function getTableList($TableData)
    {
        $TableList='';
        if (is_array($TableData)) {
            foreach ($TableData as $Val) {
                $TableList.=' '.$Val.' ,';
            }
            return substr($TableList,0,-1);
        }
        else {
            return ' '.$TableData;
        }
    }

    //获取字段列表
    private static function getFieldList($FieldData,$Default)
    {
        $FieldList='';
        if (!empty($FieldData)) {
            if (is_string($FieldData)) {
                return ' '.$FieldData;
            }
            elseif (is_array($FieldData)) {
                foreach ($FieldData as $Val) {
                    $FieldList.=' '.$Val.' ,';
                }
                return substr($FieldList,0,-1);
            }
        }
        return $Default;
    }

    //查询条件转SQL语句
    private static function queryToSql($Para): string
    {


        if (empty($Para['condition'])) {
            $Para['condition']='=';
        }

        $WhereSql='';
        foreach ($Para['field'] as $Key => $Val) {
            if ($WhereSql=='') {
                $WhereSql=' WHERE';
            }

            if (!is_array($Para['condition'])||empty($Para['condition'][$Key])) {
                $TempCo=['=','AND'];
                $WhereSql.=' '.$Val.' '.$TempCo[0].' :_Where_'.$Val;
            }
            elseif (!is_array($Para['condition'][$Key])) {
                if (strpos($Para['condition'][$Key],',')===FALSE) {
                    $Para['condition'][$Key]=str_replace(' ','',$Para['condition'][$Key]);
                    $TempCo=[$Para['condition'][$Key],'AND'];
                }
                else {
                    $Para['condition'][$Key]=str_replace(' ','',$Para['condition'][$Key]);
                    $TempCo=explode(',',$Para['condition'][$Key]);
                    if (empty($TempCo[1])) {
                        $TempCo[1]='AND';
                    }
                }
                $WhereSql.=' '.$Val.' '.$TempCo[0].' :_Where_'.$Val;
            }
            else {
                if (empty($Para['condition'][$Key][0])) {
                    $TempCo=['=','AND'];
                }
                elseif (strpos($Para['condition'][$Key][0],',')===FALSE) {
                    $Para['condition'][$Key][0]=str_replace(' ','',$Para['condition'][$Key][0]);
                    $TempCo=[$Para['condition'][$Key][0],'AND'];
                }
                else {
                    $Para['condition'][$Key][0]=str_replace(' ','',$Para['condition'][$Key][0]);
                    $TempCo=explode(',',$Para['condition'][$Key][0]);
                    if (empty($TempCo[1])) {
                        $TempCo[1]='AND';
                    }
                }
                $TempBeforeTag='';
                $TempAfterTag='';
                if (!empty($Para['condition'][$Key][1])) {
                    if (strpos($Para['condition'][$Key][1],',')===FALSE) {
                        $Para['condition'][$Key][1]=str_replace(' ','',$Para['condition'][$Key][1]);
                        $TempBeforeTag=$Para['condition'][$Key][1];
                    }
                    else {
                        $Para['condition'][$Key][1]=str_replace(' ','',$Para['condition'][$Key][1]);
                        $TempTag=explode(',',$Para['condition'][$Key][1]);
                        $TempBeforeTag=$TempTag[0];
                        $TempAfterTag=$TempTag[1];
                    }
                }

                $WhereSql.=' '.$TempBeforeTag.$Val.' '.$TempCo[0].' :_Where_'.$Val.' '.$TempAfterTag;
            }
            if ($Key<(count($Para['field'])-1)) {
                $WhereSql.=' '.$TempCo[1];
            }
        }
        if (is_string($Para['order'])) {
            $OrderSql=' ORDER BY '.$Para['order'];
            if ($Para['desc']) {
                $OrderSql.=' DESC';
            }
        }
        elseif (is_array($Para['order'])) {
            $OrderSql=' ORDER BY ';
            foreach ($Para['order'] as $Key => $Val) {
                if (!empty($Val)) {
                    $OrderSql.=$Val;
                    if ($Para['desc']||(isset($Para['desc'][$Key])&&$Para['desc'][$Key])) {
                        $OrderSql.=' DESC';
                    }
                    $OrderSql.=',';
                }
            }
            $OrderSql=substr($OrderSql,0,-1);
        }
        else {
            $OrderSql='';
        }
        if (!empty($Para['index'])) {
            $IndexSql=' FORCE INDEX('.$Para['index'].')';
        }
        else {
            $IndexSql='';
        }

        $LimitSql='';
        if (is_array($Para['limit'])) {
            if (!empty($Para['limit'][1])) {
                $LimitSql=' LIMIT '.intval($Para['limit'][0]).','.intval($Para['limit'][1]);
            }
            elseif (isset($Para['limit'][0])) {
                $LimitSql=' LIMIT 0,'.intval($Para['limit'][0]);
            }
        }

        if (!empty($Para['groupBy'])) {
            $GroupBySql='GROUP BY '.self::getFieldList($Para['groupBy'],'');
        }
        else {
            $GroupBySql='';
        }

        return $WhereSql.$OrderSql.$LimitSql.$IndexSql.$GroupBySql.' '.$Para['sql'];
    }

    //查询一条数据
    public static function select($UnionData=[])
    {
        $Para=self::parameterCheck($UnionData,['fieldLimit']);

        self::initial();

        $Para['limit']=[1];
        $Para['groupBy']=NULL;

        $QueryString='SELECT '.self::getFieldList($Para['fieldLimit'],'*').' FROM'.self::getTableList($Para['table']).self::queryToSql($Para);

        $StmtKey=self::createBind($QueryString);
        self::bindData($StmtKey,$Para['field'],$Para['value'],'_Where_');
        self::bindData($StmtKey,[],$Para['bind'],'',TRUE);

        return self::execBind($StmtKey,$QueryString,'Fetch');
    }
    
    //查询多条数据
    public static function selectMore($UnionData=[])
    {
        $Para=self::parameterCheck($UnionData,['fieldLimit','groupBy']);

        self::initial();

        $QueryString='SELECT '.self::getFieldList($Para['fieldLimit'],'*').' FROM'.self::getTableList($Para['table']).self::queryToSql($Para);

        $StmtKey=self::createBind($QueryString);
        self::bindData($StmtKey,$Para['field'],$Para['value'],'_Where_');
        self::bindData($StmtKey,[],$Para['bind'],'',TRUE);
 
        return self::execBind($StmtKey,$QueryString,'FetchAll');
    }
        
    //记录总数
    public static function total($UnionData=[])
    {
        $Para=self::parameterCheck($UnionData,['fieldLimit','groupBy']);

        self::initial();

        $Para['fieldLimit']='';
        if (!empty($Para['groupBy'])) {
            $Para['fieldLimit'].=self::getFieldList($Para['groupBy'],'').',';
        }
        
        $QueryString='SELECT '.$Para['fieldLimit'].' COUNT(*) AS Total FROM'.self::getTableList($Para['table']).self::queryToSql($Para);
        
        $StmtKey=self::createBind($QueryString);
        self::bindData($StmtKey,$Para['field'],$Para['value'],'_Where_');
        self::bindData($StmtKey,[],$Para['bind'],'',TRUE);

        $Return=self::execBind($StmtKey,$QueryString,'FetchAll');

        if (!empty($Para['groupBy'])) {
            return $Return;
        }
        else {
            return $Return[0]['Total'];
        }
            
    }
    
    //求和
    public static function sum($UnionData=[]): array
    {
        $Para=self::parameterCheck($UnionData,['sumField']);

        self::initial();

        $SumSql='';
        foreach ($Para['sumField'] as $Key => $Val) {
            $SumSql.=' SUM('.$Key.')'.' AS '.$Val.',';
        }
        $SumSql=substr($SumSql,0,-1);

        $Para['groupBy']=NULL;
        $QueryString='SELECT'.$SumSql.' FROM'.self::getTableList($Para['table']).self::queryToSql($Para);

        $StmtKey=self::createBind($QueryString);
                
        self::bindData($StmtKey,$Para['field'],$Para['value'],'_Where_');
        self::bindData($StmtKey,[],$Para['bind'],'',TRUE);
        
        $Return=self::execBind($StmtKey,$QueryString,'Fetch');
        foreach ($Return as $Key => $Val) {
            if (empty($Val)) {
                $Return[$Key]=0;
            }
        }
        return $Return;
    }
    
    //插入数据
    public static function insert($UnionData=[])
    {
        $Para=self::parameterCheck($UnionData,['data']);

        self::initial();

        $InsertField=NULL;
        $InsertValue=NULL;
        
        foreach ($Para['data'] as $Key => $Val) {
            $InsertField.=$Key.',';
            $InsertValue.=':_Insert_'.$Key.',';
        }
        $InsertField=substr($InsertField,0,-1);
        $InsertValue=substr($InsertValue,0,-1);
        
        $QueryString='INSERT INTO'.self::getTableList($Para['table']).' ( '.$InsertField.' ) VALUES ( '.$InsertValue.' )'.' '.$Para['sql'];

        $StmtKey=self::createBind($QueryString);
        self::bindData($StmtKey,[],$Para['data'],'_Insert_',TRUE);
        self::bindData($StmtKey,[],$Para['bind'],'',TRUE);

        return self::execBind($StmtKey,$QueryString,'InsertId');
    }
    
    //删除数据
    public static function delete($UnionData=[])
    {
        $Para=self::parameterCheck($UnionData,['rowCount']);

        self::initial();
        
        $Para['groupBy']=NULL;
        $QueryString='DELETE FROM'.self::getTableList($Para['table']).self::queryToSql($Para);

        $StmtKey=self::createBind($QueryString);
        self::bindData($StmtKey,$Para['field'],$Para['value'],'_Where_');
        self::bindData($StmtKey,[],$Para['bind'],'',TRUE);

        return self::execBind($StmtKey,$QueryString,$Para['rowCount']?'RowCount':'');
    }
    
    //更新数据
    public static function update($UnionData=[])
    {
        $Para=self::parameterCheck($UnionData,['data','rowCount','autoOp']);

        self::initial();

        $DataSql=NULL;
        $AutoOpNumber=0;

        foreach ($Para['data'] as $Key => $Val) {
            
            if (!empty($Para['autoOp'][$AutoOpNumber])) {
                $DataSql.=$Key.' = '.$Key.' '.$Para['autoOp'][$AutoOpNumber];
            }
            else {
                $DataSql.=$Key.' = :_Update_'.$Key;
            }
            $DataSql.=',';
            $AutoOpNumber++;
        }
        $DataSql=substr($DataSql,0,-1);
        
        $Para['groupBy']=NULL;
        $QueryString='UPDATE'.self::getTableList($Para['table']).' SET '.$DataSql.self::queryToSql($Para);

        $StmtKey=self::createBind($QueryString);
        self::bindData($StmtKey,$Para['field'],$Para['value'],'_Where_');
        self::bindData($StmtKey,[],$Para['data'],'_Update_',TRUE);
        self::bindData($StmtKey,[],$Para['bind'],'',TRUE);

        return self::execBind($StmtKey,$QueryString,$Para['rowCount']?'RowCount':'');
    }
    
    //查询自定义语句
    public static function other($UnionData=[])
    {
        $Sql=Common::quickParameter($UnionData,'sql','sql',FALSE);
        $Bind=Common::quickParameter($UnionData,'bind','绑定',FALSE,[]);
        $Fetch=Common::quickParameter($UnionData,'fetch_result','取回结果',FALSE,FALSE);

        self::initial();

        $StmtKey=self::createBind($Sql);
        self::bindData($StmtKey,[],$Bind,'',TRUE);

        return self::execBind($StmtKey,$Sql,$Fetch?'FetchAll':'');
    }
    
    //事务
    public static function acid($UnionData=[]): bool
    {
        $Option=Common::quickParameter($UnionData,'option','操作');

        self::initial();

        if ($Option=='begin') {
            try {  
                self::$DbHandle->beginTransaction();
                return TRUE;                
            } catch (Exception $Error) {
                Api::wrong(['level'=>'F','detail'=>'Error#M.8.3'."\r\n\r\n @ ".'Detail: '.$Error->getMessage(),'code'=>'M.8.3']);
            }
        }
        elseif ($Option=='commit') {
            if (!self::$DbHandle->commit()) {
                return FALSE;
            }
            else {
                return TRUE;
            }
        }
        elseif ($Option=='cancel') {
            if (!self::$DbHandle->rollBack()) {
                return FALSE;
            }
            else {
                return TRUE;
            }
        }
        return FALSE;
    }
        
    //调用方法不存在
    public static function __callStatic($Method,$Parameters)
    {
        Common::unknownStaticMethod(__CLASS__,$Method);
    }
}