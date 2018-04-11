<?php

function curl_get_media(&$error, &$media_info, $url, $timeout = 5)
{
	$ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_NOBODY, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$response = curl_exec($ch);
	$httpinfo = curl_getinfo($ch);
    trace("curl_get_media httpinfo:".json_encode($httpinfo));

    $error = false;
	$errorCode = curl_errno($ch);
	if($errorCode)
	{
		$errorMessage = curl_error($ch);
		$error = array($errorCode, $errorMessage);
	}
	curl_close($ch);

    $media_info = array_merge(array('header' => $httpinfo), array('body' => $response));

	return $response;
}

/**
* 发起一个 HTTP(S) 请求, 并返回响应文本
*
* @param array 错误信息: array($errorCode, $errorMessage)
* @param string url
* @param array 参数数组
* @param string 请求类型	GET|POST
* @param int 超时时间
* @param array 扩展的包头信息
*
* @return string
*/
function curl_request(&$error, $url, $params = null, $method = 'GET', $timeout = 5, $extheaders = null, $output_header = false, $b_param_arr = false)
{
	if(!function_exists('curl_init')) exit('Need to open the curl extension.');

	$method = strtoupper($method);
	$curl = curl_init();
	//设置curl默认访问为IPv4
	if(defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	}
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_HEADER, $output_header);
	switch($method)
	{
		case 'POST':
			curl_setopt($curl, CURLOPT_POST, TRUE);
			if(!empty($params))
			{
				curl_setopt($curl, CURLOPT_POSTFIELDS, (is_array($params) ? ($b_param_arr ? $params : http_build_query($params)) : $params));
			}
			break;

		case 'DELETE':
		case 'GET':
			if($method == 'DELETE')
			{
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
			}
			if(!empty($params))
			{
				$url = $url . (strpos($url, '?') ? '&' : '?') . (is_array($params) ? http_build_query($params) : $params);
			}
			break;
	}
	curl_setopt($curl, CURLINFO_HEADER_OUT, TRUE);
	curl_setopt($curl, CURLOPT_URL, $url);
	if(!empty($extheaders))
	{
		curl_setopt($curl, CURLOPT_HTTPHEADER, (array)$extheaders);
	}

	$response = curl_exec($curl);
    if(false === $response)
    {
        $error = false;
    	$errorCode = curl_errno($curl);
    	if($errorCode)
    	{
    		$errorMessage = curl_error($curl);
    		$error = array($errorCode, $errorMessage);
            trace("curl_exec failed! " . json_encode($error));
    	}
    }
	curl_close($curl);
	return $response;
}

/**
* 发起一个 HTTP(S) 请求, 并返回 Json 格式的响应数据
*
* @param array 错误信息: array($errorCode, $errorMessage)
* @param string url
* @param array 参数数组
* @param string 请求类型 GET|POST
* @param int 超时时间
* @param array 扩展的包头信息
*
* @return string
*/
function curl_request_json(&$error, $url, $params = null, $method = 'GET', $timeout = 5, $extheaders = null, $output_header = false, $b_param_arr = false)
{
	$response = curl_request($error, $url, $params, $method, $timeout, $extheaders, $output_header, $b_param_arr);

	return json_decode($response, true);
}
