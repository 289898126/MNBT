<?php
if($egn=='xtxf') {
	include("./class.php");
	$xf_xx=json_decode($_POST['xx']);
	foreach($xf_xx as $val) {
		if($val=='1') {
            $sql=[];
			$all_bt=$DB->get_all_prepare("SELECT * FROM MN_bt where `qk`<>'false'");
			foreach ($all_bt as $res) {
				//修复网站/修复FTP/数据库ID
                $bt_dh=$res['btdh'];
                $btipe=($res['ptl']=='true'?'https':'http').'://'.$res['btip'].':'.$res['btdk'];
                $btkeye=$res['btmy'];
                $api = new bt_api($btipe,$btkeye);
                $r_sites = $api->sjlist('sites');
                $r_ftps = $api->sjlist('ftps');
                $r_databases = $api->sjlist('databases');

                foreach($r_sites['data'] as $v) {
                    $sql[]="update `MN_zj` set `btid` ='{$v['id']}' where `sqldz`='{$v['name']}' and `ssbt`='{$res['btdh']}'";
                }

                foreach($r_ftps['data'] as $v) {
                    $sql[]="update `MN_zj` set `ftpid` ='{$v['id']}' where `user`='{$v['name']}' and `ssbt`='{$res['btdh']}'";
                }

                foreach($r_databases['data'] as $v) {
                    $sql[]="update `MN_zj` set `hxd` ='{$v['id']}' where `user`='{$v['name']}' and `ssbt`='{$res['btdh']}'";
                }
			}
            $sql_text=implode('; ',$sql);
            $DB->query_multi($sql_text);
		} elseif($val=='3') {
			if(file_exists('../user/cs.php') || file_exists('../user/mysql/qadmin.php')) {
				@unlink('../user/cs.php');
				//无用文件删除
				@unlink('../user/mysql/qadmin.php');
				//旧的数据库操作文件删除
				@rmdir('../user/mysql');
				//旧的数据库操作文件删除
				@unlink('../xy.php');
				//协议文件名已经从V1.6版本后改名为xy.html
				@unlink('./log.php');
				//log日志列表文件删除，因为日志功能已暂时废弃
			}
		}
	}
	//程序配置文件修改
	include("../cf_up.php");
	if($_POST['xe']==$mn_conf['xf']['gne']) {
		$mn_conf['xf']['qk']=0;
		$kr_sxy=ary_asd($mn_conf);
		file_put_contents("../cf_up.php",'<?php $mn_conf=array('.$kr_sxy.');?>');
	}
	json_exit('修复完成');
}
return;
