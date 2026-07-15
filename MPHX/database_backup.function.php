<?php
if(!defined('IN_CRONLITE'))exit();

function database_backup_list_data($response) {
    if (!is_array($response)) return [];
    return isset($response['data']) && is_array($response['data']) ? $response['data'] : [];
}

function database_backup_find_item($backups, $filename) {
    $filename = trim(str_replace('\\', '/', (string)$filename));
    if ($filename === '') return null;
    $basename = basename($filename);
    foreach ($backups as $item) {
        if (!is_array($item) || empty($item['filename'])) continue;
        $itemFilename = trim(str_replace('\\', '/', (string)$item['filename']));
        if ($itemFilename === $filename || basename($itemFilename) === $basename) {
            return $item;
        }
    }
    return null;
}

function database_backup_split_file($filename) {
    $filename = trim(str_replace('\\', '/', (string)$filename));
    $name = basename($filename);
    $path = substr($filename, 0, max(0, strlen($filename) - strlen($name)));
    return [$path, $name];
}

function database_backup_extract_download_token($response) {
    if (!is_array($response)) return '';
    if (isset($response['token']) && is_scalar($response['token'])) return trim((string)$response['token']);
    foreach (['msg', 'data'] as $key) {
        if (isset($response[$key]) && is_array($response[$key])) {
            $token = database_backup_extract_download_token($response[$key]);
            if ($token !== '') return $token;
        }
    }
    return '';
}

function database_backup_build_download_url($panelUrl, $token) {
    return rtrim((string)$panelUrl, '/') . '/down/' . ltrim((string)$token, '/');
}
