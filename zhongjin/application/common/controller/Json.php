<?php
/**
 * json 格式返回数据
 * by sherlock
 */

namespace app\common\controller;

use think\Response;
use think\exception\HttpResponseException;

class Json{
	public static function result($data, $code = 0, $msg = '', $header=[])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => \think\Request::instance()->server('REQUEST_TIME'),
            'data' => $data,
        ];
		
        $response = Response::create($result, 'json')->header($header);
        throw new HttpResponseException($response);
    }
}


