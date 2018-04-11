<?php
namespace app\home\controller;

use app\common\controller\Errcode;
use app\home\logic\ArchitectureLogic;
use app\home\logic\CustomerLogic;
use app\home\logic\NewsLogic;
use app\common\logic\FileLogic;

class News extends Root
{
	/**
	 * 增加
	 */
	public function add()
	{
		$list = $this->_getAddParam();
		
		$memids = [];
		$sendType = input('param.send_type', 0, 'intval');
		if($sendType == 1){
			// 发给指定成员
			$sendObj = input('param.send_obj/a');
			empty($sendObj) && $this->result('', Errcode::PARAM_ERR, '选择成员不能为空');
			$memids = ArchitectureLogic::parseToMember($sendObj);
		}else{
			$info = CustomerLogic::getName();
			foreach($info as $v){
				$memids[] = $v['id'];
			}
			unset($info);
		}
		$memids = array_values(array_filter(array_unique($memids)));
		empty($memids) && $this->result('', Errcode::PARAM_ERR, '成员不能为空');
		
		$res = NewsLogic::add($list, $memids);
		$res === false && $this->result('', Errcode::INSERT_ERR, '增加失败');
	
		return $this->resultOk();
	}
	
	/**
	 * 列表
	 */
	public function lists()
	{
		$keywords = input('param.search', '', 'htmlspecialchars');
		$type = input('param.news_type', 0, 'intval');
		in_array($type, [0,1,2,3]) || $this->result('', Errcode::PARAM_ERR, '客户类别错误');
		
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = NewsLogic::lists($keywords, $type, $offset, $psize);
		return $this->resultOk(['list'=>$info[0], 'total'=>$info[1]]);
	}
	
	/**
	 * 删除
	 */
	public function delete()
	{
		$id = input('param.id', 0, 'intval');
		$id<=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$res = NewsLogic::del($id);
		$res === false && $this->result('', Errcode::UPDATE_ERR, '删除失败');
		
		return $this->resultOk();
	}
	
	/**
	 * 详情
	 */
	public function detail()
	{
		$id = input('param.id', 0, 'intval');
		$id<=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		try{
			$info = NewsLogic::detail($id);
		} catch (\Exception $e) {
			$this->result('', $e->getCode(), $e->getMessage());
		}
		
		return $this->resultOk(['detail'=>$info]);
	}
	
	/**
	 * 获取已读名字
	 */
	public function readMember()
	{
		$id = input('param.id', 0, 'intval');
		$id<=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$info = NewsLogic::member($id, 1);
		return $this->resultOk(['member'=>$info[0]]);
	}
	
	/**
	 * 获取未读名字
	 */
	public function unreadMember()
	{
		$id = input('param.id', 0, 'intval');
		$id<=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$info = NewsLogic::member($id, 0);
		return $this->resultOk(['member'=>$info[0], 'can_remind'=>$info[1]]);
	}
	
	/**
	 * 未读提醒
	 */
	public function remind()
	{
		$id = input('param.id', 0, 'intval');
		$id<=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		NewsLogic::remind($id);
		return $this->resultOk();
	}
	
	/**
	 * 预览
	 */
	public function preview()
	{
		$list = $this->_getAddParam();
		
		$res = NewsLogic::preview($list);
		$res === false && $this->result('', Errcode::INSERT_ERR, '发送失败');
	
		return $this->resultOk();
	}
	
	/**
	 * 读取评论
	 */
	public function readComment()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = NewsLogic::readCommentReply($id, 0, $offset, $psize);
		return $this->resultOk(['list'=>$info]);
	}
	
	/**
	 * 读取回复
	 */
	public function readReply()
	{
		$id = input('param.id', 0, 'intval');	//	表示评论的ID
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = NewsLogic::readCommentReply($id, 1, $offset, $psize);
		return $this->resultOk(['list'=>$info]);
	}
	
	/**
	 * 上传客户文件 
	 */
	public function upload()
	{
		try {
			$data = FileLogic::uploadOne('file', ['size'=>1024*1024*5, 'ext'=>'png,gif,jpg,jpeg']);
		} catch (\Exception $e) {
			return $this->resultErr($e->getMessage(), $e->getCode());
		}
		
		return $this->resultOk($data);
	}
	
	/**
	 * 获取增加时参数
	 * @return type
	 */
	private function _getAddParam()
	{
		$list = input('param.list/a');
		empty($list) && $this->result('', Errcode::PARAM_ERR, '参数错误');
		count($list) > 6 && $this->result('', Errcode::PARAM_ERR, '不能超过6条');
		
		foreach($list as $k => &$v){
			in_array($v['news_type'], [1,2,3]) || $this->result('', Errcode::PARAM_ERR, '第'.($k+1).'条新闻类型错误');
			if(isset($v['sync'])){
				in_array($v['sync'], [0,1]) || $this->result('', Errcode::PARAM_ERR, '第'.($k+1).'条同步到公众号参数类型错误');
			}else{
				$v['sync'] = 0;
			}
			
			$v['cover_id'] >=0 || $this->result('', Errcode::PARAM_ERR, '第'.($k+1).'条新闻上传图片为空');
			
			$v['title'] = htmlspecialchars($v['title']);
			empty($v['title']) && $this->result('', Errcode::PARAM_ERR, '第'.($k+1).'条新闻标题为空');
			mb_strlen($v['title'],'UTF-8') >32 && $this->result('', Errcode::PARAM_ERR, '第'.($k+1).'条新闻标题不能超过32个字符');
			
			$v['content'] = deal_xiumi_image($v['content']);
			$v['content'] = htmlspecialchars($v['content']);
			empty($v['content']) && $this->result('', Errcode::PARAM_ERR, '第'.($k+1).'条新闻内容为空');
			
			$v['abstract'] = htmlspecialchars($v['abstract']);
		}
		unset($v);
		
		return $list;
	}
}
