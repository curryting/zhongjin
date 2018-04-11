<?php
/**
 * 文件处理
 * by sherlock
 */

namespace app\common\logic;

use app\common\model\File;
use app\common\controller\Errcode;

class FileLogic{
	
	/**
	 * 上传文件
	 * @param string $files		要上传的文件列表（通常是$_FILES数组）
	 * @param array  $validate	验证字段
	 */
	public static function uploadMore($files, $validate=[])
	{
		$files = request()->file($files);
		$model = new File();
		$data = [];
		
		foreach($files as $k => $file){
			// 移动到框架应用根目录/public/uploads/ 目录下
			$info = $file->validate($validate)->move(UPLOADS_PATH);
			if($info){
				$id = $model->addOne([
					'name' => $info->getFilename(),
					'path' => $info->getSaveName()
				]);
				
				if($id <=0){
					// 添加数据失败
					unset($files[$k]);
					trace('添加文件失败', 'error');
					//throw new \Exception('添加文件失败', Errcode::INSERT_ERR);
				}else{
					$data[$id] = $info->getFilename();
				}
			}else{
				// 上传失败获取错误信息
				throw new \Exception($file->getError(), Errcode::FILE_ERR);
			}
		}
		
		return $data;
	}
	
	/**
	 * 上传单个文件
	 * @param string $files		要上传的文件列表（通常是$_FILES数组）
	 * @param array  $validate	验证字段
	 */
	public static function uploadOne($files, $validate=[])
	{
		$file = request()->file($files);
		
		if($file){
			$info = $file->validate($validate)->move(UPLOADS_PATH);
			if($info){
				$model = new File();
				
				// 查找是否已经上传过相同文件
				$saveName = $info->getSaveName();
				$md5File  = md5_file(UPLOADS_PATH.$saveName);
				$sha1File = sha1_file(UPLOADS_PATH.$saveName);
				$fileInfo = $model->getOne([
					'md5' => $md5File, 'sha1' => $sha1File
				], 'id');
				
				if(empty($fileInfo)){
					$id = $model->addOne([
						'md5'	=>	$md5File,
						'sha1'	=> $sha1File,
						'name' => $info->getFilename(),
						'path' => UPLOADS_PATH.$saveName,
						'ext'	=> $info->getExtension(),
						'http_path'   => '/uploads/'.$saveName,
						'origin_name' => $info->getOriginFileName()
					]);

					if($id <=0){
						// 添加数据失败
						unset($files[$k]);
						throw new \Exception('添加文件失败', Errcode::INSERT_ERR);
					}
				}else{
					$id = $fileInfo['id'];
				}
				return $id;
			}else{
				// 上传失败获取错误信息
				throw new \Exception($file->getError(), Errcode::FILE_ERR);
			}
		}
		
	}
	
	/**
	 * 读取单个文件信息
	 * @param int $id			文件ID
	 * @param bool $originName	需要读文件原始名
	 * @param bool $httpPath	需要读取url路径
	 * @param bool $ext			需要读取后缀名
	 */
	public static function getOne($id, $originName=true, $httpPath=true, $ext=true)
	{
		$model = new File();
		
		$field = [];
		$ext && $field[] = 'ext';
		$httpPath && $field[] = 'http_path';
		$originName && $field[] = 'origin_name';
		
		if(empty($filed)){
			$filed = '*';
		}else{
			$filed = implode(',', $filed);
		}
		
		$info = $model->getOne(['id'=>$id], $field);
		return $info;
	}
	
	/**
	 * 读取多个文件信息
	 * @param array $ids		文件IDs
	 * @param bool $originName	需要读文件原始名
	 * @param bool $httpPath	需要读取url路径
	 * @param bool $ext			需要读取后缀名
	 */
	public static function getMore($ids, $originName=true, $httpPath=true, $ext=true)
	{
		$model = new File();
		
		$field = ['id'];
		$ext && $field[] = 'ext';
		$httpPath && $field[] = 'http_path';
		$originName && $field[] = 'origin_name';
		
		$field = implode(',', $field);
		$list = [];
		$_list = $model->getMore(['id'=>['in', $ids]], $field);
		foreach($_list as $v){
			$list[$v['id']] = $v;
		}
		unset($_list);
		
		return $list;
	}
}
