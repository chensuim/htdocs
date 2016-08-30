<?php

function murder($postStr){
	global $roledef,$roleflag,$document;
$roledef=[
	'k' => '狼人',
	'p' => '预言家',
	'w' => '女巫',
	's' => '守卫',
	'q' => '丘比特',
	't' => '盗贼',
	'v' => '村民',
	'f' => '吹笛者',
	'h' => '猎人',
	'i' => '白痴',
	'a' => '白狼王'
];

$roleflag=[
	5 => 'pwvkk',
	8 => 'pwqtvvvkkk',
	9 => 'pwqtsvvkkkk',
	12 => 'apwshvvvvkkk'
];


$pattern_job='/^[a-z]$/';
$pattern_num='/^\d{1,2}$/';
$pattern_join='/^\d{4}$/';
$pattern_staff='/^[a-z]{5,98}$/';

$document="1.本公众号规则基于普通狼人杀，故请自行了解普通规则；
2.本公众号可以作为法官完成第一晚的游戏，第二晚之后请选择一名死去的玩家成为新的法官。故不用特意选出一名玩家当法官不能玩；
3.请回复人数创建房间；
4.特定的人数本公众号有内置的身份配置，然而其他人数没有。如果你不满意内置身份配置或你选的人数没有内置的身份配置，请按以下规则设定身份：
	a. 身份的简称：
		预言家=>p 
		女巫=>w
		猎人=>h
		守卫=>s
		丘比特=>q
		盗贼=>t
		吹笛者=>f
		村民=>v
		狼人=>k
		白痴=>i
		白狼王=>a
	b.请按以下规则回复总身份：
		i.先列出神的种类;
		ii.再列出平民的数量,每一个平民用一个v代替（如有三个平民，请用vvv代替）;
		iii. 再列出狼人的数量,每一个狼人用一个k代替（如有三个狼人，请用kkk代替）;
		iv.请注意总身份量和房间大小的统一（如果有盗贼，总身份量应为总人数＋2）;
		v.示例：8人局，含有预言家，女巫，丘比特，盗贼，三个平民和三个狼人，故回复pwqtvvvkkk;
5.在公众号的操作中含有 三个阶段：
	a.盗贼和丘比特行动阶段;
	b.狼人阶段;
	c.其余神的行动阶段;
	其中，只有第二阶段需要平民(和神）闭眼，并请一位玩家用手机（或其余记时手段）记时90s，狼人应在90s内完成杀人任务，玩家应在90s后集体睁眼。在任意阶段，请回复‘帮助’获取通过这一阶段的方法，在第一和第三阶段，即使你不能行动，也请回复‘99’，否则无法通过这一阶段。在第一和第三阶段结束后，请回复‘结果’知道这一阶段的结果。在第三阶段结束后，请新上任的法官回复‘我是法官’了解游戏信息，并成为新的法官继续主持。
";

	function totalrole($roleflag){
		global $roledef;
		$i=0;
		$countk=substr_count($roleflag,'k');
		$countv=substr_count($roleflag,'v');
		$total="\n狼人 x".$countk."\n村民 x".$countv."\n";
		while (isset($roleflag[$i])){
				if ($roleflag[$i]!='v' AND $roleflag[$i]!='k'){
					$total=$total.$roledef[$roleflag[$i]]."\n";
				}
				$i+=1;
		}
		return $total;
	}


	function roommcheck($user){
		$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
		if (!$link) {
	 		die('database fail '.mysqli_error());
		}

		$db_selected = mysqli_select_db($link, DB_NAME);
		if (!$db_selected){
			die('database fail '.mysqli_error());
		}
	    $test_id_exist = mysqli_query($link,"SELECT roomm FROM playerm WHERE id='".$user."'");
		if (mysqli_num_rows($test_id_exist)){
			mysqli_close($link);
			return TRUE;
		} else {
			mysqli_close($link);
			return FALSE;
		}

	}

	function create($number, $creator){

		global $roledef,$roleflag;
		$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
		if (!$link) {
	 		die('database fail '.mysqli_error());
		}

		$db_selected = mysqli_select_db($link, DB_NAME);
		if (!$db_selected){
			die('database fail '.mysqli_error());
		}
		if ($number>=98 OR $number<5){
			mysqli_close($link);
			return "只允许建造5到98人的房间。";
		}
		$test_roomm_limit = mysqli_query($link,"SELECT COUNT(*) AS totalroomms FROM roomm");
		$row = mysqli_fetch_assoc($test_roomm_limit);
		if ($row['totalroomms'] == 9000){
			mysqli_close($link);
			return '总房间数已达上限。';
		}

		do{
			$roommid = mt_rand(1000,9999);
			$check_roommid_exist = mysqli_query($link,"SELECT roommid FROM roomm WHERE roommid=".$roommid);
		} while (mysqli_num_rows($check_roommid_exist));
		
		 //检测系统有没有内置身份配置。
		if (isset($roleflag[$number])){
			$roleflag1 = str_shuffle($roleflag[$number]);
			if ($roleflag1[0]=='w'){
				mysqli_query($link,"INSERT INTO playerm (id, num, roomm, role, poison, antidote) VALUES ('".$creator."',1, ".$roommid.",'".$roleflag1[0]."',1,1)");
			} else{
				mysqli_query($link,"INSERT INTO playerm (id, num, roomm, role, poison, antidote) VALUES ('".$creator."',1, ".$roommid.",'".$roleflag1[0]."',0,0)");
			}
			mysqli_query($link,"INSERT INTO roomm (roommid, totalnumber, currentnumber, answer, status,roleflag,originroleflag) VALUES (".$roommid.",".$number.",1,0,0,'".$roleflag1."','".$roleflag[$number]."')");
			$result="你已开房。房号为".$roommid."，房间里有".totalrole($roleflag[$number])."快召唤基/姬友一起来嘿嘿嘿吧！\n你的身份是\n".$roledef[$roleflag1[0]]."。如果你不满意这个身份配置，请回复‘规则’，查看修改身份配置的方法。请注意，仅在房间里只有你一人时可以更改房间身份配置。";
		} else{
			mysqli_query($link,"INSERT INTO playerm (id, num, roomm) VALUES ('".$creator."',1, ".$roommid.")");
			mysqli_query($link,"INSERT INTO roomm (roommid, totalnumber, currentnumber, answer, status) VALUES (".$roommid.",".$number.",1,0,0)");
			$result="你已开房。房号为".$roommid."。但是你输入的人数不存在系统内置身份配置。请回复‘规则’查看输入身份配置的方法。请注意，仅在房间里只有你一人时可以更改房间身份配置。";
		}

		mysqli_close($link);
		return $result;
	}

	function joinroomm($roommid, $user){
		global $roledef,$repository;
		$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
		if (!$link) {
	 		die('database fail '.mysqli_error());
		}

		$db_selected = mysqli_select_db($link, DB_NAME);
		if (!$db_selected){
			die('database fail '.mysqli_error());
		}
	    $test_roomm = mysqli_query($link,"SELECT totalnumber, currentnumber,roleflag,originroleflag FROM roomm WHERE roommid=".$roommid);
		if (!mysqli_num_rows($test_roomm)){
			mysqli_close($link);
			return '该房间不存在。你可能进错了模式。回复系统‘mode’加数字换模式。现在有'.$repository;
		}

		$roomminfo = mysqli_fetch_assoc($test_roomm);
		if (!$roomminfo['roleflag']){
			mysqli_close($link);
			return '该房间还没设置身份配置。请让房主先行设置身份配置。';		
		}
		$number = $roomminfo['totalnumber'];
		$current = $roomminfo['currentnumber'];

		if ($number==$current){
			mysqli_close($link);
			return '该房间已满。';
		}

		$current += 1;
		$fetch_roomm=mysqli_query($link,"SELECT roleflag,totalnumber,originroleflag FROM roomm WHERE roommid=".$roommid);
		$roomminfo=mysqli_fetch_assoc($fetch_roomm);
		$roleflag=$roomminfo['roleflag'];
		if ($roleflag[$current-1]=='w'){
			mysqli_query($link,"INSERT INTO playerm (id, num, roomm, role, poison, antidote) VALUES ('".$user."', ".$current.", ".$roommid.",'".$roleflag[$current-1]."',1,1)");
		} else{
			mysqli_query($link,"INSERT INTO playerm (id, num, roomm, role, poison, antidote) VALUES ('".$user."', ".$current.", ".$roommid.",'".$roleflag[$current-1]."',0,0)");
		}
		mysqli_query($link,"UPDATE roomm SET currentnumber=".$current." WHERE roommid=".$roommid);
		$result = "你已加入".$number."人房间。房间里有".totalrole($roomminfo['originroleflag'])."房间号".$roommid."。当前已有".$current."人。".fetchname($user)."\n你的身份是\n".$roledef[$roleflag[$current-1]]."。";
		if ($current==$number){
			if (strpos($roomminfo['originroleflag'],'q')===FALSE AND strpos($roomminfo['originroleflag'],'t')===FALSE){
				mysqli_query($link,"UPDATE roomm SET status=2 WHERE roommid=".$roommid);
				$result=$result."\n房间已满。由于房间没有设置丘比特和盗贼，所以直接进行第二阶段。请各位回复‘帮助’，以获取结束这阶段的方法。";
			} else{
				mysqli_query($link,"UPDATE roomm SET status=1 WHERE roommid=".$roommid);
				$result=$result."\n房间已满。请进行第一阶段。请各位回复‘帮助’，以获取结束这阶段的方法。";
			}
			
		}
		mysqli_close($link);
		return $result;
	}

	function roleflag($msg,$user){
		global $roledef;
		$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
		if (!$link) {
	 		die('database fail '.mysqli_error());
		}

		$db_selected = mysqli_select_db($link, DB_NAME);
		if (!$db_selected){
			die('database fail '.mysqli_error());
		}
		$userinfo=mysqli_query($link,"SELECT roomm FROM playerm WHERE id='".$user."'");
		$userinfo=mysqli_fetch_assoc($userinfo);
	    $roomminfo = mysqli_query($link,"SELECT roleflag, currentnumber,totalnumber FROM roomm WHERE roommid=".$userinfo['roomm']);
	    $roomminfo=mysqli_fetch_assoc($roomminfo);
	    if ($roomminfo['currentnumber']>1){
	    	mysqli_close($link);
	    	return "仅在房间里只有一人时可以修改身份配置。";
	    }
	    if(strpos($msg,'t')===FALSE){
	    	if (strlen($msg)!=$roomminfo['totalnumber']){
	    		mysqli_close($link);
	    		return "请输入与总人数相同的身份配置。";
	    	}
	    } else{
	    	if (strlen($msg)!=($roomminfo['totalnumber']+2)){
	     		mysqli_close($link);
	    		return "您的房间包含盗贼。请输入总人数＋2的身份配置。";   		
	    	}
	    }
	    if(array_intersect(str_split($msg),str_split('bcdegjlmnoruxyz'))){
	    	mysqli_close($link);
	    	return "检测到您输入了未知身份。请重新输入。";  
	    }
	    if (substr_count($msg,'p')>=2){
	    	mysqli_close($link);
	    	return "检测到您输入了多个预言家。请重新输入。";  
	    }
	    if (substr_count($msg,'w')>=2){
	       	mysqli_close($link);
	    	return "检测到您输入了多个女巫。请重新输入。";  
	    }
	    if (substr_count($msg,'s')>=2){
	    	mysqli_close($link);
	    	return "检测到您输入了多个守卫。请重新输入。";  
	    }
	    if (substr_count($msg,'q')>=2){
	    	mysqli_close($link);
	    	return "检测到您输入了多个丘比特。请重新输入。";  
	    }
	    if (substr_count($msg,'t')>=2){
	    	mysqli_close($link);
	    	return "检测到您输入了多个盗贼。请重新输入。";  
	    }
	    if (substr_count($msg,'f')>=2){
	    	mysqli_close($link);
	    	return "检测到您输入了多个吹笛者。请重新输入。";  
	    }
	    if (substr_count($msg,'h')>=2){
	    	mysqli_close($link);
	    	return "检测到您输入了多个猎人。请重新输入。";  
	    }
	    if (substr_count($msg,'i')>=2){
	    	mysqli_close($link);
	    	return "检测到您输入了多个白痴。请重新输入。";  
	    }
	    if (substr_count($msg,'a')>=2){
	    	mysqli_close($link);
	    	return "检测到您输入了多个白狼王。请重新输入。";  
	    }
	    if (substr_count($msg,'k')==0 AND substr_count($msg,'a')==0){
	    	mysqli_close($link);
	    	return "检测到您没有输入狼人。请重新输入。";  
	    }
	    if (substr_count($msg,'v')==0){
	    	mysqli_close($link);
	    	return "检测到您没有输入村民。请重新输入。";  
	    }
	    	$roleflag1=str_shuffle($msg);
	    	if ($roleflag1[0]=='w'){
	    		mysqli_query($link,"UPDATE playerm SET role='".$roleflag1[0]."',poison=1, antidote=1 WHERE id='".$user."'");
	    	} else{
	    		mysqli_query($link,"UPDATE playerm SET role='".$roleflag1[0]."',poison=0, antidote=0 WHERE id='".$user."'");
	    	}
			mysqli_query($link,"UPDATE roomm SET roleflag='".$roleflag1."', originroleflag='".$msg."'WHERE roommid=".$userinfo['roomm']);
			$result="你已修改／创建房间身份配置。现在有".totalrole($msg)."你的房间号是".$userinfo['roomm']."。快叫人来玩吧。你的身份是".$roledef[$roleflag1[0]]."。";
			return $result;
	}

	function command($command, $user){
		global $document,$tasks,$roleflag,$roledef;
		$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
		if (!$link) {
	 		die('database fail '.mysqli_error());
		}

		$db_selected = mysqli_select_db($link, DB_NAME);
		if (!$db_selected){
			die('database fail '.mysqli_error());
		}

		switch($command){
			case 'exit':
			case 'quit':
			case '退出':
				$test_id_exist=mysqli_query($link,"SELECT roomm FROM playerm WHERE id='".$user."'");
					$row = mysqli_fetch_assoc($test_id_exist);
					$roomm = $row['roomm'];
					mysqli_query($link,"DELETE FROM playerm WHERE roomm=".$roomm);
					mysqli_query($link,"DELETE FROM roomm WHERE roommid=".$roomm);
					$result="房间".$roomm."已被注销。请告诉和你在同一房间的人。";
				break;

			case '帮助':
			case 'help':
				$test_id_exist=mysqli_query($link,"SELECT roomm,role FROM playerm WHERE id='".$user."'");
					$userinfo = mysqli_fetch_assoc($test_id_exist);
					$fetch_roomm = mysqli_query($link,"SELECT status, roleflag,totalnumber FROM roomm WHERE roommid=".$userinfo['roomm']);
					$roomminfo = mysqli_fetch_assoc($fetch_roomm);
					$status=$roomminfo['status'];
					$role=$userinfo['role'];
					if ($status==0){
						$result="人还没满，我不能给予你帮助。";
					} elseif ($status==1){
						if ($role=='q'){
							$result="你的房间里有".fetchname($user)."。请你先回复其中一人的编号使其称为情侣中的一位，然后回复剩下一人的编号使其称为另一位情侣。";
						} elseif($role=='t'){
							$roleflag=$roomminfo['roleflag'];
							$totalnumber=$roomminfo['totalnumber'];
							$result="剩下的身份有1.".$roledef[$roleflag[$totalnumber]]."和2.".$roledef[$roleflag[$totalnumber+1]]."。请回复身份前的编号获取身份或回复‘0’保留盗贼身份（无能力但是神）。";
						} else{
							$result="这一阶段供丘比特和盗贼行动，请你回复‘99’。否则无法进入下一阶段。";
						}
					} elseif ($status==2){
						if ($role=='k' OR $role =='a'){
							$result="你的房间里有".fetchname($user)."。你们有90s的时间商量好需要杀死的玩家，然后回复由你们中任意一人回复被杀者编号进行杀害。你们可以自杀，也可以通过回复‘0’不杀人。请务必商量好然后选由一人回复，如果商量不出统一结果，请回复‘0’。";
						} else{
							$result="你应该安安心心睡觉。如果你不小心醒来发现还在黑夜，请让大家闭眼，让狼人们再活动一次。";
						}
					}  elseif($status==3){
						if ($role=='s'){
							$result="你的房间里有".fetchname($user)."。请你回复其中一人编号进行守护。";
						} elseif($role=='w'){
							$fetchkilled=mysqli_query($link,"SELECT id FROM playerm WHERE killed IS NOT NULL AND roomm=".$userinfo['roomm']);
							if(mysqli_num_rows($fetchkilled)){
								$result="今天晚上被狼人杀害的角色是".fetchname($user,1).",你要救吗？请回复‘1’代表救，‘0’代表不救。";
							} else{
								$result="今天晚上没人被杀。请回复人物编号毒人。‘0’代表不毒。你的房间里有：".fetchname($user);
								mysqli_query($link,"UPDATE playerm SET witch=1 WHERE id='".$user."'");
							}	
						} elseif($role=='p'){
							$result="你的房间里有".fetchname($user)."。请你回复其中一人编号进行身份检查。";
						} elseif($role=='f'){
							$result="你的房间里有".fetchname($user)."。请你先回复一人编号进行蛊惑然后回复第二个人编号继续蛊惑。";
						} else{
							$result="这一阶段供女巫，预言家，吹笛者，守卫行动，请你回复‘99’。否则无法进入下一阶段。";
						}
					}
				break;

			case '房间':
			case 'roomm':
				$test_id_exist=mysqli_query($link,"SELECT roomm FROM playerm WHERE id='".$user."'");
				$userinfo=mysqli_fetch_assoc($test_id_exist);
				$result="你的房间里有".fetchname($user);

			break;

			case '身份':
			case 'frie':
				$test_id_exist=mysqli_query($link,"SELECT roomm,role FROM playerm WHERE id='".$user."'");
				$userinfo=mysqli_fetch_assoc($test_id_exist);
				if ($userinfo['role']=='k' OR $userinfo['role']=='a'){
					$result="你的狼人兄弟们是".fetchname($user,7);
				} else{
					$result="你不是狼人，不能知道狼人们是谁。";
				}
			break;

			case '我是法官':
				$test_id_exist=mysqli_query($link,"SELECT roomm FROM playerm WHERE id='".$user."'");
				$userinfo=mysqli_fetch_assoc($test_id_exist);
				$result=fetchname($user,6);
				$fetchwitch=mysqli_query($link,"SELECT poison,antidote FROM playerm WHERE role='w' AND roomm=".$userinfo['roomm']);
				if ($fetchwitch){
					$witchinfo=mysqli_fetch_assoc($fetchwitch);
					if ($witchinfo['poison'] AND $witchinfo['antidote']){
						$result=$result."\n女巫解药毒药都有。";
					} elseif ($witchinfo['poison']){
						$result=$result."\n女巫没了解药，但还有毒药。";
					} elseif ($witchinfo['antidote']){
						$result=$result."\n女巫没了毒药，但还有解药。";
						$fetchhunter=mysqli_query($link,"SELECT poisoned FROM playerm WHERE role='h' AND roomm=".$userinfo['roomm']);
						$hunterinfo=mysqli_fetch_assoc($fetchhunter);
						if ($hunterinfo['poisoned']==1){
							$result=$result."女巫毒死的是猎人";
						}
					} else{
						$result=$result."\n女巫解药毒药都没有啦。";
					}
				} else{
					$result=$result."\n本局游戏中没有女巫这个角色。";
				}

				$fetchshield=mysqli_query($link,"SELECT id FROM playerm WHERE role='s' AND roomm=".$userinfo['roomm']);
				if (mysqli_num_rows($fetchshield)){
					$result=$result."\n上轮守卫守护的人是\n".fetchname($user,4)."守卫不能两轮守护同一个人。";
				} else{
					$result=$result."\n本局游戏中没有守卫这个角色。";
				}

				$fetchflute=mysqli_query($link,"SELECT id FROM playerm WHERE role='f' AND roomm=".$userinfo['roomm']);
				if(mysqli_num_rows($fetchflute)){
					$result=$result."\n被吹笛者迷惑的人是\n".fetchname($user,3);
				} else{
					$result=$result."\n本局游戏中没有吹笛者这个角色。";
				}

				$fetchcupid=mysqli_query($link,"SELECT id FROM playerm WHERE role='q' AND roomm=".$userinfo['roomm']);
				if(mysqli_num_rows($fetchcupid)){
					$result=$result."\n情侣是\n".fetchname($user,2);
				} else{
					$result=$result."\n本局游戏中没有丘比特这个角色。";
				}
				mysqli_query($link,"DELETE FROM playerm WHERE roomm=".$userinfo['roomm']);
				mysqli_query($link,"DELETE FROM roomm WHERE roommid=".$userinfo['roomm']);


				$result=$result."\n你将作为法官继续游戏。房间".$userinfo['roomm']."已被注销。请告诉和你在同一房间的人。";
			break;

			case '警长竞选结束':
			case '警长':
			case 'fini':
				$test_id_exist=mysqli_query($link,"SELECT roomm FROM playerm WHERE id='".$user."'");
				$userinfo=mysqli_fetch_assoc($test_id_exist);
				$fetchroomm=mysqli_query($link,"SELECT status FROM roomm WHERE roommid=".$userinfo['roomm']);
				$roomminfo=mysqli_fetch_assoc($fetchroomm);
				if ($roomminfo['status']==4){
					mysqli_query($link,"UPDATE roomm SET status=5 WHERE roommid=".$userinfo['roomm']);
					$result="警长竞选结束。请回复结果知晓昨晚的故事。特别是猎人，你需要知道你到底是怎么死的。";
				} else{
					$result="我不想知道你现在的阶段，但是警长竞选肯定没有结束（或已经结速的死死的了）。";
				}
			break;	

			case '结果':
			case 'resu':
				$test_id_exist=mysqli_query($link,"SELECT role,roomm,couple,poisoned FROM playerm WHERE id='".$user."'");
				$userinfo=mysqli_fetch_assoc($test_id_exist);
				$fetchroomm=mysqli_query($link,"SELECT totalnumber,status,answer FROM roomm WHERE roommid=".$userinfo['roomm']);
				$roomminfo=mysqli_fetch_assoc($fetchroomm);
				if ($roomminfo['status']==2){
					if ($userinfo['couple']){
						$result="你是情侣之一。";
						$fetchcouple=mysqli_query($link,"SELECT id,num,role FROM playerm WHERE couple IS NOT NULL AND id!='".$user."'");
						#NOT id='".$user."'
						$coupleinfo=mysqli_fetch_assoc($fetchcouple);
						$fetchname=mysqli_query($link,"SELECT name FROM name WHERE id='".$coupleinfo['id']."'");
						$name=mysqli_fetch_assoc($fetchname)['name'];
						if ($coupleinfo['role']=='k' OR $coupleinfo['role']=='a'){
							$result=$result."你的另一半是\n".$coupleinfo['num'].".".$name."。他（她）是狼人。";
						} else{
							$result=$result."你的另一半是\n".$coupleinfo['num'].".".$name."。他（她）不是狼人。";
						}
					} else{
						$result="单身狗你好。";
					}
				} elseif($roomminfo['status']==5){
					$fetchkilled=mysqli_query($link,"SELECT id,num,shielded,couple FROM playerm WHERE killed=1 AND roomm=".$userinfo['roomm']);
					$fetchpoisoned=mysqli_query($link,"SELECT id,num,couple,role FROM playerm WHERE poisoned=1 AND roomm=".$userinfo['roomm']);
					$killed=0;
					$couple=0;
					$poisoned=0;
					if (mysqli_num_rows($fetchkilled)){
						$killedinfo=mysqli_fetch_assoc($fetchkilled);
						if(!$killedinfo['shielded']){
							$killed=1;
							$result=fetchname($user,1)."是第一个死的。";
							if ($killedinfo['couple']){
								$fetchcouple=mysqli_query($link,"SELECT id,num FROM playerm WHERE couple=1 AND id!='".$killedinfo['id']."'");
								$coupleinfo=mysqli_fetch_assoc($fetchcouple);
								$fetchname=mysqli_query($link,"SELECT name FROM name WHERE id='".$coupleinfo['id']."'");
								$name=mysqli_fetch_assoc($fetchname)['name'];
								$result=$result."\n".$coupleinfo['num'].".".$name."是二个死的。";
								$couple=1;//代表有couple被狼人杀害
							}
						}
					}
					if (mysqli_num_rows($fetchpoisoned)){
						$poisoned=1;
						$poisonedinfo=mysqli_fetch_assoc($fetchpoisoned);
						if ($couple){//如果couple＝1，则代表有couple被狼人杀害。
							$result=$result.fetchname($user,5)."是第三个死的。";
						} else{
							if ($poisonedinfo['couple']){
								$fetchcouple=mysqli_query($link,"SELECT id,num FROM playerm WHERE couple=1 AND id!='".$poisonedinfo['id']."'");
								$coupleinfo=mysqli_fetch_assoc($fetchcouple);
								$fetchname=mysqli_query($link,"SELECT name FROM name WHERE id='".$coupleinfo['id']."'");
								$name=mysqli_fetch_assoc($fetchname)['name'];
								$couple=1;
							}
							if ($killed){
								$result=$result."\n".fetchname($user,5)."是第二个死的。";
								if ($couple){
									$result=$result."\n".$coupleinfo['num'].".".$name."是三个死的。";
								}
							} else{
								$result=fetchname($user,5)."是第一个死的。";
								if ($couple){
									$result=$result."\n".$coupleinfo['num'].".".$name."是二个死的。";
								}
							}
						}
					}
					if ($userinfo['role']=='h' AND $userinfo['poisoned']==1){
						$result=$result."\n你是被女巫毒死的，所以不能发动猎人技能。";
					}
					if (!$killed AND !$poisoned){
						$result="昨天是个平安夜。";
					}

				} elseif($roomminfo['status']==4){
					$result="请先进行警长竞选。竞选结束后，请一名玩家回复系统‘警长竞选结束’，然后可以回复系统‘结果’知道昨夜故事。";
				} else{
					$result="这一阶段还没结束。已经有".$roomminfo['answer']."个人完成了这一阶段。还差".($roomminfo['totalnumber']-$roomminfo['answer'])."个人没有完成。请等待。";
				}
			break;
			default:
				$result=$document;

		}
		mysqli_close($link);
		return $result;
	}

	function fetchname($user,$state=0){
		global $roledef;
		$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
		if (!$link) {
	 		die('database fail '.mysqli_error());
		}

		$db_selected = mysqli_select_db($link, DB_NAME);
		if (!$db_selected){
			die('database fail '.mysqli_error());
		}
		$test_id_exist=mysqli_query($link,"SELECT roomm,role FROM playerm WHERE id='".$user."'");
		$userinfo = mysqli_fetch_assoc($test_id_exist);
		if ($state==0){
	        $y=1;
			$fetch_roomm=mysqli_query($link,"SELECT currentnumber FROM roomm WHERE roommid=".$userinfo['roomm']);
	        $currentnumber=mysqli_fetch_assoc($fetch_roomm)['currentnumber'];
			$result="";
			while ($y<=$currentnumber){
	            $fetch_playerm=mysqli_query($link,"SELECT id FROM playerm WHERE roomm=".$userinfo['roomm']." && num=".$y);
	            $playinfo=mysqli_fetch_assoc($fetch_playerm);
				$fetch_name=mysqli_query($link,"SELECT name FROM name WHERE id='".$playinfo['id']."'");
				$name=mysqli_fetch_assoc($fetch_name);
	            $result=$result."\n  $y.".$name['name'];
	            $y=$y+1;
			}
		} elseif($state==1){ //killed
	        $fetch_playerm=mysqli_query($link,"SELECT id,num FROM playerm WHERE roomm=".$userinfo['roomm']." && killed=1");
	        $playinfo=mysqli_fetch_assoc($fetch_playerm);
			$fetch_name=mysqli_query($link,"SELECT name FROM name WHERE id='".$playinfo['id']."'");
			$name=mysqli_fetch_assoc($fetch_name);
	        $num=$playinfo['num'];
	        $result="\n$num.".$name['name'];
		} elseif($state==2){ //couple
			$fetch_playerm=mysqli_query($link,"SELECT id,num FROM playerm WHERE roomm=".$userinfo['roomm']." && couple=1");
	        $playinfo=mysqli_fetch_assoc($fetch_playerm);
			$fetch_name=mysqli_query($link,"SELECT name FROM name WHERE id='".$playinfo['id']."'");
			$name=mysqli_fetch_assoc($fetch_name);
	        $num=$playinfo['num'];
	        $result="$num.".$name['name'];
	        $playinfo=mysqli_fetch_assoc($fetch_playerm);
			$fetch_name=mysqli_query($link,"SELECT name FROM name WHERE id='".$playinfo['id']."'");
			$name=mysqli_fetch_assoc($fetch_name);
	        $num=$playinfo['num'];
	        $result=$result."\n$num.".$name['name'];	
		} elseif($state==3){ //flute
			$fetch_playerm=mysqli_query($link,"SELECT id,num FROM playerm WHERE roomm=".$userinfo['roomm']." && flute=1");
	        $playinfo=mysqli_fetch_assoc($fetch_playerm);
			$fetch_name=mysqli_query($link,"SELECT name FROM name WHERE id='".$playinfo['id']."'");
			$name=mysqli_fetch_assoc($fetch_name);
	        $num=$playinfo['num'];
	        $result="$num.".$name['name'];
	        if (mysqli_num_rows($fetch_playerm)>=2){
		        $playinfo=mysqli_fetch_assoc($fetch_playerm);
				$fetch_name=mysqli_query($link,"SELECT name FROM name WHERE id='".$playinfo['id']."'");
				$name=mysqli_fetch_assoc($fetch_name);
		        $num=$playinfo['num'];
		        $result=$result."\n$num.".$name['name'];
		    }	
		} elseif($state==4){ //shielded
			$fetch_playerm=mysqli_query($link,"SELECT id,num FROM playerm WHERE roomm=".$userinfo['roomm']." && shielded=1");
	        $playinfo=mysqli_fetch_assoc($fetch_playerm);
			$fetch_name=mysqli_query($link,"SELECT name FROM name WHERE id='".$playinfo['id']."'");
			$name=mysqli_fetch_assoc($fetch_name);
	        $num=$playinfo['num'];
	        $result="$num.".$name['name'];
		} elseif($state==5){ //poisoned
			$fetch_playerm=mysqli_query($link,"SELECT id,num FROM playerm WHERE roomm=".$userinfo['roomm']." && poisoned=1");
	        $playinfo=mysqli_fetch_assoc($fetch_playerm);
			$fetch_name=mysqli_query($link,"SELECT name FROM name WHERE id='".$playinfo['id']."'");
			$name=mysqli_fetch_assoc($fetch_name);
	        $num=$playinfo['num'];
	        $result="$num.".$name['name'];
		} elseif ($state==6){ //staff role
	        $y=1;
			$fetch_roomm=mysqli_query($link,"SELECT totalnumber FROM roomm WHERE roommid=".$userinfo['roomm']);
	        $totalnumber=mysqli_fetch_assoc($fetch_roomm)['totalnumber'];
			$result="";
			while ($y<=$totalnumber){
	            $fetch_playerm=mysqli_query($link,"SELECT id,role FROM playerm WHERE roomm=".$userinfo['roomm']." && num=".$y);
	            $playinfo=mysqli_fetch_assoc($fetch_playerm);
				$fetch_name=mysqli_query($link,"SELECT name FROM name WHERE id='".$playinfo['id']."'");
				$name=mysqli_fetch_assoc($fetch_name);
	            $result=$result." $y.".$name['name']."的身份是".$roledef[$playinfo['role']]."\n";
	            $y=$y+1;
			}

		} elseif ($state==7){//查找狼人
	        $y=1;
			$fetch_roomm=mysqli_query($link,"SELECT currentnumber,totalnumber FROM roomm WHERE roommid=".$userinfo['roomm']);
			$roomminfo=mysqli_fetch_assoc($fetch_roomm);
	        $currentnumber=$roomminfo['currentnumber'];
	        $totalnumber=$roomminfo['totalnumber'];
	        if ($currentnumber==$totalnumber){
				$result="";
				while ($y<=$currentnumber){

		            $fetch_playerm=mysqli_query($link,"SELECT id,role FROM playerm WHERE roomm=".$userinfo['roomm']." && num=".$y);
		            $playinfo=mysqli_fetch_assoc($fetch_playerm);
		            if ($playinfo['role']=='k'){
						$fetch_name=mysqli_query($link,"SELECT name FROM name WHERE id='".$playinfo['id']."'");
						$name=mysqli_fetch_assoc($fetch_name);
			            $result=$result."\n  $y.".$name['name'];
			        }
			        if ($playinfo['role']=='a'){
						$fetch_name=mysqli_query($link,"SELECT name FROM name WHERE id='".$playinfo['id']."'");
						$name=mysqli_fetch_assoc($fetch_name);
			            $result=$result."\n  $y.".$name['name']."（白狼王）";		        	
			        }
			        $y=$y+1;
				}
			} else{
				$result="人还没来齐。现在已有".$currentnumber."人。请等人满后再询问。";
			}
		}
		return $result;
	}	

	function answercheck($answer,$user){
		global $status,$totalnumber,$roomm;
		$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
		if (!$link) {
	 		die('database fail '.mysqli_error());
		}

		$db_selected = mysqli_select_db($link, DB_NAME);
		if (!$db_selected){
			die('database fail '.mysqli_error());
		}
		$reply=[
		2=>"本阶段结束，进入下一阶段。请回复结果知道本阶段结果。请你用手机记时90秒。请大家闭眼，狼人们睁眼互相确认身份。狼人们回复帮助了解行动指南。90秒后狼人闭眼，大家睁眼。",
		4=>"本阶段结束。请先进行警长竞选。警长竞选结束后，请选择一人回复‘警长竞选结束’，然后大家可以回复‘结果’知道昨夜故事。在票死之后，选择一位死人当法官。法官可回复‘我是法官’获取法官该知道的信息，并且本房会被删除。"
		];
							if ($answer==$totalnumber){
								$status=$status+1;
								$result=$reply[$status];
								mysqli_query($link,"UPDATE playerm SET answered = 0 WHERE roomm=".$roomm);
								mysqli_query($link,"UPDATE roomm SET answer=0, status=".$status." WHERE roommid=".$roomm);
							} else{
								mysqli_query($link,"UPDATE playerm SET answered = 1 WHERE id='".$user."'");
								mysqli_query($link,"UPDATE roomm SET answer=".$answer." WHERE roommid=".$roomm);
								$result="请等待其他人完成这一阶段。现在已经有".$answer."个人回复了。还差".($totalnumber-$answer)."人。";
							}
		return $result;
	}

	function anscheck($user){
		$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
		if (!$link) {
	 		die('database fail '.mysqli_error());
		}

		$db_selected = mysqli_select_db($link, DB_NAME);
		if (!$db_selected){
			die('database fail '.mysqli_error());
		}
		$userinfo=mysqli_query($link,"SELECT roomm,answered FROM playerm WHERE id='".$user."'");
		$userinfo=mysqli_fetch_assoc($userinfo);
		$roomminfo=mysqli_query($link,"SELECT answer,totalnumber FROM roomm WHERE roommid=".$userinfo['roomm']);
		$roomminfo=mysqli_fetch_assoc($roomminfo);
		$answer=$roomminfo['answer'];
		$totalnumber=$roomminfo['totalnumber'];
		$rest=$totalnumber-$answer;
		if ($userinfo['answered']){
			mysqli_close($link);
			return "检测到你已完成本阶段任务。已有".$answer."人完成了。还差".$rest."人。";
		} else{
			mysqli_close($link);
			return FALSE;
		}
	}

	function process($msg, $user, $msgid){
		$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
		global $status,$totalnumber,$roomm,$roleflag,$roledef;
		if (!$link) {
	 		die('database fail '.mysqli_error());
		}

		$db_selected = mysqli_select_db($link, DB_NAME);
		if (!$db_selected){
			die('database fail '.mysqli_error());
		}

		$test_id_exist = mysqli_query($link,"SELECT roomm,role,msgid,witch,num FROM playerm WHERE id='".$user."'");
		$userinfo = mysqli_fetch_assoc($test_id_exist);
		if ($userinfo['msgid']==$msgid){
			return;
		}
		mysqli_query($link,"UPDATE playerm SET msgid='".$msgid."' WHERE id='".$user."'");
		$fetchroomm = mysqli_query($link,"SELECT currentnumber,totalnumber,status,answer,roleflag FROM roomm WHERE roommid=".$userinfo['roomm']);
		$roomminfo=mysqli_fetch_assoc($fetchroomm);
		$msg=(int)$msg;
	    $status=$roomminfo['status'];
	    $totalnumber=$roomminfo['totalnumber'];
	    $roomm=$userinfo['roomm'];
		switch ($roomminfo['status']){
			case 0: 
				$result="人还没满，现在已经有".$roomminfo['currentnumber']."人。";
			break;

			case 1:
				if (strpos('qt',$userinfo['role'])===FALSE){
					if ($msg==99){
						$answer=$roomminfo['answer']+1;
						$result="谢谢你的配合。你在本阶段已经没事做了。".answercheck($answer,$user);
					} else{
						$result="现在是第一阶段，丘比特和盗贼可以行动。你请回复‘99’结束这一阶段。";
					}
				} elseif ($userinfo['role']=='q'){
					$idtest=mysqli_query($link,"SELECT id FROM playerm WHERE num=".$msg." AND roomm=".$userinfo['roomm']." AND couple IS NULL");
					//." AND roomm=".$userinfo['roomm']." AND couple=FALSE"num=".$msg." 
					if (mysqli_num_rows($idtest)){
						$id=mysqli_fetch_assoc($idtest)['id'];
						mysqli_query($link,"UPDATE playerm SET couple=1 WHERE id='".$id."'");
						$name=mysqli_query($link,"SELECT name FROM name WHERE id='".$id."'");
						$name=mysqli_fetch_assoc($name)['name'];
						$coupletest=mysqli_query($link,"SELECT id FROM playerm WHERE roomm=".$userinfo['roomm']." AND couple=1");
						$rows=mysqli_num_rows($coupletest);
						if ($rows==2){
							$result=fetchname($user,2)."\n已经变成情侣。你这一阶段任务完成。";
							$answer=$roomminfo['answer']+1;
							$result=$result.answercheck($answer,$user);

						} else{
							$result=$name."\n已经变成情侣之一。请回复下一位情侣的编号，你的房间里有".fetchname($user);
						}
					} else{
						$result="该玩家不存在或者他（她）已经是情侣之一了。";
					}
				} else{
					if ($msg==1 OR $msg==2){
						mysqli_query($link,"UPDATE playerm SET role='".$roomminfo['roleflag'][$roomminfo['totalnumber']+$msg-1]."' WHERE id='".$user."'");

						$result="你已经变成了".$roledef[$roomminfo['roleflag'][$roomminfo['totalnumber']+$msg-1]]."。";
						if ($roomminfo['roleflag'][$roomminfo['totalnumber']+$msg-1]!='q'){
							$answer=$roomminfo['answer']+1;
							$result=$result.answercheck($answer,$user);
						} else{
							$result=$result.command($link,"帮助",$user);
							
						}
					} elseif ($msg==0){
						$result="你选择继续是盗贼。";
						$answer=$roomminfo['answer']+1;
						$result=$result.answercheck($answer,$user);
					} else{
						$result="你只有两个选项，请回复帮助查看。";
					}
				}
			break;
			case 2:
				if ($userinfo['role']=='k' OR $userinfo['role']=='a'){
					if ($msg<=$roomminfo['totalnumber']AND $msg>0){
						mysqli_query($link,"UPDATE playerm SET killed=1 WHERE num=".$msg." AND roomm=".$userinfo['roomm']);

						$result=fetchname($user,1)."已被杀，请把这个消息告诉你的伙伴然后请闭眼等待倒计时结束。";
						mysqli_query($link,"UPDATE roomm SET status=3 WHERE roommid=".$userinfo['roomm']);
					} elseif($msg==0){
						$result="你们选择不杀人。请把这个消息告诉你的伙伴然后请闭眼等待倒计时结束。";
						mysqli_query($link,"UPDATE roomm SET status=3 WHERE roommid=".$userinfo['roomm']);
					} else{
						$result="不存在这个人。请选择正确的人杀害。";
					}		
				} else{
					$result="现在是狼人时间。";
				}
			break;
			case 3:
				switch($userinfo['role']){
					case 'w':
						if ($userinfo['witch']){
							if ($msg==0){
								$answer=$roomminfo['answer']+1;
								$result="你选择不毒人。";
								$result=$result.answercheck($answer,$user);
							}
							if ($msg<=$roomminfo['totalnumber'] AND $msg > 0){
								$fetchcorpse=mysqli_query($link,"SELECT num FROM playerm WHERE killed=1 AND roomm=".$userinfo['roomm']);
								$num=mysqli_fetch_assoc($fetchcorpse)['num'];
								if ($msg==$num){
									$result="请不要鞭尸。";
								} else{
									mysqli_query($link,"UPDATE playerm SET poisoned=1 WHERE num=".$msg." AND roomm=".$userinfo['roomm']);
									mysqli_query($link,"UPDATE playerm SET answered=1, poison=0 WHERE id='".$user."'");
									$answer=$roomminfo['answer']+1;
									$result=fetchname($user,5)."已被你毒死。你没有毒药了。";
									$result=$result.answercheck($answer,$user);


								}
							} else{
								$result="该玩家不存在。";
							}
						} else{
						if ($msg==1){
								$result=fetchname($user,1)."已经被你救活。你没有解药了。一个晚上只可以用一瓶药，所以你的这一阶段结束了。";
								mysqli_query($link,"UPDATE playerm SET killed =0 WHERE roomm=".$userinfo['roomm']);
								mysqli_query($link,"UPDATE playerm SET antidote=0, witch=1,answered=1 WHERE id='".$user."'");
								$answer=$roomminfo['answer']+1;
								$result=$result.answercheck($answer,$user);
							} elseif ($msg==0){
								$result="你选择不救".fetchname($user,1)."。请回复人物编号毒人。‘0’代表不毒。你的房间里有：".fetchname($user)."当然，你不能毒已经被咬的人。";
								mysqli_query($link,"UPDATE playerm SET witch=1 WHERE id='".$user."'");
							} else{
								$result="‘1’代表救，‘0’代表不救。回复帮助可以获知被杀害人信息。";
							}
						}
					break;
					case 'f':
						$idtest=mysqli_query($link,"SELECT id FROM playerm WHERE num=".$msg." AND roomm=".$userinfo['roomm']." AND flute IS NULL AND role!='f'");/*AND role!='f'*/
						if (mysqli_num_rows($idtest)){
						$id=mysqli_fetch_assoc($idtest)['id'];
						mysqli_query($link,"UPDATE playerm SET flute=1 WHERE id='".$id."'");
						$flutetest=mysqli_query($link,"SELECT id FROM playerm WHERE roomm=".$userinfo['roomm']." AND flute=1");
						$rows=mysqli_num_rows($flutetest);
						if ($rows==1){
							$result="催眠成功。".fetchname($user,3)."已经变成被催眠者。请回复下一位被催眠者的编号，你的房间里有".fetchname($user);
						} else{
							$result=fetchname($user,3)."\n已经被你催眠。你这一阶段任务完成。";
							$answer=$roomminfo['answer']+1;
							$result=$result.answercheck($answer,$user);
						}
						} else{
							$result="该玩家不存在或者他（她）已经被你催眠了。当然，你不能催眠你自己。";
						}
					break;
					case 's':
						if ($msg<=$roomminfo['totalnumber'] AND $msg>0){
							mysqli_query($link,"UPDATE playerm SET shielded=1 WHERE num=".$msg." AND roomm=".$userinfo['roomm']);
							$answer=$roomminfo['answer']+1;
							$result=fetchname($user,4)."已被你守护。你明晚不能再守护他（她）。";
							$result=$result.answercheck($answer,$user);

						} else{
							$result="该人不存在。";
						}
					break;
					case 'p':
						if ($msg<=$roomminfo['totalnumber'] AND $msg!=$userinfo['num'] AND $msg>0){
							$fetchuser=mysqli_query($link,"SELECT id,role FROM playerm WHERE num=".$msg." AND roomm=".$userinfo['roomm']);
							$fetchuser=mysqli_fetch_assoc($fetchuser);
							$fetch_name=mysqli_query($link,"SELECT name FROM name WHERE id='".$fetchuser['id']."'");
							$name=mysqli_fetch_assoc($fetch_name);
							if ($fetchuser['role']=='k' OR $fetchuser['role']=='a'){
								$result="$msg.".$name['name']."的身份是狼人。";
							} else{
					        	$result="$msg.".$name['name']."不是狼人。";
					        }
							$answer=$roomminfo['answer']+1;
							$result=$result.answercheck($answer,$user);
						} else{
							$result="你不能检测自己的身份或你输入的编号不存在。";
						}

					break;
					default:
						if ($msg==99){
							$answer=$roomminfo['answer']+1;
							$result="谢谢你的配合。你在本阶段已经没事做了。".answercheck($answer,$user);
						} else{
							$result="你不能在第三阶段干活。只有女巫，预言家，守卫和吹笛者可以做特殊的事情。你请回复‘99’结束这一阶段。";
						}
				}
			break;
			case 4: 
				$result="现在是第四阶段。不接受数字回复。";
			break;
			case 5:
				$result="现在是第五阶段。不接受数字回复。";
			break;
			default:
			return;
		}
		mysqli_close($link);
		return $result;
	}




	//$postStr=file_get_contents("php://input");
	//$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];


	if (!empty($postStr)){
		$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
		$fromUsername = $postObj->FromUserName;
		$toUsername = $postObj->ToUserName;
		$form_MsgType = $postObj->MsgType;
		$msgid = $postObj->MsgId;


		if($form_MsgType=="event"){
			$form_Event = $postObj->Event;
			if($form_Event=="subscribe"){
				$contentStr = "感谢您关注桌游魂！";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $contentStr);
				echo $resultStr;
				exit;
			}
		} elseif ($form_MsgType=="text"){
			$form_content = trim($postObj->Content);

			if (roommcheck($fromUsername)){
				if (!anscheck($fromUsername)){
					if (preg_match($pattern_num,$form_content)){
						$feedback=process($form_content, $fromUsername, $msgid);
					} elseif(preg_match($pattern_staff,$form_content)){
						$feedback=roleflag($form_content,$fromUsername);
					} else{
						$feedback=command($form_content, $fromUsername);
					}
				} else{
					$feedback=anscheck($fromUsername);
				}
			} else{
				if (preg_match($pattern_num,$form_content)){
					$feedback=create($form_content, $fromUsername);
				} elseif(preg_match($pattern_join,$form_content)){
					$feedback=joinroomm($form_content,$fromUsername);
				} else{
					$feedback="你不在任何房间中，请回复你想要的房间大小或房间号进房先。询问游戏帮助也请先进房再回复规则。";
				}
			}
			
			
			return $feedback;
			exit;
		}

	} else{
		return "";
		exit;
	}
}
