<?php
if($egn=='tjurl') {
	$yz_ip=daddslashes($_POST['yz_ip'] ?? '');
	$url=str_replace(' ','',$_POST['url'] ?? '');
	$url=str_replace('	','',$url);
	preg_match("/\d+\.\d+\.\d+\.\d+/",$url,$ure);
	preg_match("/\d+\.\d+\.\d+\.\d+/",$yz_ip,$ip_yzp);
	if (!preg_match('/^((?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1 -9]?\d))))$/', $yz_ip))exit('{"code":"源站IP不合法！"}');
	if($url==$cert['btip'] || $ure[0]==$cert['btip'] || $ip_yzp[0]==$cert['btip'])exit('{"code":"禁止添加宝塔IP！"}');
	$mhend=strripos($url,':');
if(is_numeric($mhend))$iful=mb_substr($url,0,$mhend);
else $iful=$url;
    if($iful==$cert['btip'] || $ure[0]==$cert['btip'])exit('{"code":"禁止添加本站IP！"}');
	
	include("../class.php");
	$apie = new bt_api_set($btipe,$btkeye);
	$apic = new bt_api($btipe,$btkeye);
	$ymzce = $apie->GetLogsy($zjid) ?: [];
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
	$r_data = $apie->btapi_addym($zjid,$yhc['sqldz'],$url) ?: [];
	$are=$r_data['status'] ?? '';
	if($are=='true') {
		$hhf='
';
		//换行符
		$get_host_hq = $apic->GetLogswt($l_ler_a) ?: [];
		$host_wjnr=($get_host_hq['data']??'').$hhf.$yz_ip.' '.$url;
		$get_host_xg = $apic->GetLogswh($host_wjnr,$l_ler_a);
		$get_fxdl_add = $apic->fxdl_add($url,$yhc['sqldz']);
		if($ke_url_fym) {
			$bs_tj=json_decode($ke_url_ym['json'],true);
			array_push($bs_tj,$yhc['user']);
			$tj_jg=json_encode($bs_tj,256);
			$ddxx_url=$ke_url_ym['id'];
			$DB->query_prepare("update `MN_ym` set `json` =? where `id`=?", [$tj_jg, $ddxx_url]);
		}
		json_exit('添加成功');
	} else json_exit('添加失败'.($r_data['msg']??''));
	return;
}
if($egn=='scurl') {
	$url=daddslashes($_POST['url'] ?? '');
	$dk=daddslashes($_POST['port'] ?? '');
	if($url==$yhc['sqldz']) {
		exit('{"code":"禁止删除主机名称"}');
	}
	include("../class.php");
	$apie = new bt_api_set($btipe,$btkeye);
	$apic = new bt_api($btipe,$btkeye);
	$r_data = $apie->btapi_delym($zjid,$yhc['sqldz'],$url,$dk) ?: [];
	$are=$r_data['status'] ?? '';
	if($are=='true') {
		$get_host_hq = $apic->GetLogswt($l_ler_a) ?: [];
		$kh='
';
		//换行符
		$arysz=explode($kh,$get_host_hq['data']??'');
		foreach($arysz as $val) {
			if(!strpos($val,' '.$url) && $val!='') {
				$ayrt.=$val.$kh;
			}
		}
		$get_host_xg = $apic->GetLogswh($ayrt,$l_ler_a);
		$get_fxdl_del = $apic->fxdl_del($url,$yhc['sqldz']);
		json_exit('删除成功');
	} else {
	    logjl($yhc['user'],'域名删除','删除域名'.$url.'失败','删除失败',$DB);
	    json_exit('删除失败'.$are['msg']);
	}
	return;
}
if($egn == "mailbd")
{
    $mailuser = daddslashes($_POST['mail']);
    $user = $yhc['user'];
    if($DB->query_prepare("UPDATE `MN_zj` SET `mailuser` = ? WHERE `user` = ?", [$mailuser, $user]))
    {
        json_exit('绑定成功');
    }
    else
    {
        json_exit('绑定失败,请联系开发者查询失败原因');
    }
    return;
}
if($egn=='seturl') {
	$url_zy=daddslashes($_POST['zym']);
	$url_jqz=daddslashes($_POST['jqz']);
	$url_xqz=daddslashes($_POST['xqz']);
	$xurl=$url_xqz.'.'.$url_zy;
	$durl=$url_jqz.'.'.$url_zy;
	$dk=daddslashes($_POST['port']);
	if($durl==$yhc['sqldz']) {
		exit('{"code":"禁止删除主机名称"}');
	}
	if(!preg_match('/^[0-9a-zA-Z]{1,24}$/',$url_xqz) ||	!preg_match('/^[0-9a-zA-Z]{1,24}$/',$url_jqz))exit('{"code":"域名前缀不合法！'.$url.'"}');
	include("../class.php");
	$apie = new bt_api_set($btipe,$btkeye);
	$apic = new bt_api($btipe,$btkeye);
	$ymzce = $apie->GetLogsy($zjid) ?: [];
	$azxcr=count($ymzce);
	if($azxcr>$yhc['ymbds']+1 && $yhc['ymbds']!='0' && $yhc['ymbds']!='') {
		exit('{"code":"添加失败！域名已达到最大绑定数！请删除多限制域名"}');
	}
	$r_data = $apie->btapi_delym($zjid,$yhc['sqldz'],$durl,$dk);
	$are=$r_data['status'];
	if($are=='true') {
		$url=str_replace(' ','',$xurl);
		$url=str_replace('	','',$url);
		preg_match("/\d+\.\d+\.\d+\.\d+/",$url,$ure);
		if($url==$cert['btip'] || $ure[0]==$cert['btip'])exit('{"code":"禁止添加本站IP！"}');
		$get_host_hq = $apic->GetLogswt($l_ler_a);
		$kh='
';
		//换行符
		$arysz=explode($kh,$get_host_hq['data']);
		$thhs=str_replace($durl,$url,$arysz);
		foreach($thhs as $val) {
			if($val!='') {
				$ayrt.=$val.$kh;
			}
		}
		$get_host_xg = $apic->GetLogswh($ayrt,$l_ler_a);
		$get_fxdl_del = $apic->fxdl_del($durl,$yhc['sqldz']);
		$r_data = $apie->btapi_addym($zjid,$yhc['sqldz'],$url.':'.$dk);
		$get_fxdl_add = $apic->fxdl_add($url,$yhc['sqldz']);
		$are=$r_data['status'];
		if($are=='true')json_exit('添加成功'); else json_exit('添加失败'.$r_data['msg']);
	} else json_exit('删除失败'.$are['msg']);
	return;
}
