<?php
namespace app\weixin\controller;

use app\common\logic\AgentLogic;
use app\common\logic\WxreplyLogic;
use WXBiz\WXBizMsgCrypt;
use think\Controller;

class Wxhandle extends Controller
{
	const CORPID = 'ww590b66f676001a62';	// 企业微信的corpid
	const SECRET = 'fp7J_-kcOIZj7O_WK9pl8NihGFTC60sS2wZzKO1t0WA';	// 企业微信自带通讯录的secret
	
	private $wxcpt;
	private $sReqNonce;
	private $sReqTimestamp;
	private $sReqMsgSignature;
	
	
	/**
	 * 微信接受消息回调，包括用户关注和取消关注
	 */
    public function index()
    {
		$this->sReqNonce = input('param.nonce', '');
        $this->sReqTimestamp = input('param.timestamp', '');
        $this->sReqMsgSignature = input('param.msg_signature', '');
        if(empty($this->sReqNonce) || empty($this->sReqTimestamp) || empty($this->sReqMsgSignature)) {
            exit;
        }
        
        $aid = input('param.aid', 0, 'intval');
        if($aid < 0)   exit;
        
        // 是否是企业微信,true-企业微信; false-企业号
        //if(! defined('IS_QW'))    define('IS_QW', ($qid < 18) ? false : true);
        
		$agentInfo = AgentLogic::get($aid, 'token,encoding_aes_key');
		if(empty($agentInfo) || empty($agentInfo['token']) || empty($agentInfo['encoding_aes_key']))   exit;
        $this->wxcpt = new WXBizMsgCrypt($agentInfo['token'], $agentInfo['encoding_aes_key'], self::CORPID);

        // 微信接入验证
        $sReqEchoStr = input('param.echostr', '');
		if(! empty($sReqEchoStr)){
            // 需要返回的明文
            $sEchoStr = '';
            $errCode = $this->wxcpt->VerifyURL($this->sReqMsgSignature, $this->sReqTimestamp, $this->sReqNonce, $sReqEchoStr, $sEchoStr);
            if ($errCode == 0) {
				echo $sEchoStr;
            }else{
				trace('wxcall: VerifyURL ERR: '. $errCode, 'error');
            }
            
        	exit;
        }
        
        $sMsg = ''; // 解析之后的明文
        $errCode = $this->wxcpt->DecryptMsg($this->sReqMsgSignature, $this->sReqTimestamp, $this->sReqNonce, file_get_contents('php://input'), $sMsg);
        if($errCode == 0) {
            // 解密成功，sMsg即为xml格式的明文
			trace('wxcall: DecryptMsg data: '.$sMsg);
            $this->_deal($aid, $sMsg);
        } else {
			trace('wxcall: DecryptMsg ERR: '. $errCode, 'error');
            exit;
        }
    }
	
	/**
     * 事件处理函数，根据业务判断选择对应的类对象来处理
    */
    private function _deal($aid, $content) 
	{
        if(empty($content)) exit;
        
        $data = [];
        $tmpData = new \SimpleXMLElement($content);
		foreach($tmpData as $key => $value){
			$data[$key] = strval($value);
		}
        
        if(empty($data))    exit;
        
		$xmlStr = WxreplyLogic::reply($aid, $data);
        
        if(empty($xmlStr)) exit;
		trace('wxcall reply msg: '.$xmlStr);
        
        $sEncryptMsg = ""; // xml格式的密文
		$errCode = $this->wxcpt->EncryptMsg($xmlStr, $this->sReqTimeStamp, $this->sReqNonce, $sEncryptMsg);
		if ($errCode == 0) {
            echo $sEncryptMsg;
			exit;
		}else{
			trace('wxcall EncryptMsg ERR: '.$errCode, 'error');
		}
    }
}
