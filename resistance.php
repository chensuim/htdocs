<?php
function resistance($postStr){
	global $roledef,$roles,$tasks,$fails,$rolename,$reroles,$rolefunction,$document;
	$roledef=[
	'a' => '梅林',
	'b' => '派西维尔',
	'c' => '莫甘娜',
	'd' => '亚瑟的忠臣',
	'e' => '莫德雷德的爪牙',
	'f' => '好＋兰斯洛特',
	'g' => '坏＋兰斯洛特',
	'h' => '莫德雷德'
];

$roles=[
	5 => 'abcde',
	6 => 'abcdde',
	7 => 'abcdefg',
	8 => 'abcddefg',
	9 => 'abcdddefg',
	10 => 'abcdddefgh'
];

$tasks=[
	5 => [2,3,2,3,3],
	6 => [2,3,4,3,4],
	7 => [2,3,3,4,4],
	8 => [3,4,4,5,5],
	9 => [3,4,4,5,5],
	10 => [3,4,4,5,5]
];
$fails=[
	5 => [0,0,0,0,0],
	6 => [0,0,0,0,0],
	7 => [0,0,0,1,0],
	8 => [0,0,0,1,0],
	9 => [0,0,0,1,0],
	10 => [0,0,0,1,0]
];
$rolename=[
	'a' => '抵抗者',
	'b' => '抵抗者',
	'c' => '间谍',
	'd' => '抵抗者',
	'e' => '间谍',
	'f' => '抵抗者',
	'g' => '间谍',
	'h' => '间谍'
];
$reroles=[
	5 => '32',
	6 => '42',
	7 => '43',
	8 => '53',
	9 => '63',
	10 => '64'
];
$rolefunction=[
	'a' => 'ceg',
	'b' => 'ac',
	'c' => 'egh',
	'd' => '',
	'e' => 'cgh',
	'f' => '',
	'g' => '',
	'h' => 'ceg'
];
$pattern_vote='/^(0|1)$/';
$pattern_create='/^([5-9]|10)$/';
$pattern_room='/^\d{4}$/';

$document="1. 回复总人数(5-10)创建房间，获取房间号及自己的身份（默认开始阿瓦隆游戏，如需玩抵抗组织，请在只有房主时回复系统‘抵抗组织‘）；\n2. 邀请基/姬友们回复房间号（四位阿拉伯数字）进入房间，并获取身份；\n3. 投票：“1”代表支持，“0”代表反对；\n4. 任务阶段回复“推翻”，推翻组队；\n5. 回复“结果”，得到上一轮投票结果；\n6. 回复“退出”，房间所有人会被解除房间状态。\n7.在阿瓦隆游戏中，姓莫的都是坏人，坏兰斯洛特也是坏人，然后就只有好人啦。\n8.在阿瓦隆游戏中存在王者之剑。该剑在领袖选择任务人选时一并给出（不能给自己但能给选中的任务人选）。该剑在所有人做完任务后使用。由获得剑的人选择用或不用。不用则请回复系统‘不用’，然后进入下一轮。若选择用，选择一名参与任务的玩家，被剑选择的玩家回复系统’剑‘。被剑刺中的人的任务投票变成相反结果。\n9.在7人以上房间，系统会加入兰斯洛特身份（请自行上网了解）。
在有兰斯洛特存在的房间，系统会给出一串由1和0组成的长度为5的字符串。每一个字符代表一轮任务。1代表兰斯洛特换身份，0代表不换。例子：‘10001’表示在第一轮和第五轮开始时兰斯洛特们会掉换身份";




	function totalrole($number){
		global $roles,$roledef;
		$i=0;
		$total="\n";
		$count=substr_count($roles[$number],'d');
		while($i<$number){
			if ($count==1){
				$total=$total.$roledef[$roles[$number][$i]]."\n";
				$i+=1;
			} else{
				if ($roles[$number][$i]!='d'){
					$total=$total.$roledef[$roles[$number][$i]]."\n";
					$i+=1;
				} else{
					$total=$total.$roledef['d']."*".$count."\n";
					$i=$i+$count;
				}
			}
		}
		return $total;
	}


	function create($number, $creator){

		global $roles,$roledef,$rolename,$reroles;
		$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
		if (!$link) {
	 		die('database fail '.mysqli_error());
		}

		$db_selected = mysqli_select_db($link,DB_NAME);
		if (!$db_selected){
			die('database fail '.mysqli_error());
		}
	    $test_id_exist = mysqli_query($link,"SELECT roomr,role FROM playerr WHERE id='".$creator."'");
		if (mysqli_num_rows($test_id_exist)){
			$userinfo = mysqli_fetch_assoc($test_id_exist);
			$fetch_roomr = mysqli_query($link,"SELECT totalnumber,currentnumber,mode FROM roomr WHERE roomrid=".$userinfo['roomr']);
			$roomrinfo = mysqli_fetch_assoc($fetch_roomr);
			mysqli_close($link);
			if($roomrinfo['mode']==2){
				return "你已在".$roomrinfo['totalnumber']."人房间，房间里有".totalrole($roomrinfo['totalnumber'])."房间号".$userinfo['roomr']."。当前已有".$roomrinfo['currentnumber']."人。\n你的身份是\n".$roledef[$userinfo['role']]."。";
			} else{
				return "你已在".$roomrinfo['totalnumber']."人房间，其中抵抗者".$reroles[$number][0]."人，间谍".$reroles[$number][1]."人。当前已有".$roomrinfo['currentnumber']."人。\n你的身份是\n".$rolename[$userinfo['role']]."。";		
			}
		}

		$test_roomr_limit = mysqli_query($link,"SELECT COUNT(*) AS totalroomrs FROM roomr");
		$row = mysqli_fetch_assoc($test_roomr_limit);
		if ($row['totalroomrs'] == 9000){
			mysqli_close($link);
			return '总房间数已达上限。';
		}

		do{
			$roomrid = mt_rand(1000,9999);
			$check_roomrid_exist = mysqli_query($link,"SELECT roomrid FROM roomr WHERE roomrid=".$roomrid);
		} while (mysqli_num_rows($check_roomrid_exist));
		$roleflag = str_shuffle($roles[$number]);
		mysqli_query($link,"INSERT INTO playerr (id, roomr, role, voted,resulted,num) VALUES ('".$creator."',".$roomrid.",'".$roleflag[0]."',0,FALSE,'a')");
		mysqli_query($link,"INSERT INTO roomr (roomrid, roleflag, totalnumber, currentnumber, turn, votes, disagree, status, success, fail, deny, last,lancelot,mode) VALUES (".$roomrid.",'".$roleflag."',".$number.",1,0,0,0,0,0,0,0,'第一次投票还没结束。',' 暂无','2')");
		mysqli_close($link);
		return "你已开房（默认抵抗组织阿瓦隆，如果想切换回抵抗组织，请回复‘抵抗组织’。）".$roomrid."，房间里有".totalrole($number)."快召唤基/姬友一起来嘿嘿嘿吧！\n你的身份是\n".$roledef[$roleflag[0]]."。";
	}

	function roomr($roomrid, $user){
		global $roles,$roledef,$tasks,$rolename,$reroles,$repository;
		$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
		if (!$link) {
	 		die('database fail '.mysqli_error());
		}

		$db_selected = mysqli_select_db($link,DB_NAME);
		if (!$db_selected){
			die('database fail '.mysqli_error());
		}
	    $test_roomr = mysqli_query($link,"SELECT totalnumber, currentnumber FROM roomr WHERE roomrid=".$roomrid);
		if (!mysqli_num_rows($test_roomr)){
			mysqli_close($link);
			return '该房间不存在。你可能进错了模式。回复系统‘mode’加数字换模式。现在有'.$repository;
		}
		$roomrinfo = mysqli_fetch_assoc($test_roomr);
		$number = $roomrinfo['totalnumber'];
		$current = $roomrinfo['currentnumber'];

		if ($number==$current){
			mysqli_close($link);
			return '该房间已满。';
		}
		$test_id_exist = mysqli_query($link,"SELECT roomr,role FROM playerr WHERE id='".$user."'");
		if (mysqli_num_rows($test_id_exist)){
			$userinfo = mysqli_fetch_assoc($test_id_exist);
			$fetch_roomr = mysqli_query($link,"SELECT totalnumber,currentnumber,mode FROM roomr WHERE roomrid=".$userinfo['roomr']);
			$roomrinfo = mysqli_fetch_assoc($fetch_roomr);
			$number = $roomrinfo['totalnumber'];
			mysqli_close($link);
			if($roomrinfo['mode']==2){
				return "你已在".$roomrinfo['totalnumber']."人房间，房间里有".totalrole($roomrinfo['totalnumber'])."房间号".$userinfo['roomr']."。当前已有".$roomrinfo['currentnumber']."人。\n你的身份是\n".$roledef[$userinfo['role']]."。";
			} else{
				return "你已在".$roomrinfo['totalnumber']."人房间，其中抵抗者".$reroles[$number][0]."人，间谍".$reroles[$number][1]."人。当前已有".$roomrinfo['currentnumber']."人。\n你的身份是\n".$rolename[$userinfo['role']]."。";		
			}
		}
		$current += 1;
	    $num=chr(ord('a') + $current - 1);
		$fetch_roomr=mysqli_query($link,"SELECT roleflag,mode,totalnumber FROM roomr WHERE roomrid=".$roomrid);
		$roomrinfo=mysqli_fetch_assoc($fetch_roomr);
		if ($roomrinfo['mode']==2){
			$roleflag=$roomrinfo['roleflag'];
			mysqli_query($link,"INSERT INTO playerr (id, roomr, role, voted,resulted,num) VALUES ('".$user."',".$roomrid.",'".$roleflag[$current-1]."',FALSE,FALSE,'".$num."')");
			mysqli_query($link,"UPDATE roomr SET currentnumber=".$current." WHERE roomrid=".$roomrid);
			$result = "你已加入".$number."人房间。房间里有".totalrole($roomrinfo['totalnumber'])."房间号".$roomrid."。当前已有".$current."人。".fetchname($user,TRUE)."\n你的身份是\n".$roledef[$roleflag[$current-1]]."。";
			if ($current==$number){
				mysqli_query($link,"UPDATE roomr SET status=1 WHERE roomrid=".$roomrid);
				if (strpos($roleflag,'f')!==FALSE){
					$lancelot=substr(str_shuffle('1100000'),0,5);
					mysqli_query($link,"UPDATE roomr SET lancelot='".$lancelot."'WHERE roomrid=".$roomrid);
					$result=$result."\n房间已满。请你暂时成为法官，然后按以下步骤操控大家：
1. 所有人回复‘身份’获取该知道的身份信息。
2. 兰斯洛特在这次游戏中阵营转换规律是".$lancelot."。（其中‘1’代表换阵营，‘0’代表不换）
3. 选出领袖。
4. 请领袖选出".$tasks[$number][0]."人组队。";
					if ($lancelot[0]=='1'){
						$result=$result."\n第一轮兰斯洛特们就得转换阵营。";
					}
				} else{
					$result=$result."\n房间已满。\n回复‘身份’能直接获取你能知道的人的昵称。请领袖选出".$tasks[$number][0]."人组队。";
				}
			}
		} else{
			$role=$roomrinfo['roleflag'][$current-1];
			mysqli_query($link,"INSERT INTO playerr (id, roomr, role, voted,resulted,num) VALUES ('".$user."',".$roomrid.",'".$role."',FALSE,FALSE,".$num.")");
			mysqli_query($link,"UPDATE roomr SET currentnumber=".$current." WHERE roomrid=".$roomrid);
			$result = "你已加入".$number."人房间（抵抗者".$reroles[$number][0]."人，间谍".$reroles[$number][1]."人），房间号".$roomrid."。当前已有".$current."人。\n你的身份是\n".$rolename[$role]."。";
			if ($current==$number){
			mysqli_query($link,"UPDATE roomr SET status=1 WHERE roomrid=".$roomrid);
			$result=$result."\n房间已满，回复‘身份’能直接获取你能知道的人的昵称。然后请领袖选出".$tasks[$number][0]."人组队。";
			}
		}
		mysqli_close($link);
		return $result;
	}

	function command($command, $user){
		global $document,$tasks,$fails,$reroles,$rolename,$roles;
		$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
		if (!$link) {
	 		die('database fail '.mysqli_error());
		}

		$db_selected = mysqli_select_db($link,DB_NAME);
		if (!$db_selected){
			die('database fail '.mysqli_error());
		}

		switch($command){
			case 'exit':
			case 'quit':
			case '退出':
				$test_id_exist=mysqli_query($link,"SELECT roomr FROM playerr WHERE id='".$user."'");
				if (mysqli_num_rows($test_id_exist)){
					$row = mysqli_fetch_assoc($test_id_exist);
					$roomr = $row['roomr'];
					mysqli_query($link,"DELETE FROM playerr WHERE roomr=".$roomr);
					mysqli_query($link,"DELETE FROM roomr WHERE roomrid=".$roomr);
					$result="房间".$roomr."已被注销。请告诉和你在同一房间的人。";
				} else{
					$result="你不在任何房间中。";
				}
				break;
			case 'no confidence':
			case '不受信任':
			case '推翻':
			case '反对':
			case '造反':
			case '起义':
			case '颠覆':
				$test_id_exist=mysqli_query($link,"SELECT roomr FROM playerr WHERE id='".$user."'");
				if (mysqli_num_rows($test_id_exist)){
					$userinfo = mysqli_fetch_assoc($test_id_exist);
					$fetch_roomr = mysqli_query($link,"SELECT totalnumber,turn,status,deny FROM roomr WHERE roomrid=".$userinfo['roomr']);
					$roomrinfo = mysqli_fetch_assoc($fetch_roomr);
					if ($roomrinfo['status']!=2){
						$result="只有组队成功后才可使用“不受信任”。";
					} else{
						$deny=$roomrinfo['deny']+1;
						$turn=$roomrinfo['turn'];
						$result="你已推翻当前领袖。\n这是第".$deny."次组队失败。";
						if ($deny==5){
							$result=$result."\n游戏结束，间谍胜利。";
							mysqli_query($link,"DELETE FROM playerr WHERE roomr=".$userinfo['roomr']);
							mysqli_query($link,"DELETE FROM roomr WHERE roomrid=".$userinfo['roomr']);
						} else{
							$result=$result."\n请下一位领袖选出".$tasks[$roomrinfo['totalnumber']][$turn]."人做任务。允许".$fails[$roomrinfo['totalnumber']][$turn]."人破坏。";
							mysqli_query($link,"UPDATE playerr SET voted=0 WHERE roomr=".$userinfo['roomr']);
							mysqli_query($link,"UPDATE roomr SET votes=0,disagree=0,status=1,deny=".$deny." WHERE roomrid=".$userinfo['roomr']);
						}
					}
				} else{
					$result="你不在任何房间中。";
				}
				break;
			case '帮助':
				$result=$document;
				break;
			case '兰斯洛特':
	        case 'lancelot':
	        case '兰':
				$test_id_exist=mysqli_query($link,"SELECT roomr FROM playerr WHERE id='".$user."'");
				if (mysqli_num_rows($test_id_exist)){
					$userinfo = mysqli_fetch_assoc($test_id_exist);
					$fetch_roomr = mysqli_query($link,"SELECT lancelot,turn,mode FROM roomr WHERE roomrid=".$userinfo['roomr']);
					$roomrinfo = mysqli_fetch_assoc($fetch_roomr);
					if($roomrinfo['mode']==2){
						$lancelot=$roomrinfo['lancelot'];
						$turn=$roomrinfo['turn'];
						$result="本局游戏中，兰斯洛特阵营转换规律是".$lancelot."（其中‘1’代表换阵营，‘0’代表不换，‘暂无’代表人还没满或者根本就没有兰斯洛特在你们房间。）。";
						$lanceloted=substr($lancelot,0,($turn+1));
						switch (substr_count($lanceloted,'1')){
							case 1:
								$result=$result."并且在目前状态，兰斯洛特们发生过一次阵营转换，处于相反身份状态。";
							break;
							default:
								$result=$result."并且在目前状态，兰斯洛特们处于初始身份状态。";
						}
					} else{
						$result="抵抗组织模式里没有兰斯洛特。";
					}
				} else{
					$result="你不在任何房间中。";
				}
				break;
			case '王者之剑':
	        case '剑':
	        case 'sword':
	        case 'Sword':
				$test_id_exist=mysqli_query($link,"SELECT roomr,voted FROM playerr WHERE id='".$user."'");
				if (mysqli_num_rows($test_id_exist)){
					$userinfo = mysqli_fetch_assoc($test_id_exist);
					$fetch_roomr = mysqli_query($link,"SELECT status,disagree,mode FROM roomr WHERE roomrid=".$userinfo['roomr']);
					$roomrinfo = mysqli_fetch_assoc($fetch_roomr);
					if ($roomrinfo['mode']==2){
						if($roomrinfo['status']==3){
							if ($userinfo['voted']==2){
								$disagree=$roomrinfo['disagree']-1;
								$result="王者之剑使用成功。最终结果是\n".missioncheck($disagree,$userinfo['roomr']);
								if (mysqli_num_rows($test_id_exist)){
									$result=$result."请手动告知对你用王者之剑的人，你在用剑之前的投票结果。";
								}
							} elseif($userinfo['voted']==1){
								$disagree=$roomrinfo['disagree']+1;
								$test_id_exist=mysqli_query($link,"SELECT roomr,voted FROM playerr WHERE id='".$user."'");
								$result="王者之剑使用成功。最终结果是\n".missioncheck($disagree,$userinfo['roomr']);
								if (mysqli_num_rows($test_id_exist)){
									$result=$result."请手动告知对你用王者之剑的人，你在用剑之前的投票结果。";
								}
							} else{
								$result="干嘛乱摸王者之剑。小心捅死你啊（你没参加这次任务，没有办法使用王者之剑）。";
							}
						} else{
							$result="暂时不能使用王者之剑。";
						}
					} else{
						$result="抵抗组织模式里没有王者之剑。";
					}
				} else{
					$result="你不在任何房间中。";
				}
				break;
			case '不用':
			case 'no':
			case 'No':
				$test_id_exist=mysqli_query($link,"SELECT roomr,voted FROM playerr WHERE id='".$user."'");
				if (mysqli_num_rows($test_id_exist)){
					$userinfo = mysqli_fetch_assoc($test_id_exist);
					$fetch_roomr = mysqli_query($link,"SELECT status,disagree,mode FROM roomr WHERE roomrid=".$userinfo['roomr']);
					$roomrinfo = mysqli_fetch_assoc($fetch_roomr);
	                $disagree=$roomrinfo['disagree'];
					if($roomrinfo['mode']==2){
						if($roomrinfo['status']==3){
							$result="你们选择不使用王者之剑。上一轮的投票结果是\n".missioncheck($disagree,$userinfo['roomr']);
						} else{
							$result="自己去磨自己的王者之剑去，别来这捣乱（现在不是使用王者之剑的时候）。";
						}
					} else{
						$result="抵抗组织模式里没有王者之剑。";
				}
				} else{
					$result="你不在任何房间中。";
				}
				break;
			case '抵抗组织':
			case 'resistance':
			case 'Resistance':
				$test_id_exist=mysqli_query($link,"SELECT roomr FROM playerr WHERE id='".$user."'");
				if (mysqli_num_rows($test_id_exist)){
					$userinfo = mysqli_fetch_assoc($test_id_exist);
					$fetch_roomr = mysqli_query($link,"SELECT currentnumber,totalnumber FROM roomr WHERE roomrid=".$userinfo['roomr']);
					$roomrinfo = mysqli_fetch_assoc($fetch_roomr);
					$number=$roomrinfo['totalnumber'];
					if ($roomrinfo['currentnumber']==1){
	                    $roomrid=$userinfo['roomr'];
						mysqli_query($link,"DELETE FROM playerr WHERE roomr=".$userinfo['roomr']);
						mysqli_query($link,"DELETE FROM roomr WHERE roomrid=".$userinfo['roomr']);
						$roleflag = str_shuffle($roles[$number]);
						$role=$roleflag[0];
						mysqli_query($link,"INSERT INTO playerr (id, roomr, role, voted,resulted,num) VALUES ('".$user."',".$roomrid.",'".$role."',0,FALSE,'a')");
						mysqli_query($link,"INSERT INTO roomr (roomrid, roleflag, totalnumber, currentnumber, turn, votes, disagree, status, success, fail, deny, last,mode) VALUES (".$roomrid.",'".$roleflag."',".$number.",1,0,0,0,0,0,0,0,'第一次投票还没结束。','1')");
						mysqli_close($link);
						return "你已开房抵抗组织".$roomrid."，其中抵抗者".$reroles[$number][0]."人，间谍".$reroles[$number][1]."人。快召唤基/姬友一起来嘿嘿嘿吧！\n你的身份是\n".$rolename[$role]."。";
					} else{
						$result="抱歉，忘了告诉你，当有其他人加入了房间后，就不允许切换成抵抗组织模式了。要不退了重进？";
					}
				} else{
					$result="你不在任何房间中。";
				}
				break;
			case '房间':
			case 'room':
			case 'Room':
				$test_id_exist=mysqli_query($link,"SELECT roomr FROM playerr WHERE id='".$user."'");
				if (mysqli_num_rows($test_id_exist)){
					$result=fetchname($user,TRUE);
				} else{
					$result="你不在任何房间中。";
				}
			break;
			case '身份':
			case 'secret':
			case 'Secret':
				$test_id_exist=mysqli_query($link,"SELECT roomr FROM playerr WHERE id='".$user."'");
				if (mysqli_num_rows($test_id_exist)){
					$result=fetchname($user);
				} else{
					$result="你不在任何房间中。";
				}
			break;
			default:
				$test_id_exist=mysqli_query($link,"SELECT roomr,resulted FROM playerr WHERE id='".$user."'");
				if (mysqli_num_rows($test_id_exist)){
					$userinfo = mysqli_fetch_assoc($test_id_exist);
					$fetch_roomr = mysqli_query($link,"SELECT last,status,turn,totalnumber,currentnumber,votes FROM roomr WHERE roomrid=".$userinfo['roomr']);
					$roomrinfo = mysqli_fetch_assoc($fetch_roomr);
					if (!$userinfo['resulted']){
						switch($roomrinfo['status']){
							case 0:
								return "人还没满就在这乱发东西的。想干嘛呢！现在已经有".$roomrinfo['currentnumber']."人在房间里了。";
								break;
							case 1:
								if ($roomrinfo['votes']==0){
									$status="现在是领袖选人阶段。这个时候应该选出".$tasks[$roomrinfo['totalnumber']][$roomrinfo['turn']]."人做任务。允许".$fails[$roomrinfo['totalnumber']][$roomrinfo['turn']]."人破坏。";
								} else{
									$status="现在是组队投票阶段。这个时候应该选出".$tasks[$roomrinfo['totalnumber']][$roomrinfo['turn']]."人做任务。允许".$fails[$roomrinfo['totalnumber']][$roomrinfo['turn']]."人破坏。已经有".$roomrinfo['votes']."张票了。";
								}
							break;
							default:
								$status="现在是任务投票阶段。应该有".$tasks[$roomrinfo['totalnumber']][$roomrinfo['turn']]."人做任务。允许".$fails[$roomrinfo['totalnumber']][$roomrinfo['turn']]."人破坏。已经有".$roomrinfo['votes']."张票了。";
						}
						$result=$roomrinfo['last'].$status;
						mysqli_query($link,"UPDATE playerr SET resulted=TRUE WHERE id='".$user."'");
					} else{
						switch($roomrinfo['status']){
							case 1:
								if ($roomrinfo['votes']==0){
									$status="领袖选人阶段。这个时候应该选出".$tasks[$roomrinfo['totalnumber']][$roomrinfo['turn']]."人做任务。允许".$fails[$roomrinfo['totalnumber']][$roomrinfo['turn']]."人破坏。";
								} else{
									$status="组队投票阶段。这个时候应该选出".$tasks[$roomrinfo['totalnumber']][$roomrinfo['turn']]."人做任务。允许".$fails[$roomrinfo['totalnumber']][$roomrinfo['turn']]."人破坏。已经有".$roomrinfo['votes']."张票了。";
								}
							break;
							default:
								$status="任务投票。应该有".$tasks[$roomrinfo['totalnumber']][$roomrinfo['turn']]."人做任务。允许".$fails[$roomrinfo['totalnumber']][$roomrinfo['turn']]."人破坏。已经有".$roomrinfo['votes']."张票了。";
						}
						$result="上一轮的结果已经告诉过你了。新的一轮投票结果还没有出来。现在大家正在进行地是第".($roomrinfo['turn']+1)."轮".$status;
					}
				} else{
					$result="说实话，我并不知道你想干什么。但是，你还没有进入房间。";
				}
		}
		mysqli_close($link);
		return $result;
	}
	function missioncheck($disagree,$roomrid){
		global $tasks, $fails;
		$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
		if (!$link) {
	 		die('database fail '.mysqli_error());
		}

		$db_selected = mysqli_select_db($link,DB_NAME);
		if (!$db_selected){
			die('database fail '.mysqli_error());
		}
		$fetch_roomr=mysqli_query($link,"SELECT totalnumber,fail,turn,success,mode,lancelot FROM roomr WHERE roomrid=".$roomrid);
		$roomrinfo=mysqli_fetch_assoc($fetch_roomr);
		$max=$roomrinfo['totalnumber'];
		if ($disagree>$fails[$max][($roomrinfo['turn'])]){
						$result=$disagree."人破坏任务，第".($roomrinfo['turn']+1)."回合任务失败。";
						$last=$disagree."人破坏任务，第".($roomrinfo['turn']+1)."回合任务失败。";
						$fail=$roomrinfo['fail']+1;
						if ($fail==3){
							$result=$result."\n"."游戏结束，间谍/红方胜利。";
							mysqli_query($link,"DELETE FROM playerr WHERE roomr=".$roomrid);
							mysqli_query($link,"DELETE FROM roomr WHERE roomrid=".$roomrid);
							mysqli_close($link);
							return $result;
						}
						mysqli_query($link,"UPDATE roomr SET status=1,fail=".$fail.",last='".$last."' WHERE roomrid=".$roomrid);
					} else{
						$result=$disagree."人破坏任务，第".($roomrinfo['turn']+1)."回合任务成功。";
						$last=$disagree."人破坏任务，第".($roomrinfo['turn']+1)."回合任务成功。";
						$success=$roomrinfo['success']+1;
	                    $mode=$roomrinfo['mode'];
						if ($success==3){
	                            $result=$result."\n"."游戏结束，抵抗者／蓝方胜利。";
	                            mysqli_query($link,"DELETE FROM playerr WHERE roomr=".$roomrid);
	                            mysqli_query($link,"DELETE FROM roomr WHERE roomrid=".$roomrid);
	                            mysqli_close($link);
	                            return $result;
						}
						mysqli_query($link,"UPDATE roomr SET status=1,success=".$success.",last='".$last."' WHERE roomrid=".$roomrid);
					}
		mysqli_query($link,"UPDATE playerr SET resulted=FALSE,voted=0 WHERE roomr=".$roomrid);
		$turn=$roomrinfo['turn']+1;
		mysqli_query($link,"UPDATE roomr SET votes=0,disagree=0,deny=0,turn=".$turn." WHERE roomrid=".$roomrid);
		$result=$result."进入第".($turn+1)."轮，请领袖选出".$tasks[$max][$turn]."人做任务。允许".$fails[$max][$turn]."人破坏。";
		if (isset($roomrinfo['lancelot'][$turn])){
			if ($roomrinfo['lancelot'][$turn]=='1'){
				$result=$result."这一局任务兰斯洛特的阵营发生了转变，请告知所有人。";
			}
		}
		return $result;
	}
	function fetchname($user,$state=FALSE){
		global $rolefunction,$roledef;
		$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
		if (!$link) {
	 		die('database fail '.mysqli_error());
		}

		$db_selected = mysqli_select_db($link,DB_NAME);
		if (!$db_selected){
			die('database fail '.mysqli_error());
		}
		$test_id_exist=mysqli_query($link,"SELECT roomr,role FROM playerr WHERE id='".$user."'");
		$userinfo = mysqli_fetch_assoc($test_id_exist);
		if ($state){
	        $y=0;
			$fetch_roomr=mysqli_query($link,"SELECT currentnumber FROM roomr WHERE roomrid=".$userinfo['roomr']);
	        $totalnumber=mysqli_fetch_assoc($fetch_roomr)['currentnumber'];
			$result="你的房间里有";
			while ($y<$totalnumber){
	            $fetch_playerr=mysqli_query($link,"SELECT id,num FROM playerr WHERE roomr=".$userinfo['roomr']." && num='".chr($y+ord('a'))."'");
	            $playinfo=mysqli_fetch_assoc($fetch_playerr);
	            $y=$y+1;
				$fetch_name=mysqli_query($link,"SELECT name FROM name WHERE id='".$playinfo['id']."'");
				$name=mysqli_fetch_assoc($fetch_name);
	            $num=$playinfo['num'];
	            $result=$result."\n  $num.".$name['name'];
			}
		} else{
			$fetch_roomr=mysqli_query($link,"SELECT totalnumber,currentnumber,mode FROM roomr WHERE roomrid=".$userinfo['roomr']);
			$roomrinfo=mysqli_fetch_assoc($fetch_roomr);
				if($roomrinfo['currentnumber']==$roomrinfo['totalnumber']){
					if($roomrinfo['mode']==2){
						if (strpos('cdj',$userinfo['role'])!==FALSE){
							mysqli_close($link);
							return "抱歉，你的角色不能知道其他人的身份。";
						} elseif($userinfo['role']=='i'){
							mysqli_close($link);
							return "奥伯伦就是要做一个安静的美坏人。（你不知道其他坏人的存在，其他坏人也不知道你）";
						} else{
							$fetch_roomr=mysqli_query($link,"SELECT roleflag FROM roomr WHERE roomrid=".$userinfo['roomr']);
							$roomrinfo=mysqli_fetch_assoc($fetch_roomr);
							$fetch_role=array_intersect(str_split($rolefunction[$userinfo['role']]),str_split($roomrinfo['roleflag']));
			                $role="";
			                $i=0;
			                while ($i<4){
			                    if(isset($fetch_role[$i])){
			                        $role=$role."\n  ".$roledef[$fetch_role[$i]];
			                    } else{
			                    	break;
			                    }
			                    $i+=1;
			                }
							$result="你是".$roledef[$userinfo['role']]."\n你可以知道的身份是:".$role."\n他（们）是（不是一一对应):";
							$fetch_playerr=mysqli_query($link,"SELECT id,role FROM playerr WHERE roomr=".$userinfo['roomr']);
							while ($playerrinfo=mysqli_fetch_assoc($fetch_playerr)){
								if(strpos($rolefunction[$userinfo['role']],$playerrinfo['role']) !==FALSE){
									$fetch_name=mysqli_query($link,"SELECT name FROM name WHERE id='".$playerrinfo['id']."'");
									$name=mysqli_fetch_assoc($fetch_name);
	                                $fetch_num=mysqli_query($link,"SELECT num FROM playerr WHERE id='".$playerrinfo['id']."'");
	                                $num=mysqli_fetch_assoc($fetch_num);
	                                $num=$num['num'];
							        $result=$result."\n  "."$num.".$name['name'];
							    }
							}
						}
					} else{
						if (strpos('abdf',$userinfo['role'])===FALSE){
							$result="你是间谍。\n你的同伙们是:";
							$fetch_playerr=mysqli_query($link,"SELECT id,role FROM playerr WHERE roomr=".$userinfo['roomr']);
							while ($playerrinfo=mysqli_fetch_assoc($fetch_playerr)){
								if(strpos('abcd',$playerrinfo['role']) ===FALSE){
									$fetch_name=mysqli_query($link,"SELECT name FROM name WHERE id='".$playerrinfo['id']."'");
									$name=mysqli_fetch_assoc($fetch_name);
	                                $fetch_num=mysqli_query($link,"SELECT num FROM playerr WHERE id='".$playerrinfo['id']."'");
	                                $num=mysqli_fetch_assoc($fetch_num);
	                                $num=$num['num'];
							        $result=$result."\n  "."$num.".$name['name'];
							    }
							}
						} else{
							$result="你是好人。";
						}
					}
				} else{
					mysqli_close($link);
					return "人还没满，不能获知身份。";
				}
		}
		return $result;
	}
	function vote($option, $user, $msgid){
		global $tasks, $fails;
		$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
		if (!$link) {
	 		die('database fail '.mysqli_error());
		}

		$db_selected = mysqli_select_db($link,DB_NAME);
		if (!$db_selected){
			die('database fail '.mysqli_error());
		}

		$test_id_exist = mysqli_query($link,"SELECT roomr,role,voted,msgid FROM playerr WHERE id='".$user."'");
		if (mysqli_num_rows($test_id_exist)){
			$userinfo = mysqli_fetch_assoc($test_id_exist);
			if ($userinfo['msgid']==$msgid){
				return;
			}
			$fetch_roomr = mysqli_query($link,"SELECT totalnumber,turn,votes,disagree,status,success,fail,deny,lancelot,mode FROM roomr WHERE roomrid=".$userinfo['roomr']);
			$roomrinfo = mysqli_fetch_assoc($fetch_roomr);
			$votes=$roomrinfo['votes'];
			if ($roomrinfo['status']==1){
				$max=$roomrinfo['totalnumber'];
			}
			if ($roomrinfo['status']==2){
				$max=$tasks[$roomrinfo['totalnumber']][$roomrinfo['turn']];
			}
			$disagree=$roomrinfo['disagree'];
			if ($userinfo['voted']){
					mysqli_close($link);
					return "你已投票。现在有".$votes."张票。还差".($max-$votes)."张票。";
			}
			if ($roomrinfo['status']==0){
				mysqli_close($link);
				return "房间未满，无法进行游戏。快召唤你的基/姬友吧！";
			}
			if ($roomrinfo['status']==3){
				mysqli_close($link);
				return "等着你的基友使用王者之剑吧！（如果不想使用，请任意人回复‘不用’）";
			}
			if ($roomrinfo['status']==1){
				if ($option=='0'){
					$disagree+=1;
					$voted=2;
				} else{
					$voted=1;
				}
				$votes+=1;
				$result="投票成功。这是第".$votes."张票。还差".($max-$votes)."张票。";
				mysqli_query($link,"UPDATE playerr SET voted=".$voted.", msgid=".$msgid." WHERE id='".$user."'");
				mysqli_query($link,"UPDATE roomr SET disagree=".$disagree.",votes=".$votes." WHERE roomrid=".$userinfo['roomr']);
				if ($votes==$max){
					mysqli_query($link,"UPDATE playerr SET voted=0 WHERE roomr=".$userinfo['roomr']);
					mysqli_query($link,"UPDATE roomr SET votes=0,disagree=0 WHERE roomrid=".$userinfo['roomr']);
					if ($disagree>=(ceil($votes)/2)){
						$deny=$roomrinfo['deny']+1;
						$result=$result."\n".($votes-$disagree)."人支持，".$disagree."人反对，组队失败。\n这是第".$deny."次组队失败。";
						$last=($votes-$disagree)."人支持，".$disagree."人反对，组队失败。\n这是第".$deny."次组队失败。";
						if ($deny==5){
							$result=$result."\n游戏结束，间谍胜利。";
							mysqli_query($link,"DELETE FROM playerr WHERE roomr=".$userinfo['roomr']);
							mysqli_query($link,"DELETE FROM roomr WHERE roomrid=".$userinfo['roomr']);
							mysqli_close($link);
							return $result;
						}
						mysqli_query($link,"UPDATE roomr SET deny=".$deny.",last='".$last."' WHERE roomrid=".$userinfo['roomr']);
						mysqli_query($link,"UPDATE playerr SET resulted=FALSE WHERE roomr=".$userinfo['roomr']);
					} else{
						$result=$result."\n".($votes-$disagree)."人支持，".$disagree."人反对，组队成功。请开始做任务。";
						$last=($votes-$disagree)."人支持，".$disagree."人反对，组队成功。请开始做任务。";
						mysqli_query($link,"UPDATE roomr SET status=2,last='".$last."' WHERE roomrid=".$userinfo['roomr']);
						mysqli_query($link,"UPDATE playerr SET resulted=FALSE WHERE roomr=".$userinfo['roomr']);
						mysqli_close($link);
						return $result;
					}
				}
				return $result;
			}
			if ($roomrinfo['status']==2){
				$role=$userinfo['role'];
			if($roomrinfo['mode']==2){
					$lancelot=$roomrinfo['lancelot'];
					$lanceloted=substr($lancelot,0,($roomrinfo['turn']+1));
					switch (substr_count($lanceloted,'1')){
							case 1:
								$badlancelot='c';
								$goodlancelot='j';
							break;
							default:
							$badlancelot='j';
							$goodlancelot='c';
					}
					$good='abd'.$goodlancelot;
					if (strpos($good,$role)===FALSE){
						if($role==$badlancelot)
						{
							$disagree+=1;
							$voted=2;
						} else{
							if($option==0){
								$disagree+=1;
								$voted=2;
							} else{
								$voted=1;
							}
						}
					} else{
						$voted=1;
					}
					$votes+=1;
					$result="投票成功（蓝方一律视为做任务，坏兰斯洛特一律视为破坏任务）。这是第".$votes."张票。还差".($max-$votes)."张票。";
					mysqli_query($link,"UPDATE playerr SET voted=".$voted.", msgid=".$msgid." WHERE id='".$user."'");
					mysqli_query($link,"UPDATE roomr SET disagree=".$disagree.",votes=".$votes." WHERE roomrid=".$userinfo['roomr']);
					if ($votes==$max){
						$result=$result."任务投票完成，请拥有王者之剑的玩家选择一名参加了任务的人使用王者之剑。若使用，请被选中的玩家回复‘王者之剑’；若选择不使用，请任意人回复‘不用’获取本次任务结果。";	
						mysqli_query($link,"UPDATE roomr SET status=3 WHERE roomrid=".$userinfo['roomr']);
					}
				} else{
					if ($option=='0'){
	                    if (strpos('ghijk',$role)!==FALSE){
	                        $disagree+=1;
	                    }
					}
					$votes+=1;
					$result="投票成功（抵抗者一律视为做任务）。";
					mysqli_query($link,"UPDATE playerr SET voted=1,msgid=".$msgid." WHERE id='".$user."'");
					mysqli_query($link,"UPDATE roomr SET disagree=".$disagree.",votes=".$votes." WHERE roomrid=".$userinfo['roomr']);
					if ($votes==$max){
					$result=$result.missioncheck($disagree,$userinfo['roomr']);
					}
				}
				return $result;
			}
		} else{
			mysqli_close($link);
			return "你不在任何房间中。1";
		}
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
			$contentStr = "感谢您关注抵抗组织助手！\n游戏介绍请点击http://45.118.133.173/resistance.jpg\n输入“帮助”获取游戏指南。";
			$resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $contentStr);
			echo $resultStr;
			exit;
		}
	} elseif ($form_MsgType=="text"){
		$form_content = trim($postObj->Content);
		if (preg_match($pattern_vote, $form_content)){
		$feedback=vote($form_content, $fromUsername, $msgid);
		} elseif (preg_match($pattern_create, $form_content)){
		$feedback=create($form_content, $fromUsername);
		} elseif (preg_match($pattern_room, $form_content)){
		$feedback=roomr($form_content, $fromUsername);
		} else{
		$feedback=command($form_content, $fromUsername);
		}
		
		return $feedback;
	}

	} else{
		return "";
	}
}

