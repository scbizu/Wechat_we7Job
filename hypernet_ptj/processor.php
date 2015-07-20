<?php
/**
 * Job模块处理程序
 *
 * @author Hypernet
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class hypernet_iptjModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微擎文档来编写你的代码
		if($content){
				if($content){
			$arr=array(
					'demo'=>'hellome!'
			);
			WeiXinAccount::sendTplNotice($this->message['from'],'0siCXaQSMjvIuGA7yovtY7DmjcFQKz5owFZ49-3tUuI',$arr,'');
		}
		}

	}
}
