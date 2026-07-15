<?php
if($egn=='urllist') {
	$type = isset($_POST['type']) && $_POST['type'] !== '' ? $_POST['type'] : 3;
	include("../class.php");
	$apie = new bt_api_set($btipe,$btkeye);
	$api = new bt_api($btipe,$btkeye);
	$list = $apie->btapi_ym($zjid) ?: [];
	$listz = $api->urlzmlls($zjid) ?: [];
	$arr=[];
	if($type==2 || $type==3) {
		foreach ($list as $val) {
			if(($val['name']??'')==$yhc['sqldz'])continue;
			$arr['url'][]=["name"=>$val['name']??'','port'=>$val['port']??'','addtime'=>$val['addtime']??'','path'=>'/'];
		}
	}
	if($type==1 || $type==3) {
		foreach (($listz['binding']??[]) as $val) {
			$arr['url'][]=["name"=>$val['domain']??'','port'=>$val['port']??'','addtime'=>$val['addtime']??'','path'=>$val['path']??''];
		}
	}
	$dirs = $listz['dirs'] ?? [];
	array_unshift($dirs,'/');
	$arr['dir']=$dirs;
	exit(json_encode($arr,256));
	return;
}
if($egn=='hqzmlls') {
	//获取当前运行目录(非/)下的子目录列表
	$arr=[];
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$yxml = ($api->yxmlrhq($zjid,$os_xt.$yhc['sqldz']) ?: [])['runPath']['runPath'] ?? '/';
	//获取运行目录
	if($yxml!='/') {
		$listz = $api->urlzmlls($zjid) ?: [];
		//子目录域名
		foreach (($listz['binding'] ?? []) as $val) {
			//子目录
			if(substr($val['path'],0,3)!='../') {
				$arr[]=$val['domain'];
			}
		}
	}
	if(empty($arr)) {
		exit('false');
	} else {
		exit(json_encode($arr,256));
	}
	return;
}
if($egn=='erurl') {
	$bym_list=$DB->get_all_prepare("SELECT * FROM MN_ym WHERE btdh=? and qk='true' order by id desc limit 9999", [$ssbt]) ?: [];
	$arr=[];
	foreach ($bym_list as $res) {
		$arr[]=["url"=>$res['url'],"jg"=>$res['jg'],"jj"=>$res['js']];
	}
	exit(json_encode($arr,256));
	return;
}
if($egn=='tjurl') {
	$path=$_POST['dirs'] ?? '';
	if($path=='')exit('{"code":"子目录不得为空！"}');
	$url=str_replace(' ','',$_POST['url'] ?? '');
	$url=str_replace('	','',$url);
	preg_match("/\d+\.\d+\.\d+\.\d+/",$url,$ure);
	$mhend=strripos($url,':');
	if(is_numeric($mhend))$iful=mb_substr($url,0,$mhend);
	else $iful=$url;
	    if($iful==$cert['btip'] || $ure[0]==$cert['btip'])exit('{"code":"禁止添加本站IP！"}');
	include("../class.php");
	$apie = new bt_api_set($btipe,$btkeye);
	$api = new bt_api($btipe,$btkeye);
	$ymzce = array_merge($apie->GetLogsy($zjid) ?: [],$api->urlzmlls($zjid)['binding'] ?? []);
	$azxcr=count($ymzce);
	if($azxcr>=$yhc['ymbds']+1 && $yhc['ymbds']!='0' && $yhc['ymbds']!='') {
		exit('{"code":"添加失败！域名已达到最大绑定数！"}');
	}
	$bym_list=$DB->get_all_prepare("SELECT * FROM MN_ym order by id desc limit 9999");
	foreach ($bym_list as $res) {
		if(strpos($url,$res['url'])!==false) {
			if($res['jg']>0) {
				exit('{"code":"禁止使用自定义添加本站的售卖二级域名"}');
			} else {
				$ke_url_fym=true;
				$ke_url_ym=$res;
			}
		}
	}
	if(strpos($path,'/') !==false) {
		$r_data = $apie->btapi_addym($zjid,$yhc['sqldz'],$url);
	} else {
		$r_data = $api->addzml($zjid,$url,$path,$os_xt.$yhc['sqldz']) ?: [];
	}
	$are=$r_data['status'] ?? 'false';
	logjl($yhc['user'],'域名添加','添加了域名'.$url,'添加成功',$DB);
	if($are=='true') {
		if($ke_url_fym) {
			$bs_tj=json_decode($ke_url_ym['json'],true);
			array_push($bs_tj,$yhc['user']);
			$tj_jg=json_encode($bs_tj,256);
			$ddxx_url=$ke_url_ym['id'];
			$DB->query_prepare("update `MN_ym` set `json` =? where `id`=?", [$tj_jg, $ddxx_url]);
		}
		json_exit('添加成功');
	} else json_exit('添加失败'.($r_data['msg']??'未知错误'));
	return;
}
if($egn=='scurl') {
	$url=daddslashes($_POST['url']);
	$dk=daddslashes($_POST['port']);
	$path=daddslashes($_POST['dir']);
	if($url==$yhc['sqldz']) {
		exit('{"code":"禁止删除主机名称"}');
	}
	include("../class.php");
	$apie = new bt_api_set($btipe,$btkeye);
	$api = new bt_api($btipe,$btkeye);
	if($path=='/') {
		$r_data = $apie->btapi_delym($zjid,$yhc['sqldz'],$url,$dk);
	} else {
		$r_data = $api->delzml($zjid,$url,$os_xt.$yhc['sqldz']) ?: [];
	}
	$are=$r_data['status'] ?? 'false';
	logjl($yhc['user'],'域名删除','删除了域名'.$url,($are=='true'?'删除成功':'删除失败'),$DB);
	if($are=='true')json_exit('删除成功'); else json_exit('删除失败'.($r_data['msg']??'未知错误'));
	return;
}
if($egn=='seturl') {
	$url_zy=daddslashes($_POST['zym']);
	//主域
	$url_jqz=daddslashes($_POST['jqz']);
	//旧前缀
	$url_xqz=daddslashes($_POST['xqz']);
	//新前缀
	$url_path=daddslashes($_POST['path']);
	//新前缀
	$xurl=$url_xqz.'.'.$url_zy;
	//新域名
	$durl=$url_jqz.'.'.$url_zy;
	//旧域名
	$dk=daddslashes($_POST['port']);
	if($durl==$yhc['sqldz']) {
		exit('{"code":"禁止删除主机名称"}');
	}
	if(!preg_match('/^[0-9a-zA-Z]{1,24}$/',$url_xqz) ||	!preg_match('/^[0-9a-zA-Z]{1,24}$/',$url_jqz))exit('{"code":"域名前缀不合法！'.$url.'"}');
	include("../class.php");
	$apie = new bt_api_set($btipe,$btkeye);
	$api = new bt_api($btipe,$btkeye);
	$ymzce = array_merge($apie->GetLogsy($zjid) ?: [],$api->urlzmlls($zjid)['binding'] ?? []);
	$azxcr=0;
	$jpath='/';
	foreach($ymzce as $val) {
		if($val['domain']==$durl)$jpath=$val['path'];
		$azxcr++;
	}
	if($azxcr>$yhc['ymbds']+1 && $yhc['ymbds']!='0' && $yhc['ymbds']!='') {
		exit('{"code":"添加失败！域名已达到最大绑定数！如想继续添加则请删除多余闲置域名！"}');
	}
	if($jpath=='/') {
		$r_data = $apie->btapi_delym($zjid,$yhc['sqldz'],$durl,$dk);
	} else {
		$r_data = $api->delzml($zjid,$durl,$os_xt.$yhc['sqldz']) ?: [];
	}
	$are=$r_data['status'] ?? 'false';
	if($are=='true') {
		$url=str_replace(' ','',$xurl);
		$url=str_replace('	','',$url);
		preg_match("/\d+\.\d+\.\d+\.\d+/",$url,$ure);
		if($url==$cert['btip'] || $ure[0]==$cert['btip'])exit('{"code":"禁止添加本站IP！"}');
		$ymzce = array_merge($apie->GetLogsy($zjid) ?: [],$api->urlzmlls($zjid)['binding'] ?? []);
		$azxcr=count($ymzce);
		if($azxcr>=$yhc['ymbds']+1 && $yhc['ymbds']!='0' && $yhc['ymbds']!='') {
			exit('{"code":"添加失败！域名已达到最大绑定数！如想继续添加则请删除多余闲置域名！"}');
		}
		if(strpos($url_path,'/') !==false) {
			$r_data = $apie->btapi_addym($zjid,$yhc['sqldz'],$url.':'.$dk);
		} else {
			$r_data = $api->addzml($zjid,$url.':'.$dk,$url_path,$os_xt.$yhc['sqldz']) ?: [];
		}
		$are=$r_data['status'] ?? 'false';
		logjl($yhc['user'],'域名修改','将域名'.$durl.'修改为'.$xurl,($are=='true'?'修改成功':'修改失败'),$DB);
		if($are=='true')json_exit('添加成功'); else json_exit('添加失败'.($r_data['msg']??'未知错误'));
	} else json_exit('删除失败'.($are['msg']??'未知错误'));
	return;
}
if($egn=='listurl') {
	//获取域名列表(包含子目录)
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$data=$api->get_site_domains($yhc['btid']);
	$arr=[];
	foreach (($data['domains'] ?? []) as $val) {
		if($val['name']!=$yhc['sqldz']) {
			$arr['domains'][]=["name"=>$val['name']];
		}
	}
	exit(json_encode($arr,256));
	return;
}
