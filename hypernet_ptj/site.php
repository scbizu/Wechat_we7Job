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
		if($_GPC['salarybtn']){
			$ground=$this->DboperateSorybysalary();
		}
		else if($_GPC['timebtn']){
			$ground=$this->DboperateSortbytime();
		}
		else if($_GPC['ownerbtn']){
			$ground=$this->DboperateSortbyownerid();
		}
		else {
			$ground=$this->DboperateSearchGroundinfo();
		
		}

		$searchinfo=$_GPC['Ptjindex']['Search'];
        if($searchinfo){
        	if($_GPC['salarybtn']){
		         $searchinfo=$this->DboperateSearchGround($searchinfo,1,0,0);
	}
	        else if($_GPC['timebtn']){
	           	$searchinfo=$this->DboperateSearchGround($searchinfo,FALSE,TRUE,FALSE);
	}
	        else if($_GPC['ownerbtn']){
	          	$searchinfo=$this->DboperateSearchGround($searchinfo,FALSE,FALSE,TRUE);
	}
	   else {
	     	$searchinfo=$this->DboperateSearchGround($searchinfo);
        }
        }


		
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
		
		
///////////////////////////////调用 mc  结束///////////////////////////////////		
		
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
		    //更新信息
		//获取当前时间
		$now=date("y-m-d");
		    $work=$this->DboperationSearchMyworkEx($openid);
		    foreach ($work as $k=>$v){	    	
		    	$this->Dboperatechecktime($v['jobid'], $now, $openid);
		    	//准备接收评论
		    	$comment=$profile['comm'];
		    	if($comment AND $v['status']){
		    		$this->Dboperatepushcomment($comment, $v['jobid'], $openid);
		    	
		    	}
		    }

		   $workerEx=$this->DboperationSearchMyworkEx($openid);
		

		   
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
		//获取商家的信息
		$oid=$ground['openid'];
		$Ownerinfo=$this->DboperateSearchUser($oid);
           
		$bmbutton=$_GPC['button'];
		//按钮按下 并且不是游客
			if($bmbutton=='on' AND $this->DboperateCheckUser($openid)){
                 $arr=$this->DboperateGetjob($openid, $oid);
                 if($oid!=$openid){
                  	if($arr AND in_array($ground['jobid'], $arr)){
                  		echo "<script language='javascript'>
					alert('o(*^＠^*)o你报过名了哦~');
					</script>";
                  	}
                  	else{
                  		$this->DboperateSurepostinfo($oid, $openid, $ground['jobid'], $ground['mount']);
                  		echo "<script language='javascript'>
					alert('O(∩_∩)O~~报名成功了!');
					</script>";
                  	}
                 }
                 else{
                 	echo "<script language='javascript'>
					alert('ლ(╹◡╹ლ)商家号不能参与报名哦~');
					</script>";
                 }
			}
			else if ($bmbutton=='on' AND !$this->DboperateCheckUser($openid)){
				echo "<script language='javascript'>
					alert('(～￣▽￣)～先去个人中心完善信息哦!');
					</script>";
			}
			$info=$this->Dboperateshowcomment($ground['jobid']);
			
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
		
		$t=pdo_insert('ptj_ground',array('title'=>$title,'content'=>$content,'salary'=>$salary,'mount'=>$mount,'date'=>$date,'openid'=>$openid));
		return $t;
	}
	/**
	 * 查看广场
	 * @return Ambigous <boolean, multitype:unknown >
	 */
private  function  DboperateSearchGroundinfo(){
	$ground=array();
	$Ground=pdo_fetchall("SELECT * FROM".tablename('ptj_ground'),array(),'');
	foreach ($Ground as $k=>$v){
		$exists=$this->DboperateGetIntworkers($v['jobid']);
		$arr=array(
			   'exists'=>$exists,
				'jobid'=>$v['jobid'],
				'title'=>$v['title'],
				'content'=>$v['content'],
				'mount'=>$v['mount'],
				'salary'=>$v['salary'],
				'date'=>$v['date'],
				'openid'=>$v['openid'],
		);
		array_push($ground, $arr);
	}
	return $ground;
}	
/**
 * 报名
 * @param unknown $owneroid
 * @param unknown $workoid
 * @param unknown $jobid
 * @param unknown $hadmount
 * @return Ambigous <boolean, unknown>
 */
private function DboperateSurepostinfo($owneroid,$workoid,$jobid,$hadmount){
	
	$t=pdo_insert('ptj_working',array('owneroid'=>$owneroid,'workeroid'=>$workoid,'jobid'=>$jobid,'hadmount'=>$hadmount,'visible'=>1,'sure'=>1));
	return $t;	
}
/**
 * 规定权限
 * @param unknown $openid
 * @return boolean
 */	
private function DboperateCheckUser($openid){
	
	$T=pdo_fetch("SELECT * FROM".tablename('ptj_profile')."WHERE openid=:oid",array('oid'=>$openid));
	return $T;
}	
/**
 * 查找jobid
 * @param unknown $wopenid
 * @param unknown $oopenid
 * @return multitype:
 */	
private function DboperateGetjob($wopenid,$oopenid){
	//取 全部该用户和一商家的全部的正在活跃的联系
	$arr=array();
	$t=pdo_fetchall("SELECT jobid FROM".tablename('ptj_working')."WHERE workeroid=:oid and owneroid=:ooid and sure=:su",array(':oid'=>$wopenid,':ooid'=>$oopenid,':su'=>1),'');
	foreach ($t as $k=>$v){
		array_push($arr, $v['jobid']);
	}
	return $arr;
}
/**
 * 搜索广场
 * @param string $info
 * @return Ambigous <boolean, multitype:unknown >
 */	
private function  DboperateSearchGround($info,$salary,$time,$owner){
	$ground=array();
	if($salary){
		$t=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."WHERE title REGEXP :ti ORDER BY salary DESC",array(':ti'=>$info),'');
	}
	else if($time){
		$t=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."WHERE title REGEXP :ti ORDER BY date ",array(':ti'=>$info),'');
	}
	else if($owner){
		$t=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."WHERE title REGEXP :ti ORDER BY openid DESC",array(':ti'=>$info),'');
	}
	else {
	    $t=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."WHERE title REGEXP :ti",array(':ti'=>$info),'');
	}
	foreach ($t as $k=>$v){
		$exists=$this->DboperateGetIntworkers($v['jobid']);
		$arr=array(
				'exists'=>$exists,
				'jobid'=>$v['jobid'],
				'title'=>$v['title'],
				'content'=>$v['content'],
				'mount'=>$v['mount'],
				'salary'=>$v['salary'],
				'date'=>$v['date'],
				'openid'=>$v['openid'],
		);
		array_push($ground, $arr);
	}
	return $ground;
}
/**
 * 查找工作经历
 * @param unknown $workeroid
 * @return multitype:
 */
private function  DboperationSearchMyworkEx($workeroid){
	$arr=array();
	$T=pdo_fetchall("SELECT jobid,sure FROM".tablename('ptj_working')."WHERE workeroid=:woid",array(':woid'=>$workeroid));
	//两表联查  重组数组
    foreach($T as $k=>$v){
    	$jobid=$v['jobid'];
    	$T1=pdo_fetch("SELECT * FROM".tablename('ptj_ground')."WHERE jobid=:jid",array(':jid'=>$jobid));
    	//结构化array
        $Arr=array(
        	'jobid'=>$jobid,
        		'title'=>$T1['title'],
        		'content'=>$T1['content'],
        		'status'=>$v['sure'],
        );
        array_push($arr, $Arr);
    }
    return $arr;
}	
/**
 * 现有工人数
 * @param unknown $jobid
 * @return boolean
 */	
private  function  DboperateGetIntworkers($jobid){
	$C=pdo_fetch("SELECT COUNT(*) AS count FROM".tablename('ptj_working')."WHERE jobid=:jid",array(':jid'=>$jobid));
	return $C['count'];
}	
/**
 * 广场的薪资排序
 * @return Ambigous <boolean, multitype:unknown >
 */
private function DboperateSorybysalary(){
	$ground=array();
	$t=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."ORDER BY salary DESC",array(),'');
	foreach ($t as $k=>$v){
		$exists=$this->DboperateGetIntworkers($v['jobid']);
		$arr=array(
			   'exists'=>$exists,
				'jobid'=>$v['jobid'],
				'title'=>$v['title'],
				'content'=>$v['content'],
				'mount'=>$v['mount'],
				'salary'=>$v['salary'],
				'date'=>$v['date'],
				'openid'=>$v['openid'],
		);
		array_push($ground, $arr);
	}
	return $ground;
}
/**
 * 广场的时间排序
 * @return Ambigous <boolean, multitype:unknown >
 */
private  function  DboperateSortbytime(){
	$ground=array();
	$t=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."ORDER BY date ",array(),'');
	foreach ($t as $k=>$v){
		$exists=$this->DboperateGetIntworkers($v['jobid']);
		$arr=array(
				'exists'=>$exists,
				'jobid'=>$v['jobid'],
				'title'=>$v['title'],
				'content'=>$v['content'],
				'mount'=>$v['mount'],
				'salary'=>$v['salary'],
				'date'=>$v['date'],
				'openid'=>$v['openid'],
		);
		array_push($ground, $arr);
	}
	return $ground;
}
/**
 * 广场的商家排序
 * @return Ambigous <boolean, multitype:unknown >
 */
private function DboperateSortbyownerid(){
	$ground=array();
	$t=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."ORDER BY openid DESC",array(),'');
	foreach ($t as $k=>$v){
		$exists=$this->DboperateGetIntworkers($v['jobid']);
		$arr=array(
				'exists'=>$exists,
				'jobid'=>$v['jobid'],
				'title'=>$v['title'],
				'content'=>$v['content'],
				'mount'=>$v['mount'],
				'salary'=>$v['salary'],
				'date'=>$v['date'],
				'openid'=>$v['openid'],
		);
		array_push($ground, $arr);
	}
	return $ground;
}
/**
 * 检查合同是否到期
 * @param unknown $jobid
 * @param unknown $now
 * @param unknown $openid
 * @return Ambigous <boolean, unknown>
 */
private function Dboperatechecktime($jobid,$now,$openid){
	$jobtime=pdo_fetch("select date FROM".tablename('ptj_ground')."WHERE jobid=:jid",array(':jid'=>$jobid));
	if(strtotime($jobtime['date'])<=strtotime($now)){
		$i=pdo_update('ptj_working',array('sure'=>0,'visible'=>0),array('workeroid'=>$openid,'jobid'=>$jobid));	
	}
	return $i;
}
/**
 * 提交评论
 * @param unknown $comment
 * @param unknown $jobid
 * @param unknown $workeroid
 * @return Ambigous <boolean, unknown>
 */
private function Dboperatepushcomment($comment,$jobid,$workeroid){
	$T=pdo_insert('ptj_comment',array('jobid'=>$jobid,'comment'=>$comment,'workerid'=>$workeroid));
	return $T;
}
/**
 * 拉去评论
 * @param unknown $jobid
 * @return Ambigous <boolean, multitype:unknown >
 */
private function Dboperateshowcomment($jobid){
     $info=pdo_fetchall("SELECT * FROM".tablename('ptj_comment')."WHERE jobid=:jid",array(':jid'=>$jobid),'');
     return $info; 	
}

}