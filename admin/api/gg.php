<?php
if($egn=='gglist') {
	include("../cf_up.php");
	$result = send_post($mn_conf['aet'].'://'.$mn_conf['url'].':'.$mn_conf['port'].'/'.$mn_conf['install_wj'].'/guanggao.php',[]);
	if($result===false) $result='';
	exit($result);
}
if($egn=='update') {
	include("../MPHX/BL.php");
	include("../MPHX/SQ.php");
	include("../cf_up.php");
	$gxtj = array(
	    'url' => $_SERVER['HTTP_HOST'],
	    'authcode' => $authcode,
	    'ver' => $WEBQB,
	    );
	$result = send_post($mn_conf['aet'].'://'.$mn_conf['url'].':'.$mn_conf['port'].'/check.php',$gxtj);
	$query=json_decode($result, true);
	if($query['code']=="1") {
		//file_put_contents("gxwj.zip",$blh);
		copy($query['file'],'gxwj.zip');
		$url = $_SERVER['PHP_SELF'];
		$filenamet= str_ireplace('ajax.php', '', $url);
		$filename= str_ireplace('/', '', $filenamet);
		//exit('{"code":"'.$filename.'"}');
		if(rename('../'.$filename.'/','../admin/')) {
			$file = "gxwj.zip";
			$outPath = "../";
			$zip = new ZipArchive();
			$openRes = $zip->open($file);
			if ($openRes === TRUE) {
				$zip->extractTo($outPath);
				$zip->close();
				unlink($file);
				rename('../admin/','../'.$filename.'/');
				$path='../update/update.sql';
				if(file_exists($path)) {
					function insert($file,$database,$name,$root,$pwd) {
						header("Content-type: text/html; charset=utf-8");
						$_sql = file_get_contents($file);
						$_arr = explode(';', $_sql);
						$_mysqli = new mysqli($name,$root,$pwd,$database);
						//第一个参数为域名，第二个为用户名，第三个为密码，第四个为数据库名字 
						if (mysqli_connect_errno()) {
							exit('连接数据库出错');
						} else {
							$_mysqli->query('set names utf8;');
							foreach ($_arr as $_value) {
								$_mysqli->query($_value.';');
							}
						}
						$_mysqli->close();
						$_mysqli = null;
					}
					insert($path,$dbconfig['user'],$dbconfig['host'],$dbconfig['dbname'],$dbconfig['pwd']);
					unlink($path);
					@rmdir('../update/');
				}
				json_exit('更新成功～请手动刷新页面');
			} else {
				json_exit('解压失败');
			}
		} else {
			json_exit('失败');
		}
	} else {
		json_exit('无法更新');
	}
	return;
}
return;
