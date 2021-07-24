<?php
$Config=[
	'字段'=>[
		'testinput'=>'FALSE',
	],
	'模式'=>'POST'
];

/*
	调用Filter模块中的ByMode()方法检查testinput字段是否存在。
*/

if(!Filter::ByMode($Config)){
	die('<script>alert("错误：数据格式不正确！");window.location.href="/index"</script>');
}


if(empty($Post['testinput'])){
	die('<script>alert("这是用POST方式传递的表单数据，由index.act.php进行处理。\r\n【您没有输入字符！】");window.location.href="/index"</script>');
}
else{
	die('<script>alert("这是用POST方式传递的表单数据，由index.act.php进行处理。\r\n您输入的是：【'.$Post['testinput'].'】");window.location.href="/index"</script>');
}
?>