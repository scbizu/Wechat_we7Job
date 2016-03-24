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
		require_once('config.php');
		require_once('template_conf_bottom.php');
		$share = json_decode($share_config,true);
		$bottom=json_decode($bottom,true);
		load()->model('mc');
		$avatar='';
		$date=date('Y-m-d');
		$farr=array();
		$modelres=pdo_fetchall("SELECT * FROM".tablename('ptj_model')."WHERE vis=:so",array(':so'=>'on'));
		
		
		
		
		$user=$this->DboperateSearchUser($_W['openid']);
		
		if (empty($avatar)) {
			$userinfo = mc_oauth_userinfo();
			if (!is_error($userinfo) && !empty($userinfo) && is_array($userinfo) && !empty($userinfo['avatar'])) {
				$avatar = $userinfo['avatar'];
				$nickname=$userinfo['nickname'];
			}
		}
		if (empty($avatar)) {
			// 提示用户关注公众号。;
		//	echo "请先关注该公众号";
		} else {
		$src=$avatar;
		}
	
		
		
		//查看广场
        if($_GPC['typecode'] AND !$_GPC['Search']){
        	$tprivacy=$_GPC['privacy'];
			$type=$_GPC['typecode'];
			if($_GPC['privacy']){
				if($_GPC['privacy']=='new'){
					$groundtype=$this->DboperateSearchGround($type, FALSE, FALSE,FALSE, TRUE,0);
				}else if($_GPC['privacy']=='top'){
					$groundtype=$this->DboperateSearchGround($type, FALSE, FALSE,FALSE, TRUE,1);
				}				
			}else {
				$groundtype=$this->DboperateSearchGround($type, FALSE, FALSE,FALSE, TRUE,0);
			}

			foreach($groundtype as $k=>$v){
				if($v['privacy']==0){
                   array_push($farr,$k);
				}
				$uinfo=$this->DboperateSearchUser($v['openid']);
				$v['nickname']=$uinfo['nickname']?$uinfo['nickname']:$uinfo['name'];
				$groundtype[$k]=$v;
			}
			$Groundtype=$groundtype;
		}else{
			$privacy=$_GPC['privacy'];
			if($_GPC['privacy']=='new'){
				$ground=$this->DboperateSearchGroundinfo(0);				
			}else if($_GPC['privacy']=='top'){
				$ground=$this->DboperateSearchGroundinfo(1);
			}else{
				$ground=$this->DboperateSearchGroundinfo(0);
			}
			foreach($ground as $k=>$v){
				if($v['privacy']==0){																							
					array_push($farr,$k);				
				}
			    $uinfo=$this->DboperateSearchUser($v['openid']);
				$v['nickname']=$uinfo['nickname']?$uinfo['nickname']:$uinfo['name'];
				$ground[$k]=$v;
			}	
			$Ground=$ground;
		}

		if($_GPC['Search']){
			///////////搜索后的排序
			$search=$_GPC['Search'];
			if($search){
				$sprivacy=$_GPC['privacy'];
				if($_GPC['privacy']=='new'){
					$searchinfo=$this->DboperateSearchGround($search, TRUE, FALSE, FALSE, FALSE, 0);
				}else if($_GPC['privacy']=='top'){
					$searchinfo=$this->DboperateSearchGround($search, TRUE, FALSE, FALSE, FALSE, 1);
				}else{
					$searchinfo=$this->DboperateSearchGround($search, TRUE, FALSE, FALSE, FALSE, 0);
				}
				//var_dump($searchinfo);
				foreach($searchinfo as $k=>$v){
					if($v['privacy']==0){
						array_push($farr,$k);
					}
					$sinfo=$this->DboperateSearchUser($v['openid']);
					$v['nickname']=$sinfo['nickname']?$sinfo['nickname']:$sinfo['name'];
					$searchinfo[$k]=$v;
				}
				$Searchinfo=$searchinfo;
			}
		}


        $avatar=$_W['account']['avatar'];
        $first=$farr[0];
        $img=pdo_fetchall("SELECT * FROM".tablename('ptj_pic')."WHERE ison=:io ORDER BY priority",array(':io'=>1),'');
		$proot=$_W['attachurl'];
		

		$attachroot=$_W['attachurl'];
       $Tinfo=pdo_fetch("SELECT COUNT(*) as count FROM".tablename('ptj_ground')."WHERE visible=:vis",array(':vis'=>1));

       $count=$Tinfo['count']; 
       //本页Record 数量
       $pagesize=20;
       //当前页码
       $pageindex=$_GPC['page']?$_GPC['page']:1;
       //HTML结构
        $page=pagination($count,$pageindex,$pagesize);
		include $this->template('index');
	}
	public function doWebPtjrules() {
		//这个操作被定义用来呈现 规则列表
		global  $_W,$_GPC;

	}
	public function doWebPtjtiezi(){
		global  $_W,$_GPC;
		$search = array();
		if($_GPC['search']=='on'){
			if($_GPC['title']!==""){
				$search['title'] = $_GPC['title'];
			}
			if($_GPC['name']!==""){
				$search['name'] = $_GPC['name'];
			}
			if($_GPC['privacy']!==""){
				$search['privacy'] = $_GPC['privacy'];
			}
			if($_GPC['visible']!==""){
				$search['visible'] = $_GPC['visible'];
			}
		}	
		$order = empty($_GPC['order'])?"privacy DESC":$_GPC['order'];
		$ground=$this->DboperateSearchTopicinfo($order,$search);
		$modelres=pdo_fetchall("SELECT * FROM".tablename('ptj_model')."WHERE vis=:so",array(':so'=>'on'));
		if($_GPC['status']){
			if($_GPC['status']=='down'){
				pdo_update('ptj_ground',array('privacy'=>0),array('jobid'=>$_GPC['jobid']));
			}
			elseif ($_GPC['status']=='up'){
				pdo_update('ptj_ground',array('privacy'=>1),array('jobid'=>$_GPC['jobid']));
				pdo_update('ptj_ground',array('pdate'=>date('Y-m-d H:i:s')),array('jobid'=>$_GPC['jobid']));
			}elseif($_GPC['status']=='close'){
				pdo_update('ptj_ground',array('visible'=>0),array('jobid'=>$_GPC['jobid']));
			}elseif($_GPC['status']=='open'){
				pdo_update('ptj_ground',array('visible'=>1),array('jobid'=>$_GPC['jobid']));
			}
			$_GPC = array();
			self::doWebPtjtiezi();
			return;
		}
		if($_GPC['delete']=='on'){
			pdo_delete('ptj_ground',array('jobid'=>$_GPC['jobid']));
			$url=$this->createWebUrl('ptjtiezi');
			$_GPC = array();
			self::doWebPtjtiezi();
			return;
		}
		if($_GPC['edit']=='on'){
			$detail = $this->DboperateTopicDetail($_GPC['jobid']);
			include $this->template('topic_edit');
			return;
		}
		if($_GPC['add']=='on'){
			include $this->template('topic_add');
			return;
		}			
		include $this->template('topic');
	}
	public function dowebPtjtopic_edit(){	
		global $_W,$_GPC;
		
		$jobid=$_GPC['jobid'];
		pdo_update('ptj_ground',array('title'=>$_GPC['title'],'credit'=>$_GPC['credit'],'phone'=>$_GPC['phone'],
			'type'=>$_GPC['type'],'content'=>$_GPC['content'],
			)
			,array('jobid'=>$jobid));
		
    	$this->doWebPtjtiezi();
	}
	public function dowebPtjtopic_add(){	
		global $_W,$_GPC;

		$jobid = substr(md5(substr(time(), 4,8)),4,9);
		$nowtime=date('Y-m-d');
        $now=date('Y-m-d H:i:s');
		$title = $_GPC['title'];
		$content = $_GPC['content'];
		$salary = $_GPC['salary'];
		$mount = $_GPC['mount'];
		$starttime = $_GPC['date'];
		$type = $_GPC['type'];
		$limit = $_GPC['haslimit'];
		$endtime = $_GPC['endtime'];
		$workplace = $_GPC['workplace'];
		$phone=$_GPC['phone'];
		$credit=$_GPC['credit'];
		$this->DboperateInsertGroundInfo('admin', $jobid, $title, $content,$phone,0,null,null,null,$credit,$type);
    	$this->doWebPtjtiezi();
	}
	public function doWebPtjadmin() {
		//这个操作被定义用来呈现 管理中心导航菜单
		global $_W,$_GPC;
		//$test=$this->DboperateFindWk('fromUser');
		//var_dump($test);
        $pinfo=$this->DboperateGetAllProfile();
        $adminget=$_GPC['PtjAdmin'];
	   if($adminget){
		    $info=pdo_fetch("SELECT * from".tablename('ptj_profile')."WHERE id=:i",array(':i'=>$_GPC['id']));
		 $this->SendTpl($info['openid'],$info,'refuse',$adminget['Res']);
	
		  $t=$this->DboperateDeleteUserInfo($_GPC['id']);
		  
		   $url=$this->createWebUrl('ptjadmin');
	   	 echo "<script language='javascript'>
	   	 location.href=\"$url\";
	   	 </script>";

		
	   }
	   if($_GPC['surebtn']==1){
	   	 $t=$this->DboperateAdminSurePro($_GPC['id']);
	   	 $info=pdo_fetch("SELECT * from".tablename('ptj_profile')."WHERE id=:i",array(':i'=>$_GPC['id']));
	   	 $this->SendTpl($info['openid'],$info,'auth');
		 
	   	 $url=$this->createWebUrl('ptjadmin');
		 
	   	 echo "<script language='javascript'>
	   	 location.href=\"$url\";
	   	 </script>";	
	 
	   }
	   
	   if($_GPC['redo']=='on'){
	   	 pdo_update('ptj_profile',array('identity'=>'worker'),array('id'=>$_GPC['id']));
	   	 $url=$this->createWebUrl('ptjadmin');	   	 	
	   	 echo "<script language='javascript'>
	   	 location.href=\"$url\";
	   	 </script>";
	   }
	   
	   if($_GPC['setadmin']=='on'){
	   	pdo_update('ptj_profile',array('identity'=>'admin'),array('id'=>$_GPC['id']));
	   	$url=$this->createWebUrl('ptjadmin');	   		
	   	echo "<script language='javascript'>
	   	location.href=\"$url\";
	   	</script>";
	   }
	   
	   if($_GPC['xu']=='on'){
		   pdo_update('ptj_profile',array('identity'=>'xued'),array('id'=>$_GPC['id']));
		   	   	$url=$this->createWebUrl('ptjadmin');	   		
	   	echo "<script language='javascript'>
	   	location.href=\"$url\";
	   	</script>";
	   }
	   
	   if($_GPC['rexu']=='on'){
		   $user=pdo_fetch("SELECT name FROM".tablename('ptj_profile')."WHERE id=:ID",array(':ID'=>$_GPC['id']));
		   if($user['name']){
			   pdo_update('ptj_profile',array('identity'=>'owner'),array('id'=>$_GPC['id']));
			   }
		    else{
				   pdo_update('ptj_profile',array('identity'=>'worker'),array('id'=>$_GPC['id']));
			   }
		   	   	$url=$this->createWebUrl('ptjadmin');	   		
	   	echo "<script language='javascript'>
	   	location.href=\"$url\";
	   	</script>";			   
	   }
	   
		include $this->template('Admin');
	}
	public function dowebPtjfunc(){	
		global $_W,$_GPC;
		
		$url=$_GPC['Ptjfunc']['Link'];
		$t=pdo_fetch("SELECT * FROM".tablename('ptj_link')."WHERE Linkid=:lid",array(':lid'=>1));
		if($t){
			pdo_update('ptj_link',array('url'=>$url),array('Linkid'=>1));
		}else{
			pdo_insert('ptj_link',array('url'=>$url,'Linkid'=>1));
		}
		
		
    	include $this->template('webfunc');
	}
	
	public function doMobilePtjlink() {
		//这个操作被定义用来呈现 微站首页导航图标
	}
	
	public  function doMobilePtjpay(){
		//
		global $_W,$_GPC;
		
				$credit=$_GPC['credit'];
		//./index.php?i=1&c=entry&do=index&m=meepo_credit1
		$shopUrl=pdo_fetch("SELECT * FROM".tablename('ptj_link')."WHERE Linkid=:id",array(':id'=>2));
		include $this->template('pay');

	}
	
	public function doMobilePtjmyinfo() {
		//这个操作被定义用来呈现 微站个人中心导航
		
			global $_W,$_GPC;

		require_once('config.php');
		require_once ('template_conf_bottom.php');
		
		$share = json_decode($share_config,true);
		$bottom=json_decode($bottom,true);
		
		load()->model('mc');
        load()->func('tpl');
		$avatar = '';
		$openid=$_W['openid'];
		$count=0;		
		$uid=$_W['member']['uid'];
		$sinfo=pdo_fetch("SELECT * FROM".tablename('ptj_link')."WHERE Linkid=:id",array(':id'=>1));
		$url=$sinfo['url']; 
		$modelres=pdo_fetchall("SELECT * FROM".tablename('ptj_model')."WHERE vis=:so",array(':so'=>'on'));
		if (empty($avatar)) {
			$userinfo = mc_oauth_userinfo();
			if (!is_error($userinfo) && !empty($userinfo) && is_array($userinfo) && !empty($userinfo['avatar'])) {
				$avatar = $userinfo['avatar'];
				$nickname=$userinfo['nickname'];
			}
		}
		if (empty($avatar)) {
			// 提示用户关注公众号。;
		//	echo "最终没有获取到头像,follow: {$_W['fans']['follow']}";
		} else {
		$src=$avatar;
		}
		
			if($this->DboperateIsWaiting($openid)){
			$waiting=true;
		}		
		
		//获取积分
		$uid=mc_openid2uid($openid);
	    $creditarray=mc_credit_fetch($uid);
		$credit=intval($creditarray['credit1']);
		
		
		$image=$_W['attachurl'].'confirm.jpg';

		$user=$this->DboperateSearchUser($openid);
		//var_dump($user);
		if($user['identity']=='worker'){
			$isworker=1;
		}
		else if($user['identity']=='owner'){
			$isworker=2;
		}
		else if($user['identity']=='admin'){
			$isworker=3;
		}
		else if($user['identity']=='xued'){
			$isworker=4;
		}
		//YES 兼职
		    //更新信息
		//获取当前时间
		$now=date("y-m-d");
		    $work=$this->DboperationSearchMyworkEx($openid);


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

		include $this->template('myinfo');
	}

	public function doMobilePtjdetails(){
		
		global $_W,$_GPC;
		require_once('config.php');
		$share = json_decode($share_config,true);
        load()->model('mc');
		$openid=$_W['openid'];
		$flag=$_GPC['flag'];
		$jobid=$_GPC['jobid'];
		$ground=pdo_fetch("SELECT * FROM".tablename('ptj_ground')."WHERE jobid=:jid",array(':jid'=>$jobid));
	//	$exists=$ground['exists'];
		$uid=mc_openid2uid($openid);
	    $creditarray=mc_credit_fetch($uid);
		$credit=$creditarray['credit1'];
		$collection=pdo_fetch("SELECT * FROM".tablename('ptj_collection')."WHERE jobid=:jid AND openid=:oid",array('jid'=>$jobid,'oid'=>$openid));
	//	var_dump($url);
		if(empty($ground)){
// 			$ground=pdo_fetch("SELECT * FROM".tablename('ptj_ground')." AS A left join ".tablename('ptj_working')
// 				." AS B on A.jobid=B.jobid left join ".tablename('ptj_profile')." AS C on ownerid=C.id and c.openid=:jopenid WHERE A.jobid=:jid"
// 				,array(':jid'=>$_GPC['jobid'],':jopenid'=>$openid));
// 			var_dump($ground);
			$ground=pdo_fetch("SELECT * FROM".tablename('ptj_ground')."WHERE jobid=:jid ",array(':jid'=>$_GPC['jobid']));
			$exists=$this->DboperateGetIntworkers($_GPC['jobid']);
			
		}
		//获取用户信息
		$oid=$ground['openid'];
		$Ownerinfo=$this->DboperateSearchUser($oid);	
		$name=$Ownerinfo['nickname']?$Ownerinfo['nickname']:$Ownerinfo['name'];
		$user=$this->DboperateSearchUser($_W['openid']);		 
		$bmbutton=$_GPC['button'];
		$entry='details';
		$user=$this->DboperateSearchUser($openid);
		//工人1 其他商家2 本商家和管理员3 
		//$isworker=$user['identity']=='worker'?1:2;
		if(($_W['openid']==$oid) OR $user['identity']=='admin'){
			$isworker=3;
		}
		if($user['identity']=='xued'){
			$isworker=4;
		}

			if(  $this->DboperateCheckUser($openid)){
                 $arr=$this->DboperateGetjob($openid, $oid);

                 if($_GPC['cbutton']==='collect'){
                 	 $ifcexist=pdo_fetch("SELECT * FROM".tablename('ptj_collection')."WHERE jobid=:jid AND openid=:oid",array(':jid'=>$_GPC['jobid'],':oid'=>$_W['openid']));
                 	 if($ifcexist){
                 	    $iflag=pdo_update('ptj_collection',array('watching'=>1),array('jobid'=>$_GPC['jobid'],'openid'=>$_W['openid']));
                 	 }else{
                 		$iflag=pdo_insert('ptj_collection',array('jobid'=>$_GPC['jobid'],'openid'=>$_W['openid'],'watching'=>'1'));
                 	 }
                 	if($iflag){
                 		message('success_collected');
                 	}else{
                 		message('fail_collected');
                 	}
                 }else if($_GPC['cbutton']==='uncollect'){
                 	$uflag=pdo_update('ptj_collection',array('watching'=>'0'),array('jobid'=>$_GPC['jobid']));
                 	if($uflag){
                 		message('success_uncollect');
                 	}
                 }                 
                 
                 if($oid!=$openid){
                  	if($arr AND in_array($ground['jobid'], $arr)){
                  		//表示已报名状态
                  		$commitFlag=1;							
                  		if($this->Checkifadmit($ground['jobid'],$openid)){
                  			//表示已录取状态
                  		 $commitFlag=2;
                  		}
                  	}
                  	else if($bmbutton=='on'){
                  		$this->DboperateSurepostinfo($oid, $openid, $ground['jobid']);
                  		//获取报名人数
                  		$worker=$this->DboperateGetIntworkers($ground['jobid']);
                  		               		
                  		//发送模板消息
                  		$t1=$this->SendTpl($openid,$Ownerinfo,'apply');
                  		
                  		$t2=$this->SendTpl($oid, $ground,'full');  
                		//var_dump($oid); 
                		if($t1 && $t2){
                			message('checkin_success');
                		}else{
                			message('send_fail');
                		}               		
                  		
                  	}
                 }
                 else if($bmbutton=='on'){
                 	//checkin_fail_1      -------------------   商家号不允许报名
                 	message('checkin_fail_1');
                 }
			}
			else if ($bmbutton=='on' AND !$this->DboperateCheckUser($openid)){
				//checkin_fail_2   ------------------  还没有注册
				message('checkin_fail_2');				
			}
			
						//查看当前招聘是否被置顶
			$ginfo=pdo_fetch("SELECT * from".tablename('ptj_ground')."WHERE jobid=:jid",array(':jid'=>$ground['jobid']));
			$ifprivacy=$ginfo['privacy'];
			if($_GPC['top']=='on'){
				if($this->GetPrivacyNum()>=10){
					message('full');
				}
				else if($credit>=100){
				$f=pdo_update('ptj_ground',array('privacy'=>1,'pdate'=>date('Y-m-d H:i:s')),array('jobid'=>$ground['jobid']));		
				$s=mc_credit_update(mc_openid2uid($_W['openid']), 'credit1',-100);	
						if($f AND $s ){
							$this->SendTpl($_W['openid'],$user, 'credit1', $credit-100);							
							message('success');
						}				
				}
		       else{
				   message('fail');
				 //  echo 'fail';
			   }
			}
		include $this->template('details');
		}
	
	
	public function doMobilePtjorders() {
		//这个操作被定义用来呈现 微站快捷功能导航
	}

	public function doMobilePtjfunc(){
		//功能封面 外链接
		global $_W,$_GPC;
		$sinfo=pdo_fetch("SELECT * FROM".tablename('ptj_link')."WHERE Linkid=:id",array(':id'=>1));
		$url=$sinfo['url']; 
		include $this->template('divfunc');
	}
	public function doMobilePtjeditprofile(){
		global $_W,$_GPC;
		load()->model('mc');
		load()->func('file');
		$user=$this->DboperateSearchUser($_W['openid']);
		$avatar='';
		if (empty($avatar)) {
			$userinfo = mc_oauth_userinfo();
			if (!is_error($userinfo) && !empty($userinfo) && is_array($userinfo) && !empty($userinfo['avatar'])) {
				$avatar = $userinfo['avatar'];
				$nickname=$userinfo['nickname'];
			}
		}
		if (empty($avatar)) {
			// 提示用户关注公众号。;
			//	echo "请先关注该公众号";
		} else {
			$src=$avatar;
		}
	//	$avatar=$_GPC['avatar'];
		$nickname=$_GPC['nickname'];
		$sex=$_GPC['sex'];
		if( $nickname AND $sex){
			$afile=file_upload($_FILES['avatar'],'image');
			$t=pdo_update('ptj_profile',array('nickname'=>$nickname,'sex'=>$sex,'avatar'=>tomedia($afile['path'])),array('openid'=>$_W['openid']));
	if($t){
		$url=$this->createMobileUrl('ptjmyinfo');
			echo "<script>
					window.location.href='$url';
					</script>";
	}
		}
		
		
		include $this->template('editprofile');
	}	
	public function doWebPtjshare(){
		//分享设置
		global $_W,$_GPC;
		//读取缓存文件
		require_once('config.php');
		$share = json_decode($share_config,true);
		include $this->template('share');
	}
	
	public function doWebPtjshare_edit(){
		//分享设置
		global $_W,$_GPC;
		//去除无用数据
		foreach ($_GPC as $key => $value) {
			if(strpos($key,'title')===false && strpos($key,'imgurl')===false && strpos($key,'desc')===false){
				unset($_GPC[$key]);
			}
		}
		$share = json_encode($_GPC);
		$share_file = fopen(dirname(__FILE__)."/config.php", "w") or die("Unable to open file!");;
		$data = "<?php ";
		fwrite($share_file, $data);
		$data = '$share_config=\''.$share.'\';';
		fwrite($share_file, $data);
		fclose($share_file);
		self::doWebPtjshare();
	}
	
	public function doWebPtjmodel(){
	   global $_W,$_GPC;
	   load()->func('tpl');
	   load()->func('file');
	   if($_GPC['modelcode'] && $_GPC['modelname'] && $_GPC['check']){	   	
	   	 $f=pdo_insert('ptj_model',array('typecode'=>$_GPC['modelcode'],'typename'=>$_GPC['modelname'],'vis'=>$_GPC['check'],'typeicon'=>tomedia($_GPC['icon'])));
	   	 if($f){
	   	 	$url=wurl('site/entry',array('eid'=>$_GPC['eid']));
	   	 	echo "<script>
	   	 			location.href='$url';
	   	 			</script>";
	   	 }
	   }
	   
	   $allinfo=pdo_fetchall("SELECT * FROM".tablename('ptj_model'));
	   foreach ($allinfo as $k=>$v){
	   	  $count=pdo_fetchall("SELECT COUNT(*) AS count FROM".tablename('ptj_ground')."WHERE type=:ty",array(':ty'=>trim($v['typecode'])));
	      $v['hotrate']=$count[$k]['count'];
	   	  $allinfo[$k]=$v;
	   }
	   $All=$allinfo;
	   
	   
	   if($_GPC['btn_switch']=='on'){
	   		$t=pdo_update('ptj_model',array('vis'=>$_GPC['flag']),array('id'=>$_GPC['id']));
	   		if($t){
	   	     	message('switched');
	   		}
	   }
	   include $this->template('model');
	}
	
	public function doWebPtjmsg(){
		global  $_W,$_GPC;
		require_once ('sms.php');
		$sms=json_decode($sms,true);	
		//var_dump($sms);
if($_GPC['sms_account'] && $_GPC['sms_password']){
		$inithead="<?php\n";
		$filelocale=fopen(dirname(__FILE__)."/sms.php", "w") or die('保存失败');
		$model=$_GPC;
		fwrite($filelocale, $inithead);
		fclose($filelocale);
		$dataSource=json_encode($model);
		$data='$sms=\''.$dataSource.'\';';
		$t=file_put_contents(dirname(__FILE__)."/sms.php", $data,FILE_APPEND);		
		if($t){
			$url=wurl('site/entry',array('eid'=>$_GPC['eid']));
			echo "<script>
			location.href='$url';
			</script>";
		}
}

include $this->template('msg');
	}
	
	
	
	public function doMobilePtjshare(){
		require_once('config.php');
		echo $share_config;
	}
	public function doMobilePtjperfect(){
		//这个操作被定义用户  呈现用户完善信息的页面
		global $_W,$_GPC;
		require_once 'sms.php';
		load()->model('mc');
		load()->func('tpl');
		$avatar = '';
		$openid=$_W['openid'];
		$count=0;
		
		$uid=$_W['member']['uid'];
		
		if (!empty($_W['member']['uid'])) {
			$member = mc_fetch(intval($_W['member']['uid']), array('avatar','nickname'));
		
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
			//echo "最终没有获取到头像,follow: {$_W['fans']['follow']}";
		} else {
		$src=$avatar;
		}
	//////////////////////////////////////mc调用结束////////////////////////////
	$sms=json_decode($sms,true);
		if($_GPC['tel'] AND $_GPC['verify']!=='ing'){
			//对TEL进行动态正则匹配
			$tel=$_GPC['tel'];
			$t=preg_match('/^1[34578]\d{9}$/', $tel);
			if($t){
				//给前端号码
				$tel=$_GPC['tel'];
				message('tel_success');
			}else{
				message('tel_fail');
			}
		}
		
		if($_GPC['verify']==='ing'){
		     //开始构造短信信息
			$statusStr = array(
					"0" => "POSTED",
					"-1" => "参数不全",
					"-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
					"30" => "密码错误",
					"40" => "账号不存在",
					"41" => "余额不足",
					"42" => "帐户已过期",
					"43" => "IP地址限制",
					"50" => "内容含有敏感词"
			);
			$smsapi = "http://api.smsbao.com/";
			//$user = "sevenplus2016"; //短信平台帐号
			$user = $sms['sms_account'];
			//$pass = md5("wa7plus"); //短信平台密码
			$pass = md5($sms['sms_password']);
			$m_content=substr(time(),-4,4);//要发送的短信内容
			$time=1;
			$content="【老司机】"."您的验证码为: {$m_content},"."在{$time}分钟内有效---{$_W['account']['name']}";
			$phone = $_GPC['tel'];//要发送短信的手机号码
			$sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);
			$result =file_get_contents($sendurl) ;
			$msg=$statusStr[$result];
			$resarr=array(
				"msg"=>$msg,
				"content"=>$m_content,
			);
			$res=json_encode($resarr);
			message($res);     	
		}
         $name=$_GPC['name'];
		 $phone=$_GPC['phone'];
		 $openid=$_W['openid'];
		 if(isset($name) && isset($phone)){
		 	pdo_insert('ptj_profile',array('name'=>$name,'phone'=>$phone,'sure'=>1,'openid'=>$openid));		 	
		 }

		include $this->template('perfect');
	}
	
	public function doMobilePtjOwnerindex() {
		//这个操作被定义用来呈现 商家主页
		global  $_W,$_GPC;
		
		$openid=$_W['openid'];
		$user=$this->DboperateSearchUser($_GPC['ground']['openid']);
		$OwnerEx=$this->DboperateGetworkerinfo($_GPC['ground']['openid']);
		$ground=$_GPC['ground'];
		load()->model('mc');		
		$ouid=mc_openid2uid($_GPC['ground']['openid']);
		$member = mc_fetch(intval($ouid), array('avatar','nickname'));
		$src=$member['avatar'];
          
		$des=$_GPC['description'];
		
		$last=$_GPC['last'];
		
		include $this->template('ownerindex');
	}
	
	public function doMobilePtjWorkerinfo(){
		//已报名的用户
		global $_W,$_GPC;
		load()->model('mc');
       // $exists=$_GPC['exists'];
       $jobid=$_GPC['jobid'];
		$ground=pdo_fetch("SELECT * FROM".tablename('ptj_ground')."WHERE jobid=:jid",array(':jid'=>$jobid));
		$openid=$ground['openid'];
		$uidarr=mc_credit_fetch(mc_openid2uid($openid));
		$credit=$uidarr['credit1'];
		$Ownerinfo=$this->DboperateSearchUser($openid);	

		$url=$this->createMobileUrl('ptjworkerinfo',array('ground'=>$ground));
		$Wkinfo=$this->DboperateFindWk($openid,$jobid);
		if($_GPC['oid']){
		  $Ownerinfo=$this->DboperateSearchUser($_GPC['oid']);		
		  $Wkinfo=$this->DboperateFindWk($_GPC['oid'],$_GPC['jobid']);
		  $url=$this->createMobileUrl('ptjworkerinfo',array('oid'=>$_GPC['oid']));
		  //表示此情况是由个人中心调用过来
		  $flag=1;
		}	

		
		if($_GPC['ebutton']=='on'){
						
			$t=$this->DboperateEmploy($_GPC['wopenid'],$jobid);
			mc_credit_update(mc_openid2uid($_W['openid']), 'credit1',-$ground['credit']);
			$this->SendTpl($_GPC['wopenid'],$Ownerinfo,'admit',$ground['date']);
            if($t){
            	message('w_success');
            }else{
            	message('w_fail');
            }
			
		}
		
		if($_GPC['abutton']=='on'){
			//TODO: 积分操作
			$needCredit=$ground['credit']*count($Wkinfo);
			if($needCredit<=$credit){
				
				foreach ($Wkinfo as $k=>$v){
					if($v['status']==0){
						$All=$this->DboperateEmploy($v['wopenid'],$jobid);
						//给报名用户增加积分
						mc_credit_update(mc_openid2uid($v['wopenid']),'credit1',$ground['credit']);
						//给雇主扣除全部积分
					$this->SendTpl($v['wopenid'],$Ownerinfo,'apply',$ground['date']);							
					}
				}
				mc_credit_update(mc_openid2uid($_W['openid']), 'credit1',-$needCredit);
				message('success');
			}else{
				message('fail');
			}

		}
		
		if($_GPC['sbutton']=='on'){
				$de=$this->DboperateUndoWanna($ground['jobid']);
				if($de){
					message('success_stop');
				}else{
					message('fail_stop');
				}
		}		
		include $this->template('workerinfo');
	}

	
	public function doMobilePtjmypost(){
		//发布信息
		   global  $_W,$_GPC;
			$ground=$_GPC['ground'];
			$user=$this->DboperateSearchUser($_W['openid']);
			if($user['identity']=='worker' OR $user['identity']=='admin'){
				$isworker=1;
			}
			else if($user['identity']=='owner'){
				$isworker=2;
			}
		include $this->template('posttype');
	}
	
	public function doMobilePtjpostinfo(){
		//发布信息内容
		global  $_W,$_GPC;
		
		load()->func('tpl');
		load()->model('mc');
		load()->func('file');		
		$uid=mc_openid2uid($_W['openid']);
	    $creditarray=mc_credit_fetch($uid);
		$credit=$creditarray['credit1'];
		
		$ground=$_GPC['ground'];
		$type=$_GPC['type'];
	//	$mypost=$_GPC['Ptjpostinfo'];
		$user=$this->DboperateSearchUser($_W['openid']);		

if($_GPC){
     	   		$title=$_GPC['title'];       
       			$content=$_GPC['description'];
     			 $privacy=$_GPC['top']=='on'?1:0;
     			 $c_pay=$_GPC['credit'];
     			 $phone=$_GPC['default_tel']!=''?$_GPC['default_tel']:$_GPC['new_tel'];
     			 if($_FILES['pic1']['name']){
     			 	$pic1=file_upload($_FILES['pic1'],'image');
     			 	$path1=tomedia($pic1['path']);    			 	
     			 }
     			 if($_FILES['pic2']['name']){
				$pic2=file_upload($_FILES['pic2'],'image');				
				$path2=tomedia($pic2['path']);
     			 }
     			 if($_FILES['pic3']['name']){     			 
				$pic3=file_upload($_FILES['pic3'],'image');		
				$path3=tomedia($pic3['path']);	
     			 }					
        //检测状态
        if($title!="" AND $content!=""   AND $phone!="" AND $credit>=floatval($c_pay)){
        	

        	
        	//判断置顶招聘是否已满
        	if($privacy AND $this->GetPrivacyNum()>10){
               message('置顶招聘已满...请稍后重试!','','warning');  
               exit();
        	}
        	else{
			        	if($privacy AND $credit>=100){
        		$value=mc_credit_update(mc_openid2uid($_W['openid']), 'credit1',-100);
        		$this->SendTpl($_W['openid'],$user, 'credit1', $credit-100);				
                      	}	
						else if($privacy){
							message('积分不足,您无权进行此操作,请前往个人中心充值。如有问题,请与管理员联系!');
							exit();
						}
			}
         $jobid=substr(md5(substr(time(), 4,8)),4,9);
      
        $t=$this->DboperateInsertGroundInfo($_W['openid'], $jobid, $title, $content,$phone, $privacy,$path1,$path2,$path3,intval($c_pay),$type);
        if($t){
        	$url=murl('entry//ptjindex',array('m'=>$this->modulename));      	
        	echo "<script>
        			location.href='$url';
        			</script>";
        	}                  
        }	
}

		include $this->template('postinfo');
	}
	
	public function doMobilePtjWorkerindex(){
		//用户详情
		global $_W,$_GPC;
        load()->model('mc');
	//	$exists=$_GPC['exists'];
		$ground=$_GPC['ground'];
		$entry=$_GPC['entry'];
		$wuid=mc_openid2uid($ground['openid']);
		$member = mc_fetch(intval($wuid), array('avatar','nickname'));
		$src=$member['avatar'];
		
		$winfo=$this->DboperateSearchUser($ground['openid']);
	//	$last=$_GPC['last'];
	   
		$wEx=$this->DboperationSearchMyworkEx($ground['openid']);
		
	//	var_dump($wEx);
		
		include $this->template('workerindex');
	}
	
	public function doMobilePtjmodel(){
		global $_W,$_GPC;
		$openid=$_W['openid'];
		$id=$_GPC['id'];
		if($id=='owner'){
		$OwnerEx=$this->DboperateGetworkerinfo($_W['openid']);
		}else if($id=='worker'){
		$wEx=$this->DboperationSearchMyworkEx($_W['openid']);
		}else if($id==='collection'){
			$collection=pdo_fetchall("SELECT * FROM".tablename('ptj_collection')."WHERE openid=:oid AND watching=:w",array(':oid'=>$_W['openid'],':w'=>'1'));
			foreach ($collection as $k=>$v){
				$cinfo=pdo_fetch("SELECT * FROM".tablename('ptj_ground')."WHERE jobid=:jid",array(':jid'=>$v['jobid']));
				$v['title']=$cinfo['title'];
				$v['pic1']=$cinfo['pic1'];
				$v['pic2']=$cinfo['pic2'];
				$v['pic3']=$cinfo['pic3'];
				$v['content']=$cinfo['content'];
				$v['type']=$cinfo['type'];
				$collection[$k]=$v;
			}
	//		var_dump($collection);
			$Coll=$collection;
		}
		include $this->template('usemodel');
	}
	
	
	public function doMobilePtjing(){
		//TODO 其他页面  懒得做
		global $_W,$_GPC;
		$ground=$_GPC['ground'];
		include $this->template('ing');
	}
	
	public function doMobileRecharge(){
		//积分充值入口
		global $_W, $_GPC;
		if (empty($_W['member']['uid'])) {
			checkauth();
		}
		$credit1_lv = $this->module['config']['credit1_lv']?$this->module['config']['credit1_lv']:1;
		$username = $_W['member']['email'] ? $_W['member']['email'] : $_W['member']['mobile'];
		if(empty($username)) {
			message('您的用户信息不完整,请完善用户信息后再充值', '', 'error');
		}
		if (checksubmit('submit', true) || !empty($_GPC['ajax'])||!empty($_GPC['money'])) {
		
			$fee = floatval($_GPC['money']);
			if($fee <= 0) {
				message('支付错误, 积分小于0');
			}
			$chargerecord = pdo_fetch("SELECT * FROM ".tablename('mc_credits_recharge')." WHERE uniacid = :uniacid AND uid = :uid AND fee = :fee AND status = '0'", array(
					':uniacid' => $_W['uniacid'],
					':uid' => $_W['member']['uid'],
					':fee' => $fee*$credit1_lv,
			));
			if (empty($chargerecord)) {
				$chargerecord = array(
						'uid' => $_W['member']['uid'],
						'uniacid' => $_W['uniacid'],
						'tid' => date('YmdHi').random(10, 1),
						'fee' => $fee*$credit1_lv,
						'status' => 0,
						'createtime' => TIMESTAMP,
				);
				if (!pdo_insert('mc_credits_recharge', $chargerecord)) {
					message('创建充值订单失败，请重试！', $this->createMobileUrl('index'), 'error');
				}
			}
			$params = array(
					'tid' => $chargerecord['tid'],
					'ordersn' => $chargerecord['tid'],
					'title' => '系统充值积分',
					'fee' => $chargerecord['fee'],
					'user' => $_W['member']['uid'],
			);
			$this->pay($params);
		} else {
			include $this->template('recharge');
		}	
	}

	protected function pay($params = array()) {
		global $_W;
		$params['module'] = $this->module['name'];
		$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid';
		$pars = array();
		$pars[':uniacid'] = $_W['uniacid'];
		$pars[':module'] = $params['module'];
		$pars[':tid'] = $params['tid'];
		$log = pdo_fetch($sql, $pars);
		if(!empty($log) && $log['status'] == '1') {
			message('这个订单已经支付成功, 不需要重复支付.');
		}
		$setting = uni_setting($_W['uniacid'], array('payment', 'creditbehaviors'));
		if(!is_array($setting['payment'])) {
			message('没有有效的支付方式, 请联系网站管理员.');
		}
		$pay = $setting['payment'];
		$pay['credit']['switch'] = false;
		$pay['delivery']['switch'] = false;
		include $this->template('common/paycenter');
	}
	public function payResult($params) {
		load()->model('mc');
		$status = pdo_fetchcolumn("SELECT status FROM ".tablename('mc_credits_recharge')." WHERE tid = :tid", array(':tid' => $params['tid']));
		if (empty($status)) {
			$credit1_lv = $this->module['config']['credit1_lv']?$this->module['config']['credit1_lv']:1;
			$fee = $params['fee']/$credit1_lv;
			$data = array('status' => $params['result'] == 'success' ? 1 : -1);
			if ($params['type'] == 'wechat') {
				$data['transid'] = $params['tag']['transaction_id'];
				$params['user'] = mc_openid2uid($params['user']);
			}
			pdo_update('mc_credits_recharge', $data, array('tid' => $params['tid']));
			if ($params['result'] == 'success' && $params['from'] == 'notify') {
				$setting = uni_setting($_W['uniacid'], array('creditbehaviors'));
				//a:2:{s:8:"activity";s:7:"credit1";s:8:"currency";s:7:"credit2";}
				$credit = $setting['creditbehaviors']['activity'];
				if(empty($credit)) {
					message('站点积分行为参数配置错误,请联系服务商', '', 'error');
				} else {
					$paydata = array('wechat' => '微信', 'alipay' => '支付宝');
					$record[] = $params['user'];
					$record[] = '用户通过' . $paydata[$params['type']] . '充值' . $fee.'积分';
					mc_credit_update($params['user'], $credit, $fee, $record);
				}
			}
		}
		if ($params['from'] == 'return') {
			if ($params['result'] == 'success') {
				message('支付成功！', '../../app/' . url('mc/home'), 'success');
			} else {
				message('支付失败！', '../../app/' . url('mc/home'), 'error');
			}
		}
	}	
	
	
	
	
///////////////////////////////////////////数据库操作操作函数      开始///////////////////////////////////////
/**
 * 获得帖子的详情（后台管理接口）
 * @param unknown $jobid
 * @return boolean
 */
 private function DboperateTopicDetail($jobid){
 	$detail=pdo_fetch("SELECT * FROM".tablename('ptj_ground')."WHERE jobid=:oid",array('oid'=>$jobid));
 	return $detail;
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
private function DboperateInsertIntoProfile($openid,$name,$nickname,$phone,$sex,$location,$email,$iden,$name=NULL,$Caddress=NULL,$Cphone=NULL){
	$T=pdo_insert('ptj_profile',array('openid'=>$openid,'name'=>$name,'nickname'=>$nickname,'phone'=>$phone,'location'=>$location,'identity'=>$iden,'count'=>0,'sex'=>$sex,'email'=>$email,'sure'=>'0'));

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
	private function DboperateInsertGroundInfo($openid,$jobid,$title,$content,$phone,$privay,$pic1=null,$pic2=null,$pic3=null,$credit,$type){
		
		$t=pdo_insert('ptj_ground',array('title'=>$title,'jobid'=>$jobid,'content'=>$content,'phone'=>$phone,'openid'=>$openid,'visible'=>1,'privacy'=>$privay,'pic1'=>$pic1,'pic2'=>$pic2,'pic3'=>$pic3,'credit'=>$credit,'type'=>$type));
		$admin=pdo_fetch("SELECT * FROM".tablename('ptj_profile')."WHERE openid='admin'",array(),'');
		if (empty($admin)) {
			//如果没有默认管理员，则添加一个默认管理员
			$t=pdo_insert('ptj_profile',array('sure'=>1,'openid'=>'admin','name'=>'administrator','identity'=>'admin','sex'=>'Man'));
		}
		return $t;
	}
	/**
	 * 查看广场
	 * @return Ambigous <boolean, multitype:unknown >
	 */
private  function  DboperateSearchGroundinfo($pri=1){
	$ground=array();
	$type=array();
	$Ground=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."WHERE visible=:vi AND privacy=:pa ORDER BY id DESC",array(':vi'=>1,':pa'=>$pri),'');
	$nowtype=pdo_fetchall("SELECT * FROM".tablename('ptj_model')."WHERE vis=:vi",array(':vi'=>'on'));
	foreach ($nowtype as $key => $value){
		array_push($type, trim($value['typecode']));
	}
	foreach ($Ground as $k => $v){
	//	 $t=in_array($v['type'], $type);

		if(in_array(trim($v['type']), $type)){
			array_push($ground, $v);
		}
	}

	return $ground;
}	
/**
 * 
 * @return multitype:
 */
private  function  DboperateSearchTopicinfo($order,$search){
	$ground=array();
	$flag = 0;
	$sortcolumn = "";
	$where = "1=1";
	if($order == 'exists'){
		$flag = 1;
		//需要对数组进行升序排序
		$sortcolumn = "exists";
		$order = 'privacy DESC';
	}elseif ($order == 'exists DESC') {
		$flag = 2;
		//需要对数组进行降序排序
		$sortcolumn = "exists";
		$order = 'privacy DESC';
	}elseif($order == 'name'){
		$flag = 1;
		//需要对数组进行升序排序
		$sortcolumn = "name";
		$order = 'privacy DESC';
	}elseif ($order == 'name DESC') {
		$flag = 2;
		//需要对数组进行降序排序
		$sortcolumn = "name";
		$order = 'privacy DESC';
	}
	if($search['privacy']!=""&&$search['privacy']!="2") {
		$where = 'privacy='.$search['privacy'];
	}
	if($search['title']!=""){
		if($where == '1=1'){
			$where = 'title LIKE "%'.$search['title'].'%"';
		}else{
			$where.=' AND title LIKE "%'.$search['title'].'%"';
		}
	}
	if($search['name']!=""){
		if($where == '1=1'){
			$where = '(name LIKE "%'.$search['name'].'%"  AND name = "") OR (name LIKE "%'.$search['name'].'%")';
		}else{
			$where.=' AND ((name LIKE "%'.$search['name'].'%" AND name = "") OR (name LIKE "%'.$search['name'].'%"))';
		}
	}
	if($search['visible']!=""&&$search['visible']!="2") {
		if($where == '1=1'){
			$where = 'visible='.$search['visible'];
		}else{
			$where.=' AND visible='.$search['visible'];
		}
		
	}
	$Ground=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."AS A LEFT JOIN".tablename("ptj_profile")."AS B on A.openid = B.openid WHERE ".$where." ORDER BY ".$order,array(),'');
	foreach ($Ground as $k=>$v){
		//$this->DboperateCheckGroundTime($v['jobid'], $v['date'], date('Y-m-d'));
		$this->RefreshPrivacy($v['pdate'], $v['jobid']);
		$oprofile=$this->DboperateSearchUser($v['openid']);
		$oname=$oprofile['name']?$oprofile['name']:$oprofile['name'];
		$exists=$this->DboperateGetIntworkers($v['jobid']);
		$arr=array(
		       'vis'=>$v['visible'],
			   'exists'=>$exists,
			   'nowtime'=>$v['nowtime'],
				'jobid'=>$v['jobid'],
				'title'=>$v['title'],
				'content'=>$v['content'],
				'mount'=>$v['mount'],
				'salary'=>$v['salary'],
				'date'=>$v['date'],
				'openid'=>$v['openid'],
				'now'=>$v['nowtime'],
				'limit'=>$v['limit'],
				'stopdate'=>$v['stopdate'],
				'workplace'=>$v['workplace'],
				'name'=>$oname,
				'privacy'=>$v['privacy'],
		);
		array_push($ground, $arr);
	}
	//选择排序
	if ($flag == 1) {
		for ($i=0; $i < count($ground); $i++) { 
			for ($j = $i; $j < count($ground); $j++) { 
				if($ground[$i][$sortcolumn]<$ground[$j][$sortcolumn]){
					$temp = $ground[$j];
					$ground[$j] = $ground[$i];
					$ground[$i] = $temp;
				}
			}
		}
	}elseif ($flag == 2) {
		for ($i=0; $i < count($ground); $i++) { 
			for ($j = $i; $j < count($ground); $j++) { 
				if($ground[$i][$sortcolumn]>$ground[$j][$sortcolumn]){
					$temp = $ground[$j];
					$ground[$j] = $ground[$i];
					$ground[$i] = $temp;
				}
			}
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
private function DboperateSurepostinfo($owneroid,$workoid,$jobid){
	
	$t=pdo_insert('ptj_working',array('owneroid'=>$owneroid,'workeroid'=>$workoid,'jobid'=>$jobid,'visible'=>1,'sure'=>0));
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
	$t=pdo_fetchall("SELECT jobid FROM".tablename('ptj_working')."WHERE workeroid=:oid and owneroid=:ooid ",array(':oid'=>$wopenid,':ooid'=>$oopenid),'');
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
private function  DboperateSearchGround($info,$search,$time,$owner,$type,$privacy){
	$ground=array();
	$Type=array();
	if($search){
		$t=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."WHERE title REGEXP :ti AND visible=:vi AND privacy=:pr",array(':ti'=>trim($info),':vi'=>1,':pr'=>$privacy),'');
		$nowtype=pdo_fetchall("SELECT * FROM".tablename('ptj_model')."WHERE vis=:vi",array(':vi'=>'on'));
		foreach ($nowtype as $key => $value){
			array_push($Type, trim($value['typecode']));
		}
		foreach ($t as $k => $v){
		
			if(in_array(trim($v['type']), $Type)){
				array_push($ground, $v);
			//	$ground=$v;
			}
		}

	}
	else if($time){
		$t=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."WHERE title REGEXP :ti AND WHERE visible=:vi ORDER BY date ",array(':ti'=>$info,':vi'=>1),'');
		$ground=$t;
	}
	else if($owner){
		$t=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."WHERE title REGEXP :ti AND WHERE visible=:vi ORDER BY openid DESC",array(':ti'=>$info,':vi'=>1),'');
		$ground=$t;
	}
	else if($type) {
		$t=pdo_fetchall("SELECT * FROM".tablename('ptj_ground')."WHERE type=:ti  AND  visible=:vi AND privacy=:pr ",array(':pr'=>$privacy,':ti'=>$info,':vi'=>1),'');
		$ground=$t;
	}

	return $ground;
}
/**
 * 查找工作经历
 * @param unknown $workeroid
 * @return multitype:
 */
private function  DboperationSearchMyworkEx($workeroid){
	
		$check=pdo_fetchall("SELECT * FROM".tablename('ptj_working')."WHERE workeroid=:woid",array(':woid'=>$workeroid));
	foreach ($check as $key=>$value){
	//	$this->Dboperatechecktime($value['jobid'],date('Y-m-d H:i:s'), $value['workeroid']);
	}
	
	$arr=array();
	$T=pdo_fetchall("SELECT jobid,sure,visible FROM".tablename('ptj_working')."WHERE workeroid=:woid",array(':woid'=>$workeroid));
	//三表联查  重组数组
    foreach($T as $k=>$v){
    	$jobid=$v['jobid'];
    	$T1=pdo_fetch("SELECT * FROM".tablename('ptj_ground')."WHERE jobid=:jid",array(':jid'=>$jobid));
		$profile=$this->DboperateSearchUser($T1['openid']);
    	//结构化array
        $Arr=array(
				'name'=>$profile['name'],
				'name'=>$profile['name'],
        	    'jobid'=>$jobid,
        		'title'=>$T1['title'],
        		'content'=>$T1['content'],
        		'now'=>$T1['nowtime'],
        		'status'=>$v['sure'],
        		'vis'=>$v['visible'],
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
	$jobtime=pdo_fetch("select stopdate FROM".tablename('ptj_ground')."WHERE jobid=:jid",array(':jid'=>$jobid));
	if(strtotime($jobtime['stopdate'])<=strtotime($now)){
		$i=pdo_update('ptj_working',array('visible'=>0),array('workeroid'=>$openid,'jobid'=>$jobid));	
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
				'now'=>$v['nowtime'],
				'limit'=>$v['limit'],
				'stopdate'=>$v['stopdate'],
				'workplace'=>$v['workplace'],
				'vis'=>$v['visible'],
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

  $t1=pdo_update('ptj_ground',array('visible'=>0),array('jobid'=>$jobid));

   return $t1;
}
/**
 * 查看工人简历
 * @param unknown $ownerid
 * @return multitype:
 */
private function DboperateFindWk($ownerid,$jobid){
	$Allarray=array();
	$info=pdo_fetchall("SELECT * FROM".tablename('ptj_working')."WHERE owneroid=:oid and jobid=:jid",array(':oid'=>$ownerid,':jid'=>$jobid),'');
	foreach ($info as $k=>$v){
		 $winfo=pdo_fetch("SELECT * FROM".tablename('ptj_profile')."WHERE openid=:oid",array(':oid'=>$v['workeroid']));
		 $speinfo=pdo_fetch("SELECT * FROM".tablename('ptj_ground')."WHERE jobid=:jid",array(':jid'=>$v['jobid']));
		 $wkinfo=array(
		 	    'wkinfo'=>$speinfo['title'],
		 		'wkphone'=>$winfo['phone'],
		 		'wksex'=>$winfo['sex'],
		 		'wopenid'=>$winfo['openid'],
		 		'wkname'=>$winfo['name'],
		 		'wklocation'=>$winfo['location'],
		 		'status'=>$v['sure'],
		 		'wopenid'=>$v['workeroid'],
		 );
		 array_push($Allarray, $wkinfo);
	}
	return $Allarray;
}
/**
 * 雇佣员工
 * @param unknown $wopenid
 * @return Ambigous <boolean, unknown>
 */
private function DboperateEmploy($wopenid,$jobid){
	$t=pdo_update('ptj_working',array('sure'=>1),array('workeroid'=>$wopenid,'jobid'=>$jobid));
	return $t;
}


private function RefreshPrivacy($time,$jobid){
	if(strtotime(date('Y-m-d H:i:s'))-strtotime($time)>=strtotime("+48 hours")){
		pdo_update('ptj_ground',array('privacy'=>0,'pdate'=>NULL),array('jobid'=>$jobid));
	}else{
		return TRUE;
	}
}

/**
 * 
 * @param array $imginfo
 * @param unknown $aurl
 * @return multitype:
 */
private function  upimgurl($imginfo,$aurl){
	$IMG=array();
	foreach ($imginfo as $k=>$v){
		if($v['imgurl']==$aurl){
			$v['imgurl']=NULL;
		}
		array_push($IMG,$v['imgurl']);
	}
	return $IMG;
}


private function GetPrivacyNum(){
	$info=pdo_fetchall("SELECT COUNT(*) as num FROM".tablename('ptj_ground')."WHERE visible=:vi and privacy=:pr",array(':vi'=>1,':pr'=>1));
    return $info['num'];
}
/**
 * 查找某一对 Ownerid 和 workerid 的录取关系
 * 
 * @param unknown $jid
 * @return boolean
 */
private function Checkifadmit($jid,$oid){
	$info=pdo_fetch("SELECT sure FROM".tablename('ptj_working')."WHERE jobid=:jid and workeroid=:oid",array(':jid'=>$jid,':oid'=>$oid));
	return $info['sure'];
}
/**
 * 确认状态: 是否所有的信息Sured?
 * @param unknown $jobid
 * @return number
 */
private function ifAllSure($jobid){
   $workingStatus=pdo_fetch("SELECT COUNT(*) as count FROM".tablename('ptj_working')."WHERE jobid=:jid",array(':jid'=>$jobid));
   return  $workingStatus['count'];
}


//////////////////////管理员//////////////////////////
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
/**
 * 管理员是否已经同意 防止重复提交
 * @param unknown $openid
 * @return boolean
 */
private function DboperateIsWaiting($openid){
	$info=pdo_fetch("SELECT * FROM".tablename('ptj_profile')."WHERE openid=:oid",array(':oid'=>$openid));
	if($info['sure']==1){
		return false;
	}
	else if($info AND $info['sure']==0){
		return true;
	}
	 else{
		 return false;
	 }
}  
/**
 * 管理员操作 驳回用户请求
 * @param unknown $id
 * @return Ambigous <boolean, unknown>
 */
private function DboperateDeleteUserInfo($id){
	$t=pdo_delete('ptj_profile',array('id'=>$id));
	return $t;
}

//////////////////////////业务操作 分割线//////////////////////////
/**
 * 模板消息 
 * 接口已开放   
 * 
 */
private function SendTpl($openid,$info,$type,$res){
	load()->func('communication');
	require_once 'template_conf_ap.php';
	require_once 'template_conf_auth.php';
	require_once 'template_conf_re.php';
	require_once 'template_conf_cd.php';
	require_once 'template_conf_fl.php';
	 	
    global $_W;
	$access_token = WeAccount::token();

	$apply=json_decode($apply,true);
	//		$this->saveSettings($apply);
	$auth=json_decode($auth,true);
	//		$this->saveSettings(auth);
	$refu=json_decode($refu,true);
	//		$this->saveSettings($refu);
	$credit=json_decode($credit,true);
	//		$this->saveSettings($credit);
	$full=json_decode($full,true);
	//		$this->saveSettings($full);
	
	
	//post URL
	$url='https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$access_token;
	//POST data
	if($type=='apply'){
	$data=array(
		'touser'=>$openid,
		'template_id'=>$apply['apply_tid'],
		'url'=>$_W['siteroot'].'app/index.php?i=1&c=entry&do=ptjindex&m=hypernet_iptj',
		'topcolor'=>'#FF0000',
		'data'=>array(
		'first'=>array(
				'value'=>$apply['apply_first'],
				'color'=>$apply['apply_fcolor'],
			),
	   'keynote1'=>array(
			'value'=>$apply['apply_keynote1'], 
	   		'color'=>$apply['apply_kfcolor'],
		),
		'keynote2'=>array(
						'value'=>'请联系雇主确认....',
						'color'=>'#000',
				),
	  'remark'=>array(
	   	'value'=>$apply['apply_remark'],
	  	'color'=>$apply['apply_mkcolor'],
	   ),			
	)			
	);
	}
	else if($type=='auth'){
			$date=date('Y-m-d H:i');
		$data=array(
				'touser'=>$openid,
				'template_id'=>$auth['auth_tid'],
				'url'=>$_W['siteroot'].'app/index.php?i=1&c=entry&do=ptjindex&m=hypernet_iptj',
				'topcolor'=>'#FF0000',
				'data'=>array(
						'first'=>array(
								'value'=>$auth['auth_first'],
								'color'=>$apply['auth_first'],
						),
	                    'keyword1'=>array(
			                 'value'=>$info['name'], 
	   		                  'color'=>'#000',
		                       ),
		                 'keyword2'=>array(
						     'value'=>$info['phone'],
						     'color'=>'#000',
				              ),
						 'keyword3'=>array(
						     'value'=>$date,
						     'color'=>'#000',
				              ),
						'remark'=>array(
								'value'=>$auth['auth_remark'],
								'color'=>$auth['auth_mkcolor'],
						),
				)
		);
	}
	else if($type=='refuse'){
		$date=date('Y-m-d H:i');
				$data=array(
				'touser'=>$openid,
				'template_id'=>$refu['refu_tid'],
				'url'=>$_W['siteroot'].'app/index.php?i=1&c=entry&do=ptjindex&m=hypernet_iptj',
				'topcolor'=>'#FF0000',
				'data'=>array(
						'first'=>array(
								'value'=>$refu['refu_first'],
								'color'=>$refu['refu_fcolor'],
						),
	                    'keyword1'=>array(
			                 'value'=>$info['name'], 
	   		                  'color'=>'#000',
		                       ),
		                 'keyword2'=>array(
						     'value'=>$info['phone'],
						     'color'=>'#000',
				              ),
						 'keyword3'=>array(
						     'value'=>$date,
						     'color'=>'#000',
				              ),
						'remark'=>array(
								'value'=>$refu['refu_remark'],
								'color'=>$refu['refu_mkcolor'],
						),
				)
		);
	}
	else if($type=='credit1'){
		$date=date('Y-m-d H:i:s');
						$data=array(
				'touser'=>$openid,
				'template_id'=>$credit['credit_tid'],
				'url'=>$_W['siteroot'].'app/index.php?i=1&c=entry&do=ptjindex&m=hypernet_iptj',
				'topcolor'=>'#FF0000',
				'data'=>array(
						'first'=>array(
								'value'=>$credit['credit_first'],
								'color'=>$credit['credit_fcolor'],
						),
	                    'account'=>array(
			                 'value'=>$info['name'], 
	   		                  'color'=>'#000',
		                       ),
		                 'time'=>array(
						     'value'=>$date,
						     'color'=>'#000',
				              ),
						 'type'=>array(
						     'value'=>$credit['credit_type'],
						     'color'=>$credit['credit_tpcolor'],
				              ),
						  'creditChange'=>array(
						     'value'=>'支出',
						      'color'=>'#000'
						  ),
						  'number'=>array(
						    'value'=>'100',
							'color'=>'#000',
						  ),
						  'creditName'=>array(
						    'value'=>'账户积分',
							'color'=>'#000'
						  ),
						  'amount'=>array(
						    'value'=>$res,
							'color'=>'#000',
						  ),
						'remark'=>array(
								'value'=>$credit['credit_remark'],
								'color'=>$credit['crefit_mkcolor'],
						),
				)
		);
	}
	else if($type=='full'){
				$data=array(
				'touser'=>$openid,
				'template_id'=>$full['full_tid'],
				'url'=>$_W['siteroot'].'app/index.php?i=1&c=entry&do=ptjmyinfo&m=hypernet_iptj',
				'topcolor'=>'#FF0000',
				'data'=>array(
						'first'=>array(
								'value'=>$full['full_first'],
								'color'=>$full['full_fcolor'],
						),
	                    'keyword1'=>array(
			                 'value'=>$info['title'], 
	   		                  'color'=>'#000',
		                       ),
						'keyword2'=>array(
								'value'=>'待办',
 								'color'=>'red',
						),
						'remark'=>array(
								'value'=>$full['full_remark'],
								'color'=>$full['full_mkcolor'],
						),
				)
		);
	}	 
	else if($type=='first_third'){
		$data=array(
				'touser'=>$openid,
				'template_id'=>$full['full_tid'],
				'url'=>$_W['siteroot'].'app/index.php?i=1&c=entry&do=ptjmyinfo&m=hypernet_iptj',
				'topcolor'=>'#FF0000',
				'data'=>array(
						'first'=>array(
								'value'=>'请尽快去录取应答者，完成本次招募。',
								'color'=>'#000',
						),
						'keyword1'=>array(
								'value'=>$info['title'],
								'color'=>'#000',
						),
						'keyword2'=>array(
								'value'=>'待办',
 								'color'=>'red',
						),
	
						'remark'=>array(
								'value'=>'帖子有人认领啦,快去看看.',
								'color'=>'#0542FA',
						),
				)
		);
	}	
	else if($type=='second_third'){
		$data=array(
				'touser'=>$openid,
				'template_id'=>$full['full_tid'],
				'url'=>$_W['siteroot'].'app/index.php?i=1&c=entry&do=ptjmyinfo&m=hypernet_iptj',
				'topcolor'=>'#FF0000',
				'data'=>array(
						'first'=>array(
								'value'=>'请尽快去录取应聘者，完成本次招聘。',
								'color'=>'#000',
						),
						'keyword1'=>array(
								'value'=>$info['title'],
								'color'=>'#000',
						),
						'keyword2'=>array(
								'value'=>'待办',
 								'color'=>'red',
						),
	
						'remark'=>array(
								'value'=>'招聘帖子人数快满了,请速去验收.',
								'color'=>'#0542FA',
						),
				)
		);
	}
	else if($type=='admit'){
	$data=array(
		'touser'=>$openid,
		'template_id'=>$apply['apply_tid'],
		'url'=>$_W['siteroot'].'app/index.php?i=1&c=entry&do=ptjindex&m=hypernet_iptj',
		'topcolor'=>'#FF0000',
		'data'=>array(
		'first'=>array(
				'value'=>'congrats!你已被'.$info['name'].'招募',
				'color'=>'#FF0000',
			),
	   'keynote1'=>array(
			'value'=>$info['name'].'欢迎你的加入~', 
	   		'color'=>'#000',
		),
		'keynote2'=>array(
						'value'=>'请联系雇主确认....',
						'color'=>'#000',
				),
	  'remark'=>array(
	   	'value'=>'要愉快地玩耍哦~~~~',
	  	'color'=>'#000',
	   ),			
	)			
	);
	}
   return ihttp_post($url,json_encode($data));
}
}

