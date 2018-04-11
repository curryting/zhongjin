<?php
namespace app\common\controller;

class Errcode{
	const SUCCESS			= 200; // 成功
	const PARAM_ERR		= 401; // 参数错误
	const LOGIN_INVALID	= 402; // 登录失效
	const AUTH_ERR		= 403; // 认证失败
	const USER_UNSET		= 404; // 用户不存在
	const CAPTCHA_FAILE	= 405; // 登录验证码验证失败
	const USER_PWD_ERR	= 406; // 用户名或密码错误
	const PWD_CONFIRM_ERR	= 407; // 密码不一致
	const OLD_NEW_EQUALLY	= 408; // 新密码和旧密码一致
	const USER_ISSET			= 409; // 用户已存在
	const AMDIN_NOT_ALLOW	= 410; // 管理员不允许操作
	const NO_AUTH				= 411; // 无权限
	const ARCHITECTRUE		= 412; // 选择发送对象为空
	
	const PHONR_CODE_ERR			= 600; // 获取手机验证码失败
	const PHONR_CODE_INVALID	= 601; // 手机验证码失效
	
	// 模型状态码
	const MODEL_PARAM_ERR	= 801; // 参数错误
	const INSERT_ERR			= 802; // 增加失败
	const UPDATE_ERR			= 803; // 更新失败
	const SELECT_UNSET		= 804; // 读取的数据不存在
	
	// 文件状态码
	const FILE_ERR			= 901; // 上传文件失败
	const FILE_NO_EXISET		= 902; // 上传不存在
	const FILE_DATA_BIG		= 903; // 上传数据太大
	const FILE_DATA_ERR		= 904; // 上传数据错误
	const CREATE_FILE_FAILE		= 905; // 创建文件失败

	// 产品状态码
	const PRODUCT_ISSET			= 1000; // 用户已存在

	// 通知状态码
	const NOTICE_UNSET			= 1100; // 通知不存在
	const NOTICE_DEL				= 1101; // 通知已删除
	const NOTICE_SEND			= 1102; // 通知已发送
	
	// 调研状态码
	const SURVEY_UNSET			= 1200; // 调研不存在
	const SURVEY_DEL				= 1201; // 调研已删除
	const SURVEY_PUBLISH		= 1202; // 调研已发布
	const SURVEY_QUE_UNSET		= 1203; // 调研问题不存在
	const SURVEY_ISSET_ANSWER	= 1204; // 已经参与过调研
	
	// 新闻状态码
	const NEWS_UNSET			= 1300; // 新闻不存在
	const NEWS_DEL			= 1301; // 新闻已删除
	const NEWS_PUBLISH		= 1302; // 新闻已发布
	
	// 投票状态码
	const VOTE_UNSET			= 1400; // 投票不存在
	const VOTE_DEL			= 1401; // 投票已删除
	const VOTE_PUBLISH		= 1402; // 投票已发布
	const VOTE_QUE_UNSET		= 1403; // 投票问题不存在
	const VOTE_SETUP_UNSET	= 1404; // 投票功能设置不存在
	const VOTE_UPPER_LIMIT	= 1405; // 投票超过上限
	const VOTE_NO_BEGINNING	= 1406; // 投票尚未开始
	const VOTE_OVER			= 1407; // 投票已结束
	const VOTE_SUSPEND		= 1408; // 投票已暂停
	
	// execl处理码
	const EXECL_COL_TOLONG		= 1501; // 列太多
	// 微信端登录
	const INVALID_ACCESS_TOKEN		= 2000; // 无效的accsstoken
	
	// 企业微信api报错
	const WEIXIN_API_ERR				= 3000; // 企业微信api报错
	
}

