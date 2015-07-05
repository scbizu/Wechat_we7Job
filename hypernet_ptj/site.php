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

		//查看广场
		$ground=$this->DboperateSearchGroundinfo();
	
		
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
		}
		//if user
		$profile=$_GPC['Ptjmyinfo'];
		$name=$profile['Name'];
		$phone=$profile['Phone'];
		$location=$profile['Location'];
		$sex=$profile['Sex'];
		$iden=$profile['identity'];
		if($name AND $phone AND $location AND $sex AND $iden){
			$this->DboperateInsertIntoProfile($openid, $name, $phone, $sex, $location, $iden);
			$url=$this->createMobileUrl('ptjmyinfo');
			echo "<script language='javascript'>
					location.href=\"$url\";
					</script>";
		}
		$user=$this->DboperateSearchUser($openid);
		$isworker=$user['identity']=='worker'?1:0;
		//YES 兼职
		
		
		
		//NO 商家
		 $title=$profile['Worktitle'];  
		$content=$profile['Workdetail'];
		$salary=$profile['Worksalary'];
		$mount=$profile['Workmount'];
		$date=$profile['Workdate'];
		if($title AND $content AND $salary AND $mount AND $date){
		  $this->DboperateInsertGroundInfo($openid, $title, $content, $salary, $mount, $date);
		  $url=$this->createMobileUrl('ptjmyinfo');
		  echo "<script language='javascript'>
		  location.href=\"$url\";
		  </script>";
		}
		include $this->template('myinfo');
	}
	
	public function doMobilePtjdetails(){
		
		global $_W,$_GPC;
		
		$openid=$_W['openid'];
		
		$ground=$_GPC['ground'];
		
		$Ownerinfo=$this->DboperateSearchUser($openid);
		
		include $this->template('details');
	}
	
	
	public function doMobilePtjorders() {
		//这个操作被定义用来呈现 微站快捷功能导航
	}
	/**
	 * 简历提交
	 * @param unknown $openid
	 * @param unknown $name
	 * @param unknown $phone
	 * @param unknown $sex
	 * @param unknown $location
	 * @param unknown $iden
	 * @return Ambigous <boolean, unknown>
	 */
private function DboperateInsertIntoProfile($openid,$name,$phone,$sex,$location,$iden){
	$T=pdo_insert('ptj_profile',array('openid'=>$openid,'name'=>$name,'phone'=>$phone,'location'=>$location,'identity'=>$iden,'count'=>0));
  	return $T;
	
}	
	
/**
 * 查找用户
 * @param unknown $openid
 * @return boolean
 */
 private function DboperateSearchUser($openid){
 	
 	$t=pdo_fetch("SELECT * FROM".tablename('ptj_profile')."WHERE openid=:oid",array('oid'=>$openid));
 	return $t;
 }	
	/**
	 * 提交需求
	 * @param unknown $openid
	 * @param unknown $title
	 * @param unknown $content
	 * @param unknown $salary
	 * @param unknown $mount
	 * @param unknown $date
	 * @return Ambigous <boolean, unknown>
	 */
	private function DboperateInsertGroundInfo($openid,$title,$content,$salary,$mount,$date){
		
		$t=pdo_insert('ptj_ground',array('title'=>$title,'content'=>$content,'salary'=>$salary,'mount'=>$mount,'date'=>$date));
		return $t;
	}
	/**
	 * 查看广场
	 * @return Ambigous <boolean, multitype:unknown >
	 */
private  function  DboperateSearchGroundinfo(){
	
	$ground=pdo_fetchall("SELECT * FROM".tablename('ptj_ground'),array(),'');
	return $ground;
}	

	
	
	
	
	
	
	
	
	
	
	
}