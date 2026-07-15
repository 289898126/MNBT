<?php
@header('Content-Type: application/json; charset=UTF-8');
include("../MPHX/common.php");
require_once ROOT . 'MPHX/node.function.php';

$act = $_GET['act'] ?? '';
$body = file_get_contents('php://input');
$payload = json_decode($body, true);
if (!is_array($payload)) $payload = [];

[$ok, $node, $authMsg] = mnbt_node_authenticate($DB, $conf, $body);
if (!$ok) {
    mnbt_node_exit(false, $authMsg);
}

if ($act === 'heartbeat') {
    mnbt_node_upsert_heartbeat($DB, $node, $payload);
    mnbt_node_exit(true, 'heartbeat ok', [
        'server_time' => date('Y-m-d H:i:s'),
    ]);
}

if ($act === 'pull_task') {
    $task = mnbt_node_pull_task($DB, $node['node_id']);
    mnbt_node_exit(true, 'pull task ok', [
        'task' => $task,
    ]);
}

if ($act === 'report_result') {
    [$saved, $msg] = mnbt_node_report_result($DB, $node['node_id'], $payload);
    mnbt_node_exit($saved, $msg);
}

if ($act === 'get_config') {
    $config = [
        'forbidden_scan' => [
            'enabled' => ($conf['wjsckg'] ?? 'false') === 'true',
            'content' => $conf['wjsccnr'] ?? '',
            'scan_changed_only' => ($conf['wjsckgqbfx'] ?? 'true') === 'true',
            'scan_dir' => $conf['wjscml'] ?? '/www/wwwroot',
            'skip_dirs' => $conf['wjstqml'] ?? '.git,node_modules,vendor,runtime,cache,logs',
            'skip_exts' => $conf['wjstqhz'] ?? '.jpg,.png,.gif,.webp,.mp4,.zip,.rar,.7z,.pdf,.woff,.ttf',
            'max_file_size' => (int)($conf['wjscdzmax'] ?? 5242880),
            'max_matches' => (int)($conf['wjscdhmax'] ?? 1000),
            'full_scan_enabled' => ($conf['wjscqzcskg'] ?? 'true') === 'true',
            'full_scan_cron' => $conf['wjscqzcs'] ?? '0 3 * * *',
        ],
    ];
    mnbt_node_exit(true, 'config ok', $config);
}

mnbt_node_exit(false, 'unknown action');
