<?php
namespace app\weixin\controller;

use app\common\controller\Errcode;
use app\weixin\logic\NewsLogic;

class News extends Root
{
	/**
	 * 列表
	 */
	public function lists()
	{
		$type = input('param.type', 0, 'intval');
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = NewsLogic::lists($type, $offset, $psize, intval(MEMID));
		
		return $this->resultOk(['list'=>$info]);
	}
	
	/**
	 * 详情
	 */
	public function detail()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		try{
			$info = NewsLogic::detail($id);
		}catch(\Exception $e){
			$this->result('', $e->getCode(), $e->getMessage());
		}
		
		return $this->resultOk(['detail'=>$info]);
	}
	
	/**
	 * 预览详情
	 */
	public function previewDetail()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$info = NewsLogic::previewDetail($id);
		return $this->resultOk(['detail'=>$info]);
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
		
		$info = NewsLogic::readCommentReply($id, 0, $offset, $psize, intval(MEMID));
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
	 * 评论
	 */
	public function comment()
	{
		$id = input('param.news_id', 0, 'intval');	//	表示新闻的ID
		$content = input('param.content', '', 'htmlspecialchars');
		($id <=0 || empty($content)) && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$res = NewsLogic::comment($id, $content, intval(MEMID), intval(ADMINID));
		$res === false && $this->result('', Errcode::INSERT_ERR, '评论失败');
		return $this->resultOk(['id'=>$res]);
	}
	
	/**
	 * 回复
	 */
	public function reply()
	{
		$newsId = input('param.news_id', 0, 'intval');	//	表示新闻的ID
		$content = input('param.content', '', 'htmlspecialchars');
		$commentId = input('param.comment_id', 0, 'intval');	//	表示评论的ID
		$toMemid = input('param.to_memid', 0, 'intval');		// 如果是回复客户的时候参数
		$toAdminid = input('param.to_adminid', 0, 'intval');	// 如果是回复管理员的时候参数
		($newsId <=0 || empty($content)) && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$res = NewsLogic::comment($newsId, $content, intval(MEMID), intval(ADMINID), 
			1, $commentId, $toMemid, $toAdminid);
		$res === false && $this->result('', Errcode::INSERT_ERR, '回复失败');
		return $this->resultOk(['id'=>$res]);
	}
}
