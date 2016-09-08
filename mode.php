<?php
define("DB_HOST", "HOSTNAME");
define("DB_USER", "USERNAME");
define("DB_PASS", "PASSWORD");
define("DB_NAME", "DATABASENAME");



$pattern_mode='/^(m|M)ode[1-9]{1}/';
$pattern_name='/^(n|N)ame/';

$mode_repository='12';



$repository="
1.抵抗组织阿瓦隆（包含抵抗组织初版）；
2.狼人杀。
";



$mode_name=[
	1=>'抵抗组织阿瓦隆',
	2=>'狼人杀'
];

$textTpl = "<xml>
	 <ToUserName><![CDATA[%s]]></ToUserName>
         <FromUserName><![CDATA[%s]]></FromUserName>
         <CreateTime>%s</CreateTime>
         <MsgType><![CDATA[text]]></MsgType>
         <Content><![CDATA[%s]]></Content>
         <FuncFlag>0</FuncFlag>
         </xml>";
