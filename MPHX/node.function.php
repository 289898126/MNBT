<?php
if(!defined('IN_CRONLITE'))exit();

function mnbt_node_json($success, $msg, $data = []) {
    return json_encode([
        'success' => (bool)$success,
        'code' => $success ? 200 : 400,
        'msg' => $msg,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE);
}

function mnbt_node_exit($success, $msg, $data = []) {
    exit(mnbt_node_json($success, $msg, $data));
}

function mnbt_node_random_id($prefix) {
    return $prefix . '_' . bin2hex(random_bytes(12));
}

function mnbt_node_query_ignore_duplicate_column($DB, $sql) {
    try {
        return $DB->query($sql);
    } catch (mysqli_sql_exception $e) {
        if ((int)$e->getCode() === 1060 || stripos($e->getMessage(), 'Duplicate column name') !== false) {
            return false;
        }
        throw $e;
    }
}

function mnbt_node_ensure_tables($DB) {
    // 数据表结构统一在 install.sql 中创建，此函数保留用于运行时的兼容性检查
    // 实际表结构在系统安装时由 install.sql 创建，升级时通过增量 SQL 维护
}

function mnbt_node_platform_secret($conf) {
    if (is_array($conf) && !empty($conf['node_api_key'])) return (string)$conf['node_api_key'];
    if (is_array($conf) && !empty($conf['api'])) return (string)$conf['api'];
    return defined('SYS_KEY') ? SYS_KEY : 'MNBT';
}

function mnbt_node_default_base_url() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === '') return '';
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $dir = trim(str_replace('\\', '/', dirname($script)), '/');
    if ($dir === 'admin') {
        $dir = '';
    } elseif (substr($dir, -6) === '/admin') {
        $dir = substr($dir, 0, -6);
    }
    return rtrim($scheme . '://' . $host . ($dir === '' ? '' : '/' . $dir), '/');
}

function mnbt_node_admin_config($conf, $node, $mnbtUrl = '', $intervalSeconds = 10) {
    $intervalSeconds = max(5, (int)$intervalSeconds);
    $mnbtUrl = rtrim((string)$mnbtUrl, '/');
    if ($mnbtUrl === '') $mnbtUrl = mnbt_node_default_base_url();
    return [
        'mnbt_url' => $mnbtUrl,
        'platform_secret' => mnbt_node_platform_secret($conf),
        'node_id' => (string)($node['node_id'] ?? ''),
        'node_secret' => (string)($node['node_secret'] ?? ''),
        'node_name' => (string)($node['node_name'] ?? ''),
        'version' => '0.1.0',
        'interval_seconds' => $intervalSeconds,
        'capabilities' => [
            'heartbeat',
            'task',
            'report',
            'forbidden_scan',
        ],
    ];
}

function mnbt_node_effective_status($node, $now = null, $offlineAfterSeconds = 60) {
    if (($node['enabled'] ?? 'true') !== 'true') return 'disabled';
    if ($now === null) $now = time();
    $lastHeartbeat = trim((string)($node['last_heartbeat'] ?? ''));
    if ($lastHeartbeat === '') return 'offline';
    $heartbeatTime = strtotime($lastHeartbeat);
    if (!$heartbeatTime) return 'offline';
    return ((int)$now - (int)$heartbeatTime) <= (int)$offlineAfterSeconds ? 'online' : 'offline';
}

function mnbt_node_body_hash($body) {
    return hash('sha256', (string)$body);
}

function mnbt_node_signing_key($platformSecret, $nodeSecret) {
    return hash_hmac('sha256', (string)$nodeSecret, (string)$platformSecret);
}

function mnbt_node_canonical_request($method, $path, $body, $timestamp, $nonce) {
    return strtoupper((string)$method) . "\n" .
        (string)$path . "\n" .
        mnbt_node_body_hash($body) . "\n" .
        (string)$timestamp . "\n" .
        (string)$nonce;
}

function mnbt_node_signature($method, $path, $body, $platformSecret, $nodeSecret, $timestamp, $nonce) {
    return hash_hmac(
        'sha256',
        mnbt_node_canonical_request($method, $path, $body, $timestamp, $nonce),
        mnbt_node_signing_key($platformSecret, $nodeSecret)
    );
}

function mnbt_node_build_headers($nodeId, $method, $path, $body, $platformSecret, $nodeSecret, $timestamp = null, $nonce = null) {
    if ($timestamp === null) $timestamp = time();
    if ($nonce === null) $nonce = bin2hex(random_bytes(12));
    return [
        'X-MNBT-Node' => $nodeId,
        'X-MNBT-Time' => (string)$timestamp,
        'X-MNBT-Nonce' => $nonce,
        'X-MNBT-Sign' => mnbt_node_signature($method, $path, $body, $platformSecret, $nodeSecret, $timestamp, $nonce),
    ];
}

function mnbt_node_header_value($headers, $name) {
    foreach ($headers as $key => $value) {
        if (strtolower((string)$key) === strtolower($name)) return is_array($value) ? reset($value) : $value;
    }
    return '';
}

function mnbt_node_verify_signature($headers, $method, $path, $body, $platformSecret, $nodeSecret, $now = null) {
    $timestamp = (int)mnbt_node_header_value($headers, 'X-MNBT-Time');
    $nonce = (string)mnbt_node_header_value($headers, 'X-MNBT-Nonce');
    $sign = (string)mnbt_node_header_value($headers, 'X-MNBT-Sign');
    if ($timestamp <= 0 || $nonce === '' || $sign === '') return false;
    if ($now === null) $now = time();
    if (abs((int)$now - $timestamp) > 300) return false;
    $expected = mnbt_node_signature($method, $path, $body, $platformSecret, $nodeSecret, $timestamp, $nonce);
    return hash_equals($expected, $sign);
}

function mnbt_node_request_path() {
    $uri = $_SERVER['REQUEST_URI'] ?? '/api/node.php';
    $path = parse_url($uri, PHP_URL_PATH) ?: '/api/node.php';
    $query = $_SERVER['QUERY_STRING'] ?? '';
    return $query === '' ? $path : $path . '?' . $query;
}

function mnbt_node_get_headers() {
    if (function_exists('getallheaders')) return getallheaders();
    $headers = [];
    foreach ($_SERVER as $key => $value) {
        if (substr($key, 0, 5) === 'HTTP_') {
            $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
            $headers[$name] = $value;
        }
    }
    return $headers;
}

function mnbt_node_normalize_json($value) {
    if (is_array($value)) return $value;
    if (!is_string($value) || trim($value) === '') return [];
    $data = json_decode($value, true);
    return is_array($data) ? $data : [];
}

function mnbt_node_clip($value, $maxLength) {
    $value = trim((string)$value);
    if (function_exists('mb_substr')) return mb_substr($value, 0, $maxLength, 'UTF-8');
    return substr($value, 0, $maxLength);
}

function mnbt_node_match_target($match) {
    $type = $match['type'] ?? 'file';
    if ($type === 'database') {
        $table = $match['table'] ?? '';
        $field = $match['field'] ?? '';
        $recordId = isset($match['record_id']) ? '#' . $match['record_id'] : '';
        return trim($table . '.' . $field . $recordId, '.');
    }
    if ($type === 'log') return (string)($match['path'] ?? $match['url'] ?? '');
    return (string)($match['path'] ?? '');
}

function mnbt_node_normalize_forbidden_report($payload) {
    $payload = mnbt_node_normalize_json($payload);
    $summary = isset($payload['summary']) && is_array($payload['summary']) ? $payload['summary'] : [];
    $matches = isset($payload['matches']) && is_array($payload['matches']) ? $payload['matches'] : [];
    $normalizedMatches = [];
    foreach ($matches as $match) {
        if (!is_array($match)) continue;
        $normalizedMatches[] = [
            'site' => mnbt_node_clip($match['site'] ?? ($payload['site'] ?? ''), 250),
            'type' => mnbt_node_clip($match['type'] ?? 'file', 30),
            'target' => mnbt_node_clip(mnbt_node_match_target($match), 1000),
            'line' => max(0, (int)($match['line'] ?? 0)),
            'keyword' => mnbt_node_clip($match['keyword'] ?? '', 250),
            'excerpt' => mnbt_node_clip($match['excerpt'] ?? '', 200),
        ];
    }
    if (!isset($summary['matches'])) $summary['matches'] = count($normalizedMatches);
    if (!isset($summary['scanned_files'])) $summary['scanned_files'] = 0;
    if (!isset($summary['scanned_rows'])) $summary['scanned_rows'] = 0;
    return [
        'site' => mnbt_node_clip($payload['site'] ?? '', 250),
        'summary' => $summary,
        'matches' => $normalizedMatches,
    ];
}

function mnbt_node_cleanup_nonces($DB) {
    $cutoff = date('Y-m-d H:i:s', time() - 600);
    $DB->query_prepare("DELETE FROM `MN_node_nonce` WHERE `created_at` < ?", [$cutoff]);
}

function mnbt_node_nonce_used($DB, $nodeId, $nonce) {
    mnbt_node_cleanup_nonces($DB);
    $exists = $DB->get_row_prepare("SELECT id FROM `MN_node_nonce` WHERE `node_id`=? AND `nonce`=? LIMIT 1", [$nodeId, $nonce]);
    if ($exists) return true;
    return !$DB->query_prepare("INSERT INTO `MN_node_nonce` (`node_id`,`nonce`,`created_at`) VALUES (?,?,?)", [$nodeId, $nonce, date('Y-m-d H:i:s')]);
}

function mnbt_node_authenticate($DB, $conf, $body) {
    mnbt_node_ensure_tables($DB);
    $headers = mnbt_node_get_headers();
    $nodeId = (string)mnbt_node_header_value($headers, 'X-MNBT-Node');
    $nonce = (string)mnbt_node_header_value($headers, 'X-MNBT-Nonce');
    if ($nodeId === '') return [false, null, '缺少节点ID'];
    $node = $DB->get_row_prepare("SELECT * FROM `MN_node` WHERE `node_id`=? LIMIT 1", [$nodeId]);
    if (!$node || ($node['enabled'] ?? 'true') !== 'true') return [false, null, '节点不存在或已禁用'];
    if (!mnbt_node_verify_signature($headers, $_SERVER['REQUEST_METHOD'] ?? 'POST', mnbt_node_request_path(), $body, mnbt_node_platform_secret($conf), $node['node_secret'])) {
        return [false, null, '节点签名校验失败'];
    }
    if (mnbt_node_nonce_used($DB, $nodeId, $nonce)) return [false, null, '重复请求'];
    return [true, $node, 'ok'];
}

function mnbt_node_upsert_heartbeat($DB, $node, $payload) {
    $capabilities = isset($payload['capabilities']) ? json_encode($payload['capabilities'], JSON_UNESCAPED_UNICODE) : ($node['capabilities'] ?? '[]');
    $version = (string)($payload['version'] ?? ($node['version'] ?? ''));
    $nodeName = (string)($payload['node_name'] ?? ($node['node_name'] ?? ''));
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $now = date('Y-m-d H:i:s');
    $DB->query_prepare(
        "UPDATE `MN_node` SET `node_name`=?, `status`='online', `ip`=?, `version`=?, `capabilities`=?, `last_heartbeat`=?, `updated_at`=? WHERE `node_id`=?",
        [$nodeName, $ip, $version, $capabilities, $now, $now, $node['node_id']]
    );
}

function mnbt_node_pull_task($DB, $nodeId) {
    $task = $DB->get_row_prepare("SELECT * FROM `MN_node_task` WHERE `node_id`=? AND `status`='pending' ORDER BY id ASC LIMIT 1", [$nodeId]);
    if (!$task) return null;
    $now = date('Y-m-d H:i:s');
    $DB->query_prepare("UPDATE `MN_node_task` SET `status`='running', `pulled_at`=?, `updated_at`=? WHERE `task_id`=?", [$now, $now, $task['task_id']]);
    return [
        'task_id' => $task['task_id'],
        'action' => $task['action'],
        'payload' => mnbt_node_normalize_json($task['payload']),
    ];
}

function mnbt_node_store_forbidden_report($DB, $nodeId, $taskId, $payload, $status = 'success') {
    $report = mnbt_node_normalize_forbidden_report($payload);
    $summary = $report['summary'];
    $now = date('Y-m-d H:i:s');
    $DB->query_prepare("DELETE FROM `MN_forbidden_scan` WHERE `task_id`=?", [$taskId]);
    $DB->query_prepare("DELETE FROM `MN_forbidden_match` WHERE `task_id`=?", [$taskId]);
    $DB->query_prepare(
        "INSERT INTO `MN_forbidden_scan` (`task_id`,`node_id`,`site`,`status`,`scanned_files`,`scanned_rows`,`matches_count`,`summary`,`created_at`,`updated_at`) VALUES (?,?,?,?,?,?,?,?,?,?)",
        [
            $taskId,
            $nodeId,
            $report['site'],
            $status,
            (int)($summary['scanned_files'] ?? 0),
            (int)($summary['scanned_rows'] ?? 0),
            (int)($summary['matches'] ?? count($report['matches'])),
            json_encode($summary, JSON_UNESCAPED_UNICODE),
            $now,
            $now,
        ]
    );
    foreach ($report['matches'] as $match) {
        $DB->query_prepare(
            "INSERT INTO `MN_forbidden_match` (`task_id`,`node_id`,`site`,`match_type`,`target`,`line_no`,`keyword`,`excerpt`,`created_at`) VALUES (?,?,?,?,?,?,?,?,?)",
            [$taskId, $nodeId, $match['site'], $match['type'], $match['target'], $match['line'], $match['keyword'], $match['excerpt'], $now]
        );
    }
}

function mnbt_node_report_result($DB, $nodeId, $payload) {
    $taskId = (string)($payload['task_id'] ?? '');
    if ($taskId === '') return [false, '缺少任务ID'];
    $task = $DB->get_row_prepare("SELECT * FROM `MN_node_task` WHERE `task_id`=? AND `node_id`=? LIMIT 1", [$taskId, $nodeId]);
    if (!$task) return [false, '任务不存在'];
    $status = (($payload['status'] ?? 'success') === 'failed') ? 'failed' : 'success';
    $result = $payload['result'] ?? [];
    $error = (string)($payload['error'] ?? '');
    if (($task['action'] ?? '') === 'forbidden_scan' && $status === 'success') {
        mnbt_node_store_forbidden_report($DB, $nodeId, $taskId, $result, $status);
    }
    $now = date('Y-m-d H:i:s');
    $DB->query_prepare(
        "UPDATE `MN_node_task` SET `status`=?, `result`=?, `error`=?, `finished_at`=?, `updated_at`=? WHERE `task_id`=?",
        [$status, json_encode($result, JSON_UNESCAPED_UNICODE), $error, $now, $now, $taskId]
    );
    return [true, '结果已接收'];
}

function mnbt_node_register($DB, $btId, $nodeName = '', $nodeId = '', $nodeSecret = '') {
    mnbt_node_ensure_tables($DB);
    if ($nodeId === '') $nodeId = mnbt_node_random_id('node');
    if ($nodeSecret === '') $nodeSecret = bin2hex(random_bytes(32));
    $now = date('Y-m-d H:i:s');
    $exists = $DB->get_row_prepare("SELECT id FROM `MN_node` WHERE `node_id`=? LIMIT 1", [$nodeId]);
    if ($exists) {
        $DB->query_prepare(
            "UPDATE `MN_node` SET `bt_id`=?, `node_name`=?, `node_secret`=?, `enabled`='true', `updated_at`=? WHERE `node_id`=?",
            [(int)$btId, $nodeName, $nodeSecret, $now, $nodeId]
        );
    } else {
        $DB->query_prepare(
            "INSERT INTO `MN_node` (`bt_id`,`node_id`,`node_name`,`node_secret`,`status`,`enabled`,`created_at`,`updated_at`) VALUES (?,?,?,?,?,?,?,?)",
            [(int)$btId, $nodeId, $nodeName, $nodeSecret, 'offline', 'true', $now, $now]
        );
    }
    return [
        'node_id' => $nodeId,
        'node_secret' => $nodeSecret,
    ];
}

function mnbt_node_create_task($DB, $nodeId, $action, $payload = []) {
    mnbt_node_ensure_tables($DB);
    $taskId = mnbt_node_random_id('task');
    $now = date('Y-m-d H:i:s');
    $DB->query_prepare(
        "INSERT INTO `MN_node_task` (`task_id`,`node_id`,`action`,`payload`,`status`,`created_at`,`updated_at`) VALUES (?,?,?,?,?,?,?)",
        [$taskId, $nodeId, $action, json_encode($payload, JSON_UNESCAPED_UNICODE), 'pending', $now, $now]
    );
    return $taskId;
}