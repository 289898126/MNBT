<?php
if(in_array($egn, ['cacheadd','addcache','cacheedit','editcache','cacheupdate','setcache','cache'], true)) {
	$suffix = trim($_POST['suffix'] ?? ($_POST['ext'] ?? ''));
	$time_out = trim($_POST['time_out'] ?? ($_POST['time'] ?? '30d'));
	$old_suffix = trim($_POST['old_suffix'] ?? '');
	if ($suffix === '') {
		json_exit('请输入文件后缀');
		return;
	}
	if (!preg_match('/^[A-Za-z0-9_,|-]+$/', $suffix)) {
		json_exit('文件后缀格式错误，仅允许字母、数字、逗号、竖线、中划线和下划线');
		return;
	}
	if ($old_suffix !== '' && !preg_match('/^[A-Za-z0-9_,|-]+$/', $old_suffix)) {
		json_exit('原文件后缀格式错误');
		return;
	}
	if (!preg_match('/^\d+[smhdMy]$/', $time_out)) {
		json_exit('缓存时间格式错误，例如：30d、12h、60m、3600s');
		return;
	}
	include_once("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$result = $api->set_static_cache($yhc['sqldz'], $suffix, $time_out, $old_suffix);
	if (isset($result['status']) && ($result['status'] === true || $result['status'] === 'true')) {
		$action = (in_array($egn, ['cacheedit','editcache','cacheupdate','setcache'], true) || $old_suffix !== '') ? '修改' : '新增';
		logjl($yhc['user'], '缓存配置', $action.'缓存规则: '.$suffix.' => '.$time_out, '操作成功', $DB);
		json_exit($action.'成功');
	} else {
		json_exit('操作失败：'.($result['msg'] ?? '未知错误'));
	}
	return;
}

if(in_array($egn, ['cachedel','delcache','cachedelete','deletecache','cache_del','cache_delete'], true)) {
	$suffix = trim($_POST['suffix'] ?? ($_POST['ext'] ?? ''));
	if ($suffix === '') {
		json_exit('参数错误');
		return;
	}
	if (!preg_match('/^[A-Za-z0-9_,|-]+$/', $suffix)) {
		json_exit('文件后缀格式错误');
		return;
	}
	include_once("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$result = $api->remove_static_cache($yhc['sqldz'], $suffix);
	if (isset($result['status']) && ($result['status'] === true || $result['status'] === 'true')) {
		logjl($yhc['user'], '缓存配置', '删除缓存规则: '.$suffix, '删除成功', $DB);
		json_exit('删除成功');
	} else {
		json_exit('操作失败：'.($result['msg'] ?? '未知错误'));
	}
	return;
}
