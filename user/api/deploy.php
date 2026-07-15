<?php
if($egn=='yjbs') {
	//一键部署
	$id=daddslashes($_POST['id'] ?? 0);
	$zxwc_ms_h='3';
	//每次远程操作执行完成后等待的秒数
	$res=$DB->get_row_prepare("SELECT * FROM MN_bs WHERE id=? limit 1", [$id]);
	if(!in_array((string)($res['qk'] ?? ''), ['true','1','TRUE','True'], true))exit('{"code":"禁止部署已经下架的程序"}');
	$pz_jx_json=json_decode($res['sxpz'],true) ?: [0, 0];
	$pz_web_a=$pz_jx_json[0] ?? 0;
	$pz_sql_a=$pz_jx_json[1] ?? 0;
	$hxa = json_decode($yhc['hxa'],true) ?: [];
	$hxb = json_decode($yhc['hxb'],true) ?: [];
	if($pz_web_a>($hxa['max']??0) || $pz_sql_a>($hxb['max']??0))exit('{"code":"您的配置未达到要求！"}');
	if(!in_array($yhc['user'],json_decode($res['tj'],true) ?: []) && $res['jg']!=0)exit('{"code":"您未购买该程序"}');
	include("../fn.php");
	//引入代码执行库
	include("../class.php");
	//引入数据处理库
	$api = new bt_api($btipe,$btkeye);
	//实例化类
	$cxform = json_decode($res['inp'], true) ?: [];
	$bds = [];
	foreach ($cxform as $va => $val) {
		$cz = $val['cz'] ?? '';
		if ($cz !== '' && isset($_POST[$cz])) {
			$bds[$va][$cz] = $_POST[$cz];
		}
	}
	$userform=formcl($res['inp'], $bds);
	//获取变量与用户提交数据的的对应数组
	if($userform['code']=='0')exit(json_encode(['qk'=>4,"code"=>$userform['msg']]));
	$file=$res['cxwz'];
	$type='application/zip';
	$name=basename($file);
	//获取文件名称
	$wj=$api->GetLogshqwjlo($os_xt.$yhc['sqldz']);
	//网站目录下所有文件
	$nameh=array();
	$sl=0;
	foreach (($wj['DIR'] ?? []) as $val) {
		$nameb=explode(";",$val);
		array_push($nameh,$nameb[0]);
	}
	foreach (($wj['FILES'] ?? []) as $val) {
		$nameb=explode(";",$val);
		if($nameb[0]!='.user.ini') {
			array_push($nameh,$nameb[0]);
		}
		//不删除防跨站配置文件
	}
	$json=json_encode($nameh);
	$c1=$api->xzdelwj('/',$json,[$yhc['btid'],$os_xt.$yhc['sqldz']]);
	//删除该站点所有文件
	sleep($zxwc_ms_h);
	$c2=$api->zswjsc(array('tmp_name'=>$file,'type'=>$type,'name'=>$name),$os_xt.$yhc['sqldz']);
	//上传源码
	sleep($zxwc_ms_h);
	$c3=$api->GetLogsjywj($os_xt.$yhc['sqldz'].'/'.$name,$os_xt.$yhc['sqldz'],'UTF-8','');
	//解压文件
	sleep($zxwc_ms_h);
	$c4=$api->delwj($os_xt.$yhc['sqldz'].'/'.$name);
	//删除源码
	$c5=$api->setyxml([$yhc['btid'],'/',$os_xt.$yhc['sqldz']]);
	//将运行目录设置为根目录
	$c6 = $api->setwjt(['','/www/server/panel/vhost/rewrite/'.$yhc['sqldz'].'.conf']);
	//将伪静态设置为空
	$bs=1;
	if($res['pz']!='null' && !empty($res['pz'])) {
		foreach (json_decode($res['pz'],true) ?: [] as $val) {
			$funcz=$val['cz'];
			//获取该执行哪个函数
			$ab=$funcz($val,$yhc,$os_xt,$userform);
			//对数据进行处理
			if($funcz!='gettj') {
				$abc=$api->$funcz($ab);
				//执行操作
				if($abc['status']==false) {
					exit(json_encode(['code'=>"部署失败：第{$bs}步出错，错误提示：{$abc['msg']}"]));
					break;
				}
			}
			$bs++;
			sleep('0.5');
			//此处只用等待0.5秒即可
		}
	}
	$bs_tj=json_decode($res['tj'],true) ?: [];
	if(!in_array($yhc['user'],$bs_tj)) {
		$bs_tj[]=$yhc['user'];
	}
	$tj=json_encode($bs_tj,256);
	$DB->query_prepare("update `MN_bs` set `tj` =? where `id`=?", [$tj, $id]);
	if($res['alet']!=null) {
		exit(json_encode(['qk'=>1,"code"=>tihs($res['alet'],$userform,$yhc)]));
	} else {
		exit(json_encode(['qk'=>1,"code"=>'程序部署成功！']));
	}
	return;
}
if($egn=='yjbsform') {
	//一键部署-表单获取
	$id=$_POST['id'] ?? 0;
	$res=$DB->get_row_prepare("SELECT * FROM MN_bs WHERE id=? limit 1", [$id]);
	if(!in_array((string)($res['qk'] ?? ''), ['true','1','TRUE','True'], true))exit(json_encode(["qk"=>4,'code'=>"无法部署已经下架从程序！"],256));
	$pz_jx_json=json_decode($res['sxpz'],true) ?: [0, 0];
	$pz_web_a=$pz_jx_json[0] ?? 0;
	$pz_sql_a=$pz_jx_json[1] ?? 0;
	$hxa = json_decode($yhc['hxa'],true) ?: [];
	$hxb = json_decode($yhc['hxb'],true) ?: [];
	if($pz_web_a>($hxa['max']??0) || $pz_sql_a>($hxb['max']??0))exit(json_encode(["qk"=>4,'code'=>"您的配置未达到要求"],256));
	if(!in_array($yhc['user'],json_decode($res['tj'],true) ?: []) && $res['jg']!=0)exit(json_encode(["qk"=>4,'code'=>"您未购买该程序！"],256));
	exit(json_encode(["qk"=>1,'code'=>"获取成功！",'form'=>$res['inp']],256));
	return;
}
