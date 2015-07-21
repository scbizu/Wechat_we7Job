<?php
/**
 * Job模块微站定义
 *
 * @author Hypernet
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class hypernet_iptjModuleSite extends WeModuleSite {

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
		global  $_W,$_GPC;
	}
	public function doWebPtjadmin() {
		//这个操作被定义用来呈现 管理中心导航菜单
		global $_W,$_GPC;
        $pinfo=$this->DboperateGetAllProfile();
        $root=$_W['siteroot'].'app/index.php?i=5&c=entry&do=ptjindex&m=hypernet_iptj';
	   if($_GPC['surebtn']){
	   	 $t=$this->DboperateAdminSurePro($_GPC['id']);
	   	 $info=pdo_fetch("SELECT * from".tablename('ptj_profile')."WHERE id=:i",array(':i'=>$_GPC['id']));
	   	 $this->SendTpl($info['openid'],$info,'auth');
	   	 $url=$this->createWebUrl('ptjadmin');
	   	 echo "<script language='javascript'>
	   	 location.href=\"$url\";
	   	 </script>";	   	 
	   }
		include $this->template('Admin');
	}
	public function doMobilePtjlink() {
		//这个操作被定义用来呈现 微站首页导航图标
	}
	public function doMobilePtjmyinfo() {
		//这个操作被定义用来呈现 微站个人中心导航
		
			global $_W,$_GPC;

			
		load()->model('mc');
        load()->func('tpl');
		$avatar = '';
		$openid=$_W['openid'];
		$count=0;
		
                $uid=$_W['member']['uid'];
//		$nickname=$_W['member']['nickname'];
		if (!empty($_W['member']['uid'])) {
			$member = mc_fetch(intval($_W['member']['uid']), array('avatar','nickname'));
//var_dump($member);
			if (!empty($member)) {
				$avatar = $member['avatar'];
                                $nickname=$member['nickname'];
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
                           var_dump($userinfo);
			if (!is_error($userinfo) && !empty($userinfo) && is_array($userinfo) && !empty($userinfo['avatar'])) {
				$avatar = $userinfo['avatar'];
                                 $nickname=$userinfo['nickname'];
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
			
			$this->DboperateInsertIntoProfile($openid, $name, $nickname,$phone, $sex, $location, $iden);
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
		$myworkerbtn=$_GPC['myworkerbtn'];
		if($myworkerbtn){
			$myworker=$this->DboperateGetworkerinfo($openid);
			if($_GPC['undowker']&&$_GPC['jobid']){
			
                 $undo=$this->DboperateUndoWanna($_GPC['jobid']);
                 $url=$this->createMobileUrl('ptjmyinfo',array('myworkerbtn'=>'on'));
                 echo "<script language='javascript'>
                 alert('哇!!你的一条招募令消失了~~');
                 </script>";
                 echo "<script language='javascript'>
                 location.href=\"$url\";
                 </script>";

			}
		}
		$workmanbtn=$_GPC['workmanbtn'];
		if($workmanbtn){
		   $workmaninfo=$this->DboperateFindWk($openid);	
		}
		
		$mypost=$_GPC['mypost'];
		   //post info
		$title=$profile['Worktitle'];  
		
		$content=$profile['Workdetail'];
		$salary=$profile['Worksalary'];
		$mount=$profile['Workmount'];
		$date=$profile['Workdate'];
		if($title AND $content AND $salary AND $mount AND $date){
			$jobid=substr(md5(substr(time(), 4,8)),4,9);
		  $this->DboperateInsertGroundInfo($openid,$jobid, $title, $content, $salary, $mount, $date);
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
                  		$this->SendTpl($openid,$Ownerinfo,'apply');
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
					alert('(～￣▽￣)～先去个人中心完善信息并等待管理员确认哦~');
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
	 * @param unknown $nickname
	 * @return Ambigous <boolean, unknown>
	 */
private function DboperateInsertIntoProfile($openid,$name,$nickname,$phone,$sex,$location,$iden){
	$T=pdo_insert('ptj_profile',array('openid'=>$openid,'name'=>$name,'nickname'=>$nickname,'phone'=>$phone,'location'=>$location,'identity'=>$iden,'count'=>0,'sex'=>$sex,'sure'=>'0'));
  	return $T;
}	
	
/**
 * 查找用户
 * @param unknown $openid
 * @return boolean
 */
 private function DboperateSearchUser($openid){
 	
 	$t=pdo_fetch("SELECT * FROM".tablename('ptj_profile')."WHERE openid=:oid AND sure=:su",array('oid'=>$openid,':su'=>1));
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
	private function DboperateInsertGroundInfo($openid,$jobid,$title,$content,$salary,$mount,$date){
		
		$t=pdo_insert('ptj_ground',array('title'=>$title,'jobid'=>$jobid,'content'=>$content,'salary'=>$salary,'mount'=>$mount,'date'=>$date,'openid'=>$openid,'visible'=>1));
		return $t;
	}
	/**
	 * 查看广场
	 * @return Ambigous <boolean, multitype:unknown >
	 */
private  function  DboperateSearchGroundinfo(){
	$ground=array();
	$Ground=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."WHERE visible=:vi",array(':vi'=>1),'');
	foreach ($Ground as $k=>$v){
		$this->DboperateCheckGroundTime($v['jobid'], $v['date'], date('y-m-d'));
		$exists=$this->DboperateGetIntworkers($v['jobid']);
		if($exists<$v['mount']){
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
	else{
		pdo_update('ptj_ground',array('visible'=>0),array('jobid'=>$v['jobid']));
	}
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
	
	$T=pdo_fetch("SELECT * FROM".tablename('ptj_profile')."WHERE openid=:oid AND sure=:su",array('oid'=>$openid,':su'=>1));
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
		$t=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."WHERE title REGEXP :ti AND WHERE visible=:vi ORDER BY salary DESC",array(':ti'=>$info,':vi'=>1),'');
	}
	else if($time){
		$t=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."WHERE title REGEXP :ti AND WHERE visible=:vi ORDER BY date ",array(':ti'=>$info,':vi'=>1),'');
	}
	else if($owner){
		$t=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."WHERE title REGEXP :ti AND WHERE visible=:vi ORDER BY openid DESC",array(':ti'=>$info,':vi'=>1),'');
	}
	else {
	    $t=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."WHERE title REGEXP :ti AND WHERE visible=:vi",array(':ti'=>$info,':vi'=>1),'');
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
	$t=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."WHERE visible=:vi ORDER BY salary DESC",array(':vi'=>1),'');
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
	$t=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."WHERE visible=:vi ORDER BY date ",array(':vi'=>1),'');
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
	$t=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."WHERE visible=:vi ORDER BY openid DESC",array(':vi'=>1),'');
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
 * 检查广场消息是否过期
 * @param unknown $jobid
 * @param unknown $date
 * @param unknown $now
 * @return unknown
 */
private function DboperateCheckGroundTime($jobid,$date,$now){
	if(strtotime($date)<=strtotime($now)){
		$t=pdo_update('ptj_ground',array('visible'=>0),array('jobid'=>$jobid));
	}
	return $t;
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
 * 拉取评论
 * @param unknown $jobid
 * @return Ambigous <boolean, multitype:unknown >
 */
private function Dboperateshowcomment($jobid){
     $info=pdo_fetchall("SELECT * FROM".tablename('ptj_comment')."WHERE jobid=:jid",array(':jid'=>$jobid),'');
     return $info; 	
}
/**
 * 获取招募信息
 * @param unknown $openid
 * @return Ambigous <boolean, multitype:unknown >
 */
private function DboperateGetworkerinfo($openid){
	$ground=array();
	$Ground=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."WHERE openid=:oid",array(':oid'=>$openid),'');
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
 * 撤销招募令
 * @param unknown $jobid
 * @return Ambigous <boolean, unknown>
 */
private function DboperateUndoWanna($jobid){
	$t=pdo_delete('ptj_working',array('jobid'=>$jobid));

	$t1=pdo_delete('ptj_ground',array('jobid'=>$jobid));

   return $t+$t1;
}
/**
 * 查看工人简历
 * @param unknown $ownerid
 * @return multitype:
 */
private function DboperateFindWk($ownerid){
	$Allarray=array();
	$info=pdo_fetchall("SELECT * FROM".tablename('ptj_working')."WHERE owneroid=:oid and sure=:su",array(':oid'=>$ownerid,':su'=>1),'');
	foreach ($info as $k=>$v){
		 $winfo=pdo_fetch("SELECT * FROM".tablename('ptj_profile')."WHERE openid=:oid",array(':oid'=>$v['workeroid']));
		 $speinfo=pdo_fetch("SELECT * FROM".tablename('ptj_ground')."WHERE jobid=:jid",array(':jid'=>$v['jobid']));
		 $wkinfo=array(
		 	'wkinfo'=>$speinfo['title'],
		 		'wkphone'=>$winfo['phone'],
		 		'wksex'=>$winfo['sex'],
		 		'wkname'=>$winfo['name'],
		 		'wklocation'=>$winfo['location']
		 );
		 array_push($Allarray, $wkinfo);
	}
	return $Allarray;
}
/**
 * 后台操作函数  获得所有人名单
 * @return unknown
 */
private function DboperateGetAllProfile(){
	$info=pdo_fetchall("SELECT * FROM".tablename('ptj_profile')."ORDER BY id DESC",array(),'');
	return  $info;
}
/**
 * 后台操作函数 管理员的确认profile函数
 * @param unknown $id
 * @return unknown
 */
private  function DboperateAdminSurePro($id){
	$t=pdo_update('ptj_profile',array('sure'=>1),array('id'=>$id));
	return $t;
}
/*
private function DboperateGetOinfo($oid){
	$info=pdo_fetch("SELECT * FROM".tablename('ptj_profile')."WHERE openid=:oid",array(':oid'=>$oid));
	return $info;
}
*/
//////////////////////////业务操作 分割线//////////////////////////
/**
 * 模板消息 demo   
 * @todo 接口未开放   TID DATA等请自行设定  
 * @param unknown $openid
 */
private function SendTpl($openid,$info,$type){
	load()->func('communication');
    global $_W;
	$access_token = WeAccount::token();
	$date=date('Y-m-d H:i');
	//post URL
	$url='https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$access_token;
	//POST data
	if($type=='apply'){
	$data=array(
		'touser'=>$openid,
		'template_id'=>'xYkOym8y5fCrgYlJUfRCGjK-CGscFsD9sihZy3kf27E',
		'url'=>$_W['siteroot'].'app/index.php?i=5&c=entry&do=ptjindex&m=hypernet_iptj',
		'topcolor'=>'#FF0000',
		'data'=>array(
		'first'=>array(
				'value'=>'报名成功!这是商家发来的贺电哦~~',
				'color'=>'#F8091D',
			),
	   'keynote1'=>array(
			'value'=>$info['name'].'欢迎你的加入~', 
	   		'color'=>'#F8091D',
		),
		'keynote2'=>array(
						'value'=>'即日起~~',
						'color'=>'#F8091D',
				),
	  'remark'=>array(
	   	'value'=>'这是我的电话:'.$info['phone'],'   欢迎咨询~',
	  	'color'=>'#F8091D',
	   ),			
	)			
	);
	}
	elseif ($type=='auth'){
		$data=array(
				'touser'=>$openid,
				'template_id'=>'GR8B0nCPus9hjnQajwuLMHr-AntvCSqZy_E5ErJh3P4',
				'url'=>$_W['siteroot'].'app/index.php?i=5&c=entry&do=ptjindex&m=hypernet_iptj',
				'topcolor'=>'#FF0000',
				'data'=>array(
						'first'=>array(
								'value'=>'一封来自老司机兼职平台的邀请函。。。。',
								'color'=>'#F8091D',
						),
						'keynote1'=>array(
								'value'=>$info['name'],
								'color'=>'#0542FA',
						),
						'keynote2'=>array(
								'value'=>$info['phone'],
								'color'=>'#0542FA',
						),
						'keynote3'=>array(
								'value'=>$date,
								'color'=>'#0542FA',
						),
						'remark'=>array(
								'value'=>'老司机兼职平台的大门永远为充满生活热情的你打开~~',
								'color'=>'#0542FA',
						),
				)
		);
	}
   ihttp_post($url,json_encode($data));
}
}
