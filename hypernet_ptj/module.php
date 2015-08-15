<?php
/**
 * Job模块定义
 *
 * @author Hypernet
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class hypernet_iptjModule extends WeModule {
	public function fieldsFormDisplay($rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return '';
	}

	public function fieldsFormSubmit($rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
	}

	public function settingsDisplay($settings) {
		global $_W, $_GPC;
		$proot=$_W['attachurl'];
		//点击模块设置时将调用此方法呈现模块设置页面，$settings 为模块设置参数, 结构为数组。这个参数系统针对不同公众账号独立保存。
		//在此呈现页面中自行处理post请求并保存设置参数（通过使用$this->saveSettings()来实现）
		load()->func('tpl');


			//字段验证, 并获得正确的数据$dat
			
			$default='照片要求格式为横版,最佳像素为320px * 180px';				
			$dat1=$_GPC['ImageUrl1'];
			if($dat1!=$default){
            $this->setpic(0, $dat1,$proot);
			$this->saveSettings($dat1);
			}
			
			$dat2=$_GPC['ImageUrl2'];
			
			if($dat2!=$default){
				$this->setpic(1, $dat2,$proot);				
			    $this->saveSettings($dat2);
			}
			$dat3=$_GPC['ImageUrl3'];
			if($dat3!=$default){
			$this->setpic(2, $dat3,$proot);
			$this->saveSettings($dat3);
			}
			$dat4=$_GPC['ImageUrl4'];
			if($dat4!=$default){
			$this->setpic(3, $dat4,$proot);
			$this->saveSettings($dat4);
			}
			$dat5=$_GPC['ImageUrl5'];
			if($dat5!=$default){
			$this->setpic(4, $dat5,$proot);
			$this->saveSettings($dat5);
			}


		//这里来展示设置项表单
		include $this->template('setting');
	}
   private function setpic($id,$img,$proot){
   	
   	pdo_update('ptj_pic',array('imgurl'=>$proot.$img),array('pid'=>$id));
   }
}
