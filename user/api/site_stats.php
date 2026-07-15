<?php
if ($egn == 'site_stats') {
    $site = $yhc['sqldz'];
    if (empty($site)) {
        exit(json_encode(['code' => '未关联站点'], JSON_UNESCAPED_UNICODE));
    }
    $act = $_POST['act'] ?? '';
    $range = $_POST['range'] ?? 'today';
    $page = intval($_POST['page'] ?? 1);
    $page_size = min(intval($_POST['page_size'] ?? 10), 200);

    include("../class.php");
    $api = new bt_api($btipe, $btkeye);

    switch ($act) {
        case 'overview':
            $result = $api->pluginRequest('mnbt_connector', 'get_site_overview', [
                'site' => $site, 'range' => $range,
            ]);
            break;
        case 'trend':
            $result = $api->pluginRequest('mnbt_connector', 'get_site_trend', [
                'site' => $site, 'range' => $range,
            ]);
            break;
        case 'ip_rank':
            $result = $api->pluginRequest('mnbt_connector', 'get_site_ip_rank', [
                'site' => $site, 'range' => $range,
                'page' => $page, 'page_size' => $page_size,
            ]);
            break;
        case 'uri_rank':
            $result = $api->pluginRequest('mnbt_connector', 'get_site_uri_rank', [
                'site' => $site, 'range' => $range,
                'page' => $page, 'page_size' => $page_size,
            ]);
            break;
        case 'errors':
            $result = $api->pluginRequest('mnbt_connector', 'get_site_error_logs', [
                'site' => $site, 'range' => $range,
                'page' => $page, 'page_size' => $page_size,
            ]);
            break;
        case 'spider':
            $result = $api->pluginRequest('mnbt_connector', 'get_site_spider_analysis', [
                'site' => $site, 'range' => $range,
                'page' => $page, 'page_size' => $page_size,
            ]);
            break;
        case 'client':
            $result = $api->pluginRequest('mnbt_connector', 'get_site_client_stats', [
                'site' => $site, 'range' => $range,
                'page' => $page, 'page_size' => $page_size,
            ]);
            break;
        case 'method':
            $result = $api->pluginRequest('mnbt_connector', 'get_site_method_stats', [
                'site' => $site, 'range' => $range,
                'page' => $page, 'page_size' => $page_size,
            ]);
            break;
        case 'recent':
            $result = $api->pluginRequest('mnbt_connector', 'get_site_recent_logs', [
                'site' => $site,
                'page' => $page, 'page_size' => $page_size,
            ]);
            break;
        default:
            exit(json_encode(['code' => '未知操作'], JSON_UNESCAPED_UNICODE));
    }

    if (empty($result)) {
        exit(json_encode(['code' => '请求插件失败，请检查节点状态'], JSON_UNESCAPED_UNICODE));
    }
    $result['_site'] = $site;
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}
