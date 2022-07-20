<?php
$_SERVER['84PHP']['Config']['Filter']=[
	'rule'=>[
		'digit'=>'/^\d+$|^\d+[.]?\d+$/',//数值
		'int_digit'=>'/^\d+$/',//正整数
		'float_digit'=>'/^\d+[.]?\d+$/',//小数
		'email'=>'',//邮箱规则使用内置函数，自定义无效
		'ip'=>'',//IP地址规则使用内置函数，自定义无效
		'cn_mobile'=>'/^1[0-9]{10}$/',  //中国大陆11位手机手机号
		'cn_phone'=>'/^0[1-9][0-9]{1,2}\-[0-9]{5,8}$/',  //中国大陆固定电话（分机号请单独验证）
		'cn_idcard'=>'/^[1-9](\d{5})(\d{4})(0|1)(\d)(0|1|2|3)(\d)(\d{3})([0-9]|X)$/',  //中国大陆身份证号
		'cn_name'=>'/^([\u4e00-\u9fa5][\u4e00-\u9fa5·]{1,25})$/',  //中国大陆姓名（含少数民族姓名中的点符号）
		'cn_username'=>'/^[a-zA-Z0-9_\u4e00-\u9fa5]+$/',
		'en_username'=>'/^[0-9a-zA-Z_]+$/',
		'password_base'=>'/^(?=.*?[a-zA-Z])(?=.*?[0-9])[a-zA-Z0-9_\-\+\,\.\!\$\*\(\)\[\]\{\};:<>#@&=]+$/',//普通强度密码，必须含有字母、数字，其它字符仅限于以下字符之内：_-+,.!$*()[]{};:<>#@&=
		'password_middle'=>'/^(?=.*?[a-z])(?=.*?[A-Z])(?=.*?[0-9])[a-zA-Z0-9_\-\+\,\.\!\$\*\(\)\[\]\{\};:<>#@&=]+$/',  //中级强度密码，必须含有大写字母、小写字母、数字，其它字符仅限于以下字符之内：_-+,.!$*()[]{};:<>#@&=
		'password_height'=>'/^(?=.*?[a-z])(?=.*?[A-Z])(?=.*?[0-9])(?=.*?[_\-\+\,\.\!\$\*\(\)\[\]\{\};:<>#@&=])[a-zA-Z0-9_\-\+\,\.\!\$\*\(\)\[\]\{\};:<>#@&=]+$/',  //高级强度密码，必须含有且仅限于大写字母、小写字母、数字、特殊字符，特殊字符仅限于以下字符之内：_-+,.!$*()[]{};:<>#@&=
	]
];