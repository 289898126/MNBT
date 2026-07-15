<?php
$monitor_actions = ['monitor_save','monitor_del','monitor_toggle','notice_read'];
if(!in_array($egn, $monitor_actions, true)) return;
include_once("../MPHX/monitor.function.php");
monitor_ensure_tables($DB);

if($egn=='monitor_save') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $task_type = trim($_POST['task_type'] ?? 'url');
    $url = trim($_POST['url'] ?? '');
    $resource_type = trim($_POST['resource_type'] ?? '');
    $resource_threshold = max(1, min(100, intval($_POST['resource_threshold'] ?? 80)));
    $method = monitor_normalize_method($_POST['method'] ?? 'GET');
    if(!in_array($task_type, ['url','resource'], true)) $task_type = 'url';
    $interval = monitor_normalize_interval($task_type, $_POST['interval_seconds'] ?? 60);
    $timeout = max(1, min(30, intval($_POST['timeout_seconds'] ?? 10)));
    $status_rule = trim($_POST['status_rule'] ?? 'eq');
    $status_value = trim($_POST['status_value'] ?? '200');
    $content_rule = trim($_POST['content_rule'] ?? 'none');
    $content_value = trim($_POST['content_value'] ?? '');
    $fail_threshold = max(1, intval($_POST['fail_threshold'] ?? 1));
    $notify_email = ($_POST['notify_email'] ?? 'true') === 'true' ? 'true' : 'false';
    $enabled = ($_POST['enabled'] ?? 'true') === 'true' ? 'true' : 'false';
    if($name==='') json_exit('任务名称不能为空');
    if($task_type === 'url' && $url==='') json_exit('URL不能为空');
    if($task_type === 'resource' && !in_array($resource_type, ['web','sql','traffic'], true)) json_exit('请选择资源类型');
    if($task_type === 'url' && !monitor_is_safe_url($url)) json_exit('URL格式错误或不允许访问内网地址');
    if($task_type === 'resource') {
        $url = '';
        $method = 'GET';
        $timeout = 10;
        $status_rule = 'eq';
        $status_value = '200';
        $content_rule = 'none';
        $content_value = '';
        $fail_threshold = 1;
    }
    $now = date('Y-m-d H:i:s');
    $next = date('Y-m-d H:i:s', time()+$interval);
    if($id>0) {
        $row = $DB->get_row_prepare("SELECT id FROM MN_monitor_task WHERE id=? AND user=? limit 1", [$id, $yhc['user']]);
        if(!$row) json_exit('任务不存在');
        $ok = $DB->query_prepare("UPDATE MN_monitor_task SET name=?,task_type=?,url=?,resource_type=?,resource_threshold=?,method=?,interval_seconds=?,timeout_seconds=?,status_rule=?,status_value=?,content_rule=?,content_value=?,fail_threshold=?,notify_email=?,enabled=?,next_run=?,updated_at=? WHERE id=? AND user=?", [$name,$task_type,$url,$resource_type,$resource_threshold,$method,$interval,$timeout,$status_rule,$status_value,$content_rule,$content_value,$fail_threshold,$notify_email,$enabled,$next,$now,$id,$yhc['user']]);
        json_exit($ok?'保存成功':'保存失败');
    } else {
        $task_count = $DB->count_prepare("SELECT count(*) FROM MN_monitor_task WHERE user=?", [$yhc['user']]);
        if($task_count >= 5) json_exit('每个用户最多只能添加5个监控任务');
        $ok = $DB->query_prepare("INSERT INTO MN_monitor_task (user,name,task_type,url,resource_type,resource_threshold,method,interval_seconds,timeout_seconds,status_rule,status_value,content_rule,content_value,fail_threshold,notify_email,enabled,next_run,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", [$yhc['user'],$name,$task_type,$url,$resource_type,$resource_threshold,$method,$interval,$timeout,$status_rule,$status_value,$content_rule,$content_value,$fail_threshold,$notify_email,$enabled,$next,$now,$now]);
        json_exit($ok?'添加成功':'添加失败');
    }
    return;
}

if($egn=='monitor_del') {
    $id = intval($_POST['id'] ?? 0);
    if($id<=0) json_exit('参数错误');
    $ok = $DB->query_prepare("DELETE FROM MN_monitor_task WHERE id=? AND user=?", [$id, $yhc['user']]);
    $DB->query_prepare("DELETE FROM MN_monitor_log WHERE task_id=? AND user=?", [$id, $yhc['user']]);
    json_exit($ok?'删除成功':'删除失败');
    return;
}

if($egn=='monitor_toggle') {
    $id = intval($_POST['id'] ?? 0);
    $enabled = ($_POST['enabled'] ?? 'true') === 'true' ? 'true' : 'false';
    if($id<=0) json_exit('参数错误');
    $next = date('Y-m-d H:i:s', time()+60);
    $ok = $DB->query_prepare("UPDATE MN_monitor_task SET enabled=?,next_run=?,updated_at=? WHERE id=? AND user=?", [$enabled,$next,date('Y-m-d H:i:s'),$id,$yhc['user']]);
    json_exit($ok?'修改成功':'修改失败');
    return;
}

if($egn=='notice_read') {
    $id = intval($_POST['id'] ?? 0);
    if($id>0) {
        $ok = $DB->query_prepare("UPDATE MN_notice_log SET is_read='true' WHERE id=? AND user=?", [$id, $yhc['user']]);
    } else {
        $ok = $DB->query_prepare("UPDATE MN_notice_log SET is_read='true' WHERE user=?", [$yhc['user']]);
    }
    json_exit($ok?'操作成功':'操作失败');
    return;
}
