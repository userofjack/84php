<?php
require_once(RootPath."/Core/Class/Base/Receive.Class.php");
$ClassReceive=new Receive;
$Post=$GLOBALS['ClassReceive']->Post(array('字段'=>array('testinput')));
if(empty($Post['testinput'])){
	die('<script>alert("这是用POST方式传递的表单数据，由index.act.php进行处理。\r\n【您没有输入字符！】");window.location.href="/index"</script>');
}
else{
	die('<script>alert("这是用POST方式传递的表单数据，由index.act.php进行处理。\r\n您输入的是：【'.$Post['testinput'].'】");window.location.href="/index"</script>');
}
?>
