<?php
$_SERVER['84PHP_CONFIG']['Cache']=array(
	'TPath'=>'/Source/Template',
	'DPath'=>'/Source/Data',

	'CacheMatch'=>array(
					'001_Echo'=>'/\{\$([a-zA-Z_\x7f-\xff][a-zA-Z\[\]\'\'""0-9_\x7f-\xff]*)\}/',
					'002_LoopForeach'=>'/\{(loop|foreach) \$([a-zA-Z_\x7f-\xff][a-zA-Z\[\]\'\'""0-9_\x7f-\xff]*)\}/',
					'003_LoopForeach'=>'/\{\/(loop|foreach)}/',
					'004_IfElse'=>'/\{if(.*?)\}/i',
					'005_IfElse'=>'/\{(else if|elseif) (.*?)\}/i',
					'006_IfElse'=>'/\{else\}/',
					'007_IfElse'=>'/\{\/if\}/',
					'008_Notes'=>'/\{(\#|\*)(.*?)(\#|\*)\}/',
					'009_PHPTag'=>'/\{\?(.*?)\?\}/i'
				),
	'CacheReplace'=>array(
					'001_Echo'=>'<?php echo $\\1; ?>',
					'002_LoopForeach'=>'<?php foreach($\\2 as $Key => $Val) { ?>',
					'003_LoopForeach'=>'<?php } ?>',
					'004_IfElse'=>'<?php if(\\1){ ?>',
					'005_IfElse'=>'<?php }else if(\\2){ ?>',
					'006_IfElse'=>'<?php }else{ ?>',
					'007_IfElse'=>'<?php } ?>',
					'008_Notes'=>'',
					'009_PHPTag'=>'<?php \\1 ?>'
				)
);