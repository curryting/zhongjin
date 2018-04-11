<?php
/**
 * 微信消息回调
 * by sherlock
 */

namespace app\common\logic;

use app\common\controller\Errcode;
use app\home\logic\CustomerLogic;

class WxreplyLogic{
	
	/**
	 * 相应微信消息
	 * @param int $aid		应用ID
	 * @param array $data	数据
	 */
	public static function reply($aid, $data)
	{
		 /**
		 * 通过微信事件来定位处理的插件
		 * event可能的值：
		 * subscribe : 关注公众号
		 * unsubscribe : 取消关注公众号
		 * scan : 扫描带参数二维码事件
		 * click : 自定义菜单事件
		 */

        $msgType = strtolower($data['MsgType']);
        switch($msgType){
            case 'event':
                $aid = intval($aid);
                $event = strtolower($data['Event']);
                switch($event){
                    case 'change_contact':  // 企业微信关注状态变更
                        if(isset($data['WxPlugin_Status'])){
                            $userid = strval($data['UserID']);
                            $fstatus = intval($data['WxPlugin_Status']);
                            if($fstatus === 1){
								FollowLogic::update($userid);
							}else{
								FollowLogic::setStatus($userid, 4);
                            }
                        }
						break;
                    case 'subscribe':
                        $userid = strval($data['FromUserName']);
                        FollowLogic::update($userid);
						break;
                    case 'unsubscribe':
						$userid = strval($data['FromUserName']);
                        FollowLogic::setStatus($userid, 4);
						break;
                    case 'location':
                    case 'view':
						break;
                    case 'click':
						//return $this->replyText('敬请期待！');
						break;
                }
            break;
            default:
                $aid = intval($aid);
                $aid >= 0 || exit;
                
                $msgType = strtolower($data['MsgType']);
                if($msgType == 'text') {
                    // 被动回复消息 暂时不需要
                }
            break;
        }

		return '';
	}
}
