<?php
/**
 * Job模块微站定义
 *
 * @author Hypernet
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class Hypernet_PTJModuleSite extends WeModuleSite {

	public function doMobilePtjindex() {
		//这个操作被定义用来呈现 功能封面
		global  $_W,$_GPC;
		
		include $this->template('index');
	}
	public function doWebPtjrules() {
		//这个操作被定义用来呈现 规则列表
	}
	public function doWebPtjadmin() {
		//这个操作被定义用来呈现 管理中心导航菜单
	}
	public function doMobilePtjlink() {
		//这个操作被定义用来呈现 微站首页导航图标
	}
	public function doMobilePtjmyinfo() {
		//这个操作被定义用来呈现 微站个人中心导航
			global $_W, $_GPC;
		

		
		load()->model('mc');
		$avatar = '';
		$openid=$_W['openid'];
		$count=0;
		
		
		if (!empty($_W['member']['uid'])) {
			$member = mc_fetch(intval($_W['member']['uid']), array('avatar'));
			if (!empty($member)) {
				$avatar = $member['avatar'];
			}
		}
		if (empty($avatar)) {
			$fan = mc_fansinfo($_W['openid']);
			if (!empty($fan)) {
				$avatar = $fan['avatar'];
			}
		}
		if (empty($avatar)) {
			$userinfo = mc_oauth_userinfo();
			if (!is_error($userinfo) && !empty($userinfo) && is_array($userinfo) && !empty($userinfo['avatar'])) {
				$avatar = $userinfo['avatar'];
			}
		}
		if (empty($avatar) && !empty($_W['member']['uid'])) {
			$avatar = mc_require($_W['member']['uid'], array('avatar'));
		}
		if (empty($avatar)) {
			// 提示用户关注公众号。;
			echo "最终没有获取到头像,follow: {$_W['fans']['follow']}";
		} else {
			$src=$avatar;
	//	echo <<<IMG
    //  <img src="$avatar" style="width:20%;height:10%;margin-top:16%">
//IMG;

		}
		
		include $this->template('myinfo');
	}
	
	public function doMobilePtjdetails(){
		
		global $_W,$_GPC;
		
		include $this->template('details');
	}
	
	
	public function doMobilePtjorders() {
		//这个操作被定义用来呈现 微站快捷功能导航
	}

}