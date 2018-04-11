<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

// 从$_SERVER['HTTP_HOST']中解析域名
if (!function_exists('httphost_get_domain'))
{
    function httphost_get_domain()
    {
        $http_host = strtolower($_SERVER['HTTP_HOST']);

        $pos = strpos($http_host, ':');
        (false !== $pos) && $http_host = substr($http_host, 0, $pos);

        $host_section_list = explode('.', $http_host);
        $host_section_list = array_reverse($host_section_list);
        if(count($host_section_list) <= 2)
        {
            $domain = array_reverse(array_slice($host_section_list, 0));
            return array($domain, array());
        }

        $domain = array_reverse(array_slice($host_section_list, 0, 2));
        $sub_domain = array_reverse(array_slice($host_section_list, 2));
        return array($domain, $sub_domain);
    }
}

// 递归查询父级
function contact_get_root($data_array, $id)
{
    if(! array_key_exists($id, $data_array))    return '';
    if($data_array[$id]['pid'] == 0)    return $id;

    return $id . ',' . contact_get_root($data_array, $data_array[$id]['pid']);
}

// 递归调用子部门
function contact_get_child($tree, $id)
{
    $id_str = '';
    foreach($tree as $key => $value){
        if($value['id'] == $id){
            return get_by_tree($tree[$key], $id);
        }

        if(isset($tree[$key]['_child'])){
            $id_str .= contact_get_child($tree[$key]['_child'], $id);
        }
    }

    return $id_str;
}

function get_by_tree($data_array, $id)
{
    $id_str = '';
    if(isset($data_array['_child'])){
        foreach($data_array['_child'] as $key => $value){
            $id_str .= empty($id_str) ? '' : ',';
            $id_str .= get_by_tree($value, $value['id']);
        }
    }

    return $id . (empty($id_str) ? '' : ",$id_str");
}

/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 */
function list_to_tree($list, $pk='id', $pid='pid', $child='_child', $root = 0)
{
	// 创建Tree
	$tree = array();
	if(is_array($list)){
		// 创建基于主键的数组引用
		$refer = array();
		foreach($list as $key => $data){
			$refer[$data[$pk]] = &$list[$key];
		}

		foreach($list as $key => $data){
			// 判断是否存在parent
			$parentId = $data[$pid];
			if($root == $parentId){
				$tree[] = &$list[$key];
			}else{
				if(isset($refer[$parentId])){
					$parent = &$refer[$parentId];
					$parent[$child][] = &$list[$key];
				}
			}
		}
	}

	return $tree;
}

/**
 *  把时间戳转化为对应的时间
 */
function format_pub_time($pub_time)
{
	$pub_time = intval($pub_time);

	$cur_time = time();
	$time_interval = $cur_time - $pub_time;

	if($time_interval < 0) return '未知';

	if($time_interval <= 120){
		return '刚刚';
	}elseif ($time_interval <= 3600){
		return ceil($time_interval / 60) . '分钟前';
	}else{
		if(date('Ymd', $cur_time) == date('Ymd', $pub_time)){
			return '今天 ' . date('H:i', $pub_time);
		}elseif(date('Ymd', $cur_time - 86400) == date('Ymd', $pub_time)){
			return '昨天 ' . date('H:i', $pub_time);
		}elseif(date('Ymd', $cur_time - 172800) == date('Ymd', $pub_time)){
			return '前天 ' . date('H:i', $pub_time);
		}else{
			return date('n月j日', $pub_time);
		}
	}
}

/** 生成临时文件 
 * @param string $filename 文件名
 * @param string $content 文件内容
 * @return string 返回文件的绝对路径
*/
function create_tmp_file($filename, $content)
{
    $dirname = APP_PATH . 'temp/';
    if(! is_dir($dirname)){
        if(! mkdir($dirname, 0777, true)){
            return '';
        }
    }

    $filename = $dirname. $filename;
    if(false === file_put_contents($filename, $content)){
        return '';
    }

    return $filename;
}

/**
 * 导出execl
 * @param type $data		数据
 * @param type $filename	文件名
 * @param type $sheet		
 */
function out_execl(&$data, $filename='', $sheet=false)
{
	require_once (APP_HTTP . 'common/common/download_xlsx.php');
	export($data, $filename, $sheet);
}

/**
*功能：php多种方式完美实现下载远程图片保存到本地
*参数：文件url,保存文件名称，使用的下载方式
*当保存文件名称为空时则使用远程文件原来的名称
*/
function get_remote_img($url,$filename='',$type=0)
{
    if($url==''){return false;}
    if($filename==''){
        $param_pos = strrpos($url, '?');
        $orignal_url = $param_pos === false ? $url : substr($url, 0, $param_pos);
        $filename = strrchr($orignal_url, '/');
    }
    //文件保存路径
    if($type){
        $ch=curl_init();
        $timeout=5;
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
        $img=curl_exec($ch);
        curl_close($ch);
    }else{
        ob_start();
        readfile($url);
        $img=ob_get_contents();
        ob_end_clean();
    }
    
    // 目录检测
    $dir = UPLOADS_PATH . 'xiumi';
    if(! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    
    // 写入文件内容
    $filename = $dir . $filename;
    $fp2=@fopen($filename,'w');
    fwrite($fp2,$img);
    fclose($fp2);
    return $filename;
}

/**
 * 替换秀米图片
 * @param type $content
 */
function deal_xiumi_image($content)
{
    $xiumiImgs = [];
    $localImgs = [];
    preg_replace_callback('/<img.*?src=["\'](.*?)["\']/is', function($matches) use(&$xiumiImgs, &$localImgs){
        if(!empty($matches[1]) && !in_array($matches[1], $xiumiImgs) && stripos($matches[1], '.xiumi.us') !== false){
            $localFile = get_remote_img($matches[1]);
			if(file_exists($localFile)){
				$pos = strpos($localFile, 'uploads');
				$localFile = APP_HTTP.TLD.'.'.WEB_DOMAIN.'/'.substr($localFile, $pos);
				$xiumiImgs[] = $matches[1];
                $localImgs[] = $localFile;
            }
        }
    }, $content);
    
    preg_replace_callback('/url\(&quot;(.*?)&quot;\);/is', function($matches) use(&$xiumiImgs, &$localImgs){
		if(!empty($matches[1]) && !in_array($matches[1], $xiumiImgs) && stripos($matches[1], '.xiumi.us') !== false){
            $localFile = get_remote_img($matches[1]);
            if(file_exists($localFile)){
				$pos = strpos($localFile, 'uploads');
				$localFile = APP_HTTP.TLD.'.'.WEB_DOMAIN.'/'.substr($localFile, $pos);
				$xiumiImgs[] = $matches[1];
                $localImgs[] = $localFile;
            }
       }
    }, $content);
    $content = str_replace($xiumiImgs, $localImgs, $content);
    return $content;
}

require_once (APP_PATH . 'common/common/httpcurl.php');
require_once (APP_PATH . 'common/common/common.php');