<?php
if($egn=='sqssl') {
	//申请/续签/SSL证书
	$urllist=$_POST['list'] ?? [];
	$type=$_POST['type'] ?? 'false';
	if(empty($urllist))exit(json_encode(["qk"=>4,'code'=>'未选择域名！'],256));
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	if($type=='false') {
		//申请
		$datas=$api->getsslpem($yhc['sqldz']) ?: [];
		//获取SSL是否开启
		if($datas['status'] ?? false)exit(json_encode(["qk"=>4,'code'=>'错误！SSL已开启！如需继续申请请先关闭SSL(申请成功后将覆盖现有密钥和证书)！'],256));
		$data=$api->sslsq(json_encode($urllist),$yhc['btid'],$yhc['sqldz'],false) ?: [];
	} else {
		//续签
		$data=$api->sslsq(json_encode($urllist),$yhc['btid'],$yhc['sqldz'],true) ?: [];
	}
	logjl($yhc['user'],'SSL证书',($type=='false'?'申请':'续签').'了SSL证书',($data['status']??false?'操作成功':'操作失败'),$DB);
	if($data['status']??false) {
		exit(json_encode(["qk"=>1,'code'=>($data['msg'][0]??'')],256));
	} else {
		exit(json_encode(["qk"=>4,'code'=>($data['msg'][0]??'')],256));
	}
	return;
}
if($egn=='setssl') {
	//设置key和pem
	$key=$_POST['key'] ?? '';
	$pem=$_POST['pem'] ?? '';
	if($key=='' || $pem=='')exit(json_encode(["qk"=>4,'code'=>'错误！禁止留空！'],256));
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$data=$api->setsslpem($yhc['sqldz'],$key,$pem) ?: [];
	logjl($yhc['user'],'SSL证书','手动设置了SSL证书',($data['status']??false?'设置成功':'设置失败'),$DB);
	if($data['status']??false) {
		exit(json_encode(["qk"=>1,'code'=>($data['msg']??'')],256));
	} else {
		exit(json_encode(["qk"=>4,'code'=>($data['msg']??'')],256));
	}
	return;
}
if($egn=='getssl') {
	//获取证书配置
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$data=$api->getsslpem($yhc['sqldz']);
	$data = $data ?: [];
	exit(json_encode(["key"=>($data['key']??''),"csr"=>($data['csr']??''),"httpTohttps"=>($data['httpTohttps']??''),"status"=>($data['status']??''),"cert_data"=>($data['cert_data']??''),"type"=>($data['type']??'')],256));

	return;
}
if($egn=='clossl') {
	//关闭SSL
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$data=$api->closessl($yhc['sqldz']) ?: [];
	logjl($yhc['user'],'SSL证书','关闭了SSL证书',($data['status']??false?'关闭成功':'关闭失败'),$DB);
	if($data['status']??false) {
		exit(json_encode(["qk"=>1,'code'=>($data['msg']??'')],256));
	} else {
		exit(json_encode(["qk"=>4,'code'=>($data['msg']??'')],256));
	}
	return;
}
if($egn=='httpsqz') {
	//开启/关闭强制https
	$kg=$_POST['qk'] ?? '';
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$data=$api->httpsqzf($yhc['sqldz'],$kg) ?: [];
	logjl($yhc['user'],'HTTPS',($kg=='true'?'开启':'关闭').'了强制HTTPS',($data['status']??false?'操作成功':'操作失败'),$DB);
	if($data['status']??false) {
		exit(json_encode(["qk"=>1,'code'=>($data['msg']??'')],256));
	} else {
		exit(json_encode(["qk"=>4,'code'=>($data['msg']??'')],256));
	}
	return;
}
