<?php

/**
 * 此文件为微信企业号 所有API定义
 */

/** 获取AccessToken, CorpID是企业号的标识，每个企业号拥有一个唯一的CorpID；Secret是管理组凭证密钥。
 * 正常情况下AccessToken有效期为7200秒，有效期内重复获取返回相同结果；有效期内有接口交互（包括获取AccessToken的接口），会自动续期。
*/
if(! function_exists('get_accesstoken'))
{
    /**
     * $token 企业号ID
    */
    function get_accesstoken($agentid=-1, $bflush=false)
    {
		$agentid = intval($agentid);
		$key = 'accessToken_'. ($agentid > 0 ? $agentid : 0);
		if(!$bflush && cache('?'.$key)){
			// 从缓存中读取 并且缓存存在
			return cache($key);
		}else{
			cache($key, null);
			$corpid = app\weixin\controller\Wxhandle::CORPID;
			if(empty($corpid))  return '';

			$secret = '';
			if($agentid > 0 && $agentid !== app\common\logic\AgentLogic::CONTACT_AID){
				$agentInfo = app\common\logic\AgentLogic::get($agentid, 'secret');
				(isset($agentInfo['secret']) && !empty($agentInfo['secret'])) && $secret = trim($agentInfo['secret']);
			}else{
				$secret = app\weixin\controller\Wxhandle::SECRET;
			}
			
			if(empty($secret))  return '';

			$expire = 0;
			$accessToken = my\Weixin::getAccesstoken($corpid, $secret, $expire);
			if(is_string($accessToken) && !empty($accessToken)) {
				$expire = intval($expire);
				$expire > 0 || $expire = 7200;
				cache($key, $accessToken, $expire);
				return $accessToken;
			}
		}
    }
}

if(! function_exists('qy_get_jsapi'))
{
    function qy_get_jsapi($qid=0, $agentid=-1, $funcname='')
    {
        if($qid==0)    return false;

        $key = 'jsApi_'.$qid.'_'.$agentid;
    	$res = cache($key);
    	if($res === false)
        {
            $err = -1;
            $api = APP_HTTP.API_WEB_DOMAIN.'/wx/api/jsapi_ticket';

            $response_list = api_http_get($api, ['qid' => $qid, 'agentid'=>$agentid, 'funcname'=>$funcname]);
            if(isset($response_list['ticket']) && isset($response_list['corpid'])){
                cache($key, $response_list, $response_list['expire']);
                $res = $response_list;
            }else{
                trace("qy_get_jsapi failed! " . json_encode($response_list));
                return false;
            }
        }

        return $res;
    }
}

// OAuth验证接口
if(! function_exists('qy_oauth_userinfo'))
{
    function qy_oauth_userinfo($access_token, $code, &$errCode=null)
    {
        $errInfo = array('access_token' => $access_token, 'code' => $code);
        if(!empty($access_token) && !empty($code))
        {
            $err = -1;
            $api = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token={$access_token}&code={$code}";
            $errInfo['response'] = curl_request_json($err, $api);
            if(!isset($errInfo['response']['errcode']))
            {
                return $errInfo['response'];
            }
        }
        
        is_null($errCode) || $errCode = intval($errInfo['response']['errcode']);
        trace("qy_oauth_userinfo failed! info: " . json_encode($errInfo));
        return false;
    }
}

// Userid与openid互换接口
if(! function_exists('qy_convert_to_openid'))
{
    function qy_convert_to_openid($access_token, $userid, $agentid=-1)
    {
        $errInfo = array('access_token' => $access_token, 'userid' => $userid, 'agentid' => $agentid);
        if(!empty($access_token) && !empty($userid))
        {
            $err = -1;
            $api = "https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_openid?access_token={$access_token}";
            
            $agentid = intval($agentid);
            $errInfo['reqdata'] = array('userid' => $userid);
            $agentid >= 0 && $errInfo['reqdata']['agentid'] = $agentid;
            $errInfo['response'] = curl_request_json($err, $api, urldecode(json_encode($errInfo['reqdata'])), 'POST');
            if(isset($errInfo['response']['errcode']) && intval($errInfo['response']['errcode']) === 0 && isset($errInfo['response']['openid']))
            {
                return $errInfo['response']['openid'];
            }
        }
        
        trace("qy_convert_to_openid failed! info: " . urldecode(json_encode($errInfo)));
        return false;
    }
}

if(! function_exists('qy_upload_media'))
{
    // 媒体文件类型，分别有图片（image）、语音（voice）、视频（video），普通文件(file)
    function qy_upload_media($access_token, $filename, $type)
    {
		//trace('=======token:'.$access_token.'=======name:'.$filename.'======'.$type);
        if(empty($access_token) || empty($filename) || !in_array($type, array('image', 'voice', 'video', 'file')))  return '';

        $err = -1;
        $data = array('media' => new CURLFile($filename));
        $api = "https://qyapi.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type={$type}";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $api);
    	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
    	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    	curl_setopt($curl, CURLOPT_URL, $api);
    	$response = curl_exec($curl);
		
		$error = curl_error($curl);
        if(false === $response)
        {
            return '';
        }

    	curl_close($curl);
        $response_list = json_decode($response, true);
        if(isset($response_list['media_id']) && !empty($response_list['media_id']))
        {
            return $response_list['media_id'];
        }
        else
        {
            trace("qy_user_update failed! data:" . json_encode($data). ' result: ' . json_encode($response_list));
            return '';
        }
    }
}

if(! function_exists('qy_get_media'))
{
    function qy_get_media($access_token, $media_id, &$fsize = null)
    {
        if(empty($access_token) || empty($media_id))  return false;

        $err = -1;
        $media_info = array();
        $api = "https://qyapi.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$media_id}";
        $response_list = curl_get_media($err, $media_info, $api);
        
        $response_list = json_decode($response_list, true);
        if(! isset($response_list['errcode']) && isset($media_info['header']['content_type']))
        {
            $content_type = $media_info['header']['content_type'];
            $content_type = explode('/', $content_type);
            $file_ext = $content_type[1];
            $local_file = C('PICTURE_UPLOAD.rootPath') . "$media_id.$file_ext";
            $local_file_fp = fopen($local_file, 'w');
            if(false !== $local_file_fp)
            {
                if(false !== fwrite($local_file_fp, $media_info['body']))
                {
                    fclose($local_file_fp);
                    trace("get_media local:$local_file");

                    if($content_type[0] == 'audio')
                    {
                        $retval = -1;
                        $local_file_2 = C('PICTURE_UPLOAD.rootPath') . "$media_id.wav";
                        system("ffmpeg -i '$local_file' '$local_file_2'", $retval);
                        if(intval($retval) == 0)
                        {
                            unlink($local_file);
                            $file_ext = 'wav';
                            $local_file = $local_file_2;
                        }
                        else
                        {
                            return false;
                        }
                    }
                    $data = array('status' => 1, 'create_time' => NOW_TIME);
                    $data['md5']  = md5_file($local_file);
                    $data['sha1'] = sha1_file($local_file);

                    $model_obj = D('Common/Picture');
                    $file_data = $model_obj->isFile($data);
                    if($file_data)
                    {
                        unlink($local_file);
                        return $file_data['id'];
                    }
                    else
                    {
                        $size = getimagesize($local_file);
                        if(is_array($size))
                        {
                            $data['width'] = intval($size[0]);
                            $data['height'] = intval($size[1]);
                        }

                        $data['size'] = filesize($local_file);
                        $ret = oss_sdk_upload($local_file, $data['md5']);
                        if(is_string($ret))
                        {
                            trace("get_media oss:$ret");
                            is_null($fsize) || $fsize = $data['size'];
                            unlink($local_file);
                            $data['path'] = $data['url'] = $ret;
                        }
                        else
                        {
                            /* 记录文件信息 */
                            $data['path'] = substr(C('PICTURE_UPLOAD.rootPath'), 1) . "$media_id.$file_ext";	//在模板里的url路径
                        }

                        if($id = $model_obj->add($data))
                        {
                            return $id;
                        }
                    }
                }
            }
        }

        return false;
    }
}