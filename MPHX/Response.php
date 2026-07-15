<?php
if (!defined('IN_CRONLITE')) exit();

class Response
{
    public static function build($code, $msg = null, $data = null, $redirect = null, $success = null)
    {
        if ($msg === null) $msg = is_scalar($code) ? (string)$code : '返回信息';
        if ($success === null) {
            $success = !in_array((string)$code, ['0', '4', '100', '300'], true);
        }
        $result = [
            'success' => (bool)$success,
            'code' => $code,
            'msg' => $msg,
            'redirect' => $redirect,
        ];
        if ($data !== null) $result['data'] = $data;
        return $result;
    }

    public static function json($code, $msg = null, $data = null, $redirect = null, $success = null)
    {
        return json_encode(self::build($code, $msg, $data, $redirect, $success), JSON_UNESCAPED_UNICODE);
    }

    public static function success($msg, $data = null, $redirect = null)
    {
        return self::json($msg, $msg, $data, $redirect, true);
    }

    public static function error($msg, $data = null, $redirect = null)
    {
        return self::json($msg, $msg, $data, $redirect, false);
    }

    public static function exit_json($code, $msg = null, $data = null, $redirect = null, $success = null)
    {
        exit(self::json($code, $msg, $data, $redirect, $success));
    }

    public static function exit_success($msg, $data = null, $redirect = null)
    {
        exit(self::json($msg, $msg, $data, $redirect, true));
    }

    public static function exit_error($msg, $data = null, $redirect = null)
    {
        exit(self::json($msg, $msg, $data, $redirect, false));
    }
}
