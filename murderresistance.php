<?php
include("murder.php");
include("resistance.php");
include("mode.php");
$postStr=file_get_contents("php://input");

function name($user,$name){
	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
	if (!$link) {
 		die('database fail '.mysqli_error());
	}

	$db_selected = mysqli_select_db($link, DB_NAME);
	if (!$db_selected){
		die('database fail '.mysqli_error());
	}
    $name=substr($name,4);
    $test_name_exist=mysqli_query($link,"SELECT name FROM name WHERE name='".$name."'");
    if(mysqli_num_rows($test_name_exist)){
        mysqli_close($link);
        return "该名字已存在。";
    }
    if(strlen($name)>21){
    	mysqli_close($link);
        return "名字过长。（请输入21个字符以下名字，一个汉字算算个字符。）";
    }
    $test_id_exist=mysqli_query($link,"SELECT id FROM name WHERE id='".$user."'");
    if(mysqli_num_rows($test_id_exist)){
		mysqli_query($link,"UPDATE name SET name='".$name."' WHERE id='".$user."'");
		mysqli_close($link);
	    return "新名字已储存。";
	} else{
		mysqli_query($link,"INSERT INTO name (id, name) VALUES ('".$user."','".$name."')");
		mysqli_close($link);
	    return "名字已储存。";
	}
}

function name_check($user){
	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
	if (!$link) {
 		die('database fail '.mysqli_error());
	}

	$db_selected = mysqli_select_db($link, DB_NAME);
	if (!$db_selected){
		die('database fail '.mysqli_error());
	}
	$test_id_exist=mysqli_query($link,"SELECT id FROM name WHERE id='".$user."'");
	if(mysqli_num_rows($test_id_exist)){
		mysqli_close($link);
        return TRUE;
	} else{
        mysqli_close($link);
		return FALSE;
	}
}

function mode_check($user){
	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
	if (!$link) {
 		die('database fail '.mysqli_error());
	}

	$db_selected = mysqli_select_db($link, DB_NAME);
	if (!$db_selected){
		die('database fail '.mysqli_error());
	}
	$fetch_user=mysqli_query($link,"SELECT id FROM name WHERE id='".$user."' AND mode IS NOT NULL");
	if(mysqli_num_rows($fetch_user)){
		mysqli_close($link);
        return TRUE;
	} else{
        mysqli_close($link);
        return FALSE;
	}
}

function mode_modify($user,$mode){
	global $mode_repository,$repository,$mode_name;
	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
	if (!$link) {
 		die('database fail '.mysqli_error());
	}

	$db_selected = mysqli_select_db($link, DB_NAME);
	if (!$db_selected){
		die('database fail '.mysqli_error());
	}
    $mode=substr($mode,4);
    if (strpos($mode_repository,$mode)!==FALSE){
    	$mode=(int)$mode;
    	mysqli_query($link,"INSERT INTO name (mode) VALUES (".$mode.") WHERE id='".$user."'");
    	mysqli_query($link,"UPDATE name SET mode=".$mode." WHERE id='".$user."'");
    	mysqli_close($link);
    	return "你已更换成".$mode_name[$mode]."模式。";
    } else{
    	mysqli_close($link);
    	return "你所选的模式不存在。现在仅有".$repository;
    }

}

function feedback($postStr){
	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
	if (!$link) {
 		die('database fail '.mysqli_error());
	}

	$db_selected = mysqli_select_db($link, DB_NAME);
	if (!$db_selected){
		die('database fail '.mysqli_error());
	}
	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
	$fromUsername = $postObj->FromUserName;
	$fetch_user=mysqli_query($link,"SELECT mode FROM name WHERE id='".$fromUsername."'");
	$userinfo=mysqli_fetch_assoc($fetch_user);
	switch($userinfo['mode']){
		case 1:
			$result=resistance($postStr);
		break;
		case 2:
			$result=murder($postStr);
		break;
		default:
		 $result= "模式错误。";

	}
	mysqli_close($link);
	return $result;
}
if (!empty($postStr)){
	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
	$fromUsername = $postObj->FromUserName;
	$toUsername = $postObj->ToUserName;
	$form_MsgType = $postObj->MsgType;
	$msgid = $postObj->MsgId;


	if($form_MsgType=="event"){
		$form_Event = $postObj->Event;
		if($form_Event=="subscribe"){
			$contentStr = "感谢您关注桌游魂！请先回复系统‘name’加你的昵称（需要便于玩家识别）建立你的昵称，然后回复系统‘mode’加数字选择模式。现在有".$repository;
			$resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $contentStr);
			echo $resultStr;
			exit;
		}
	} elseif ($form_MsgType=="text"){
		$form_content = trim($postObj->Content);
		if (preg_match($pattern_name,$form_content)){
			$feedback=name($fromUsername,$form_content);
			$resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $feedback);
			echo $resultStr;
			exit;
		}
		if (name_check($fromUsername)){
			if (preg_match($pattern_mode,$form_content)){
				$feedback=mode_modify($fromUsername,$form_content);
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $feedback);
				echo $resultStr;
				exit;
			}
			if (mode_check($fromUsername)){
					$feedback=feedback($postStr);
			} else{
				$feedback="你还未选择模式。请回复系统‘mode’加数字选择模式（‘1’代表抵抗组织阿瓦隆，‘2’代表狼人杀）。同样的方法也可以改变模式。";
			}
		} else{
			$feedback="你的名称还未被系统记录。请回复系统‘name’加你的昵称（需要便于玩家识别）建立你的昵称。同样的方法也可以改变昵称。";
		}
		$resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $feedback);
		echo $resultStr;
		exit;
	}

} else{
	echo "";
	exit;
}
