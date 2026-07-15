<?php
if($egn=='ftpsc') {
	//删除文件/目录
	$lx=daddslashes($_POST['lx'] ?? '');
	$filepath=daddslashes($_POST['path'] ?? '');
	$filename=trim(daddslashes($_POST['name'] ?? ''));
	if(substr($filepath,0,1)!='/')exit('{"code":"目录格式错误！"}');
	if(strpos($filename,'/')!==false)exit('{"code":"文件名格式错误！"}');
	if($filename=='.user.ini' && $lx=='file')exit('{"code":"错误！您在删除配置文件(.user.ini)！这是不被允许的！"}');
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	if($lx=='file') {
		$r_data = $api->delwj($os_xt.$yhc['sqldz'].$filepath.$filename);
	} else {
		$r_data = $api->delwjj($filepath,$filename,[$yhc['btid'],$os_xt.$yhc['sqldz']]);
	}
	$r_data = $r_data ?: [];
	logjl($yhc['user'],'文件删除','删除了文件'.$filepath.$filename,(($r_data['status']??'')=='true'?'删除成功':'删除失败'),$DB);
	echo ($r_data['status']??'')=='true' ? '{"code":"删除成功"}' : '{"code":"'.($r_data['msg']??'未知错误').'"}';
	exit;
	return;
}
if($egn=='ftpscxz') {
	//删除多个文件/目录
	$idsze=daddslashes($_POST['idsz'] ?? '');
	//被删除的文件(数组)
	$path=daddslashes($_POST['path'] ?? '');
	if(substr($path,0,1)!='/')exit('{"code":"目录格式错误！"}');
	if(empty($idsze))exit('{"code":"您未选择需要删除的文件或目录！"}');
	if(in_array('.user.ini',$idsze))exit('{"code":"错误！您在删除配置文件(.user.ini)！这是不被允许的！"}');
	if($path==null)exit('{"code":"目录错误！"}');
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$wjne=json_encode($idsze,256);
	$r_data = $api->xzdelwj($path,$wjne,[$yhc['btid'],$os_xt.$yhc['sqldz']]);
	//print_r($wjlj);
	if($r_data['status']??false)json_exit('删除成功'); else json_exit($r_data['msg']??'');
	return;
}
if($egn=='xjwj') {
	//新建文件
	$name=daddslashes($_POST['wjname'] ?? '');
	$ml=daddslashes($_POST['ml'] ?? '');
	if(substr($ml,0,1)!='/')exit('{"code":"目录格式错误！"}');
	if(strpos($name,'/')!==false)exit('{"code":"文件名格式错误！"}');
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$abc=$api->xjwj($os_xt.$yhc['sqldz'].$ml.$name);
	$abc = $abc ?: [];
	logjl($yhc['user'],'新建文件','新建了文件'.$ml.$name,(($abc['msg']??'')=='success'?'新建成功':'新建失败'),$DB);
	json_exit($abc['msg']??'');
	return;
}
if($egn=='xjwjj') {
	//新建文件夹
	$name=daddslashes($_POST['wjname']);
	$ml=daddslashes($_POST['ml']);
	if(substr($ml,0,1)!='/')exit('{"code":"目录格式错误！"}');
	if(strpos($name,'/')!==false)exit('{"code":"文件名格式错误！"}');
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$abc=$api->xjwjj($os_xt.$yhc['sqldz'].$ml.$name);
	$abc = $abc ?: [];
	logjl($yhc['user'],'新建目录','新建了目录'.$ml.$name,(($abc['msg']??'')=='success'?'新建成功':'新建失败'),$DB);
	json_exit($abc['msg']??'');
	return;
}
if($egn=='hqwj') {
	//获取文件内容
	$lw=daddslashes($_POST['wj'] ?? '');
	if(substr($lw,0,1)!='/')exit('{"code":"文件不存在！"}');
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$abc=$api->hqwjnr($os_xt.$yhc['sqldz'].$lw) ?: [];
	exit($abc['data']??'');
	return;
}
if($egn=='setwj') {
	//修改文件内容
	$lm=daddslashes($_POST['wj'] ?? '');
	if(substr($lm,0,1)!='/')exit('{"code":"被修改文件不存在！"}');
	$nr=$_POST['nr'] ?? '';
	//赋值时不对文件内容进行转义
	if(strpos($lm,'.user.ini')!==false)exit('{"code":"错误！您在修改配置文件(.user.ini)！这是不被允许的！"}');
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$abc=$api->setwj(array($nr,$os_xt.$yhc['sqldz'].$lm));
	$abc = $abc ?: [];
	logjl($yhc['user'],'修改文件','修改了文件'.$lm,'修改成功',$DB);
	json_exit($abc['msg']??'');
	return;
}
if($egn=='hqdx') {
	//获取文件大小
	$lw=daddslashes($_POST['dw'] ?? '');
	if(substr($lw,0,1)!='/')exit('{"code":"文件所在目录错误！"}');
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$abc=$api->hqsize($os_xt.$yhc['sqldz'].$lw) ?: [];
	exit('{"code":"'.($abc['size']??'0').'"}');
	return;
}
if($egn=='setname') {
	//重命名文件
	$name=daddslashes($_POST['wjmc'] ?? '');
	$jmc=daddslashes($_POST['wjjm'] ?? '');
	$lj=($_POST['lj'] ?? '')=='' ? '/' : daddslashes($_POST['lj']);
	if(substr($lj,0,1)!='/')exit('{"code":"目录格式错误！"}');
	if(strpos($jmc,'/')!==false)exit('{"code":"旧文件名格式错误！"}');
	if(strpos($name,'/')!==false)exit('{"code":"新文件名格式错误！"}');
	if($jmc=='.user.ini')exit('{"code":"错误！您在重命名配置文件(.user.ini)！这是不被允许的！"}');
	if($name=='.user.ini')exit('{"code":"错误！该文件(.user.ini)已存在！"}');
	if($name==null)exit('{"code":"文件名禁止为空！"}');
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$abc=$api->cxname(array($os_xt.$yhc['sqldz'],$lj,$jmc,$name));
	$abc = $abc ?: [];
	logjl($yhc['user'],'重命名','将'.$jmc.'重命名为'.$name,'重命名成功',$DB);
	json_exit($abc['msg']??'');
	return;
}
if($egn=='file_upload_size') {
	//判断文件是否为断点续传
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$path=$_POST['htl'] ?? '';
	$file_name=trim($_POST['fename'] ?? '');
	if(substr($path,0,1)!='/')exit('{"code":"目录格式错误！"}');
	if($file_name==='' || strpos($file_name,'/')!==false)exit('{"code":"文件名格式错误！"}');
	if($file_name==='.user.ini')exit('{"code":"禁止上传.user.ini配置文件！"}');
	$abcm=$api->fileupa($os_xt.$yhc['sqldz'].$path.($_POST['fename']??'').'.'.($_POST['size']??'0').'.upload.tmp') ?: [];
	$asei=($abcm['status']??false) ? ($abcm['msg']['size']??0) : 0;
	exit(json_encode(['code'=>1,'size'=>$asei]));
}
if($egn=='fileupload') {
	//上传文件
	if(substr(($_POST['htl']??''),0,1)!='/')exit(json_encode(['error'=>1,'size'=>4,'msg'=>'目录格式错误！']));
	if(strpos(($_POST['tempfilename']??''),'/')!==false)exit(json_encode(['error'=>1,'size'=>4,'msg'=>'上传的文件名格式错误！']));
	
	if(in_array(($_POST['tempfilename']??''),['.user.ini','.user.ini.upload.tmp']))exit(json_encode(['error'=>1,'size'=>4,'msg'=>'禁止上传.user.ini配置文件！']));
	
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	if(!isset($_FILES['file']) || $_FILES['file']==null)exit(json_encode(['error'=>1,'size'=>4,'msg'=>'上传的文件不能为空']));
	$websize=json_decode($yhc['hxa'],true);
	$mbsize=round(($_POST['zsize'] ?? 0)/1048576);
	if($mbsize>$websize['max'])exit(json_encode(['error'=>1,'size'=>4,'msg'=>'错误！上传的文件大于您的最大可用网页空间！故无法上传此文件']));
	if($websize['max']<=$websize['dq'])exit(json_encode(['error'=>1,'size'=>4,'msg'=>'错误！网页空间已满！']));
	if($mbsize>$websize['max']-$websize['dq'])exit(json_encode(['error'=>1,'size'=>4,'msg'=>'错误！上传的文件大于现在您当前可使用的网页空间！请清除空间至剩余'.$mbsize.'MB后再试']));
	$abc=$api->fileups($os_xt.$yhc['sqldz'].($_POST['htl']??''),$_FILES['file']??[],$_POST['fesw']??'',$_POST['tempfilename']??'',$_POST['zsize']??'');
	if(is_numeric($abc)) {
		exit(json_encode(['error'=>0,'size'=>$abc]));
	} else {
		exit(json_encode(['error'=>1,'size'=>1,'msg'=>'上传成功！']));
	}
}
if($egn=='listfile') {
	$sorting=($_POST['sortOrder']??'')=='asc' ? 'False' : 'True';
	//顺序或倒序
	$paixu=$_POST['sort']??'';
	$paixu=$paixu=='type' ? 'name' : $paixu;
	//排序字段
	$pagesize=$_POST['limit']??'';
	$page=$_POST['page']??'';
	$path=$_POST['path']??'';
	if(substr($path,0,1)!='/')exit('{"code":"目录格式错误！"}');
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$contents=$api->GetLogshqwjlo($os_xt.$yhc['sqldz'].$path,$sorting,$paixu,$pagesize,$page) ?: [];
	
	//当前目录下所有文件
	if(($contents['PATH']??'')==$conf['hxi'] || ($contents['PATH']??'')==$conf['hxo']) {
		$contents=$api->GetLogshqwjlo($os_xt.$yhc['sqldz'],$sorting,$paixu,$pagesize,$page);
		$paths='/';
	} else {
		$paths=$path;
	}
	$dir=dirfiles($contents['DIR']??[],'dir');
	$file=dirfiles($contents['FILES']??[],'file');
	$dirfile=array_merge($dir['file'],$file['file']);
	//合并数组
	$zzbds=preg_match('/共(\d+)条/', $contents['PAGE']??'', $matches);
	$val=(int)($matches[1]??0);
	if($val===1 && empty($dirfile))$val=0;
	$data=array("total"=>$val,"path"=>$paths);
	$data["rows"]=$dirfile;
	exit(json_encode($data,256));
	return;
}
if($egn=='filecp') {
	//这里不使用宝塔自带的多文件复制，因为标记功能1个主机复制文件另外一个主机粘贴文件会出现跨站点复制文件
	$yfile=$_POST['yfile'] ?? [];
	$ypath=$_POST['ypath'] ?? '';
	$xpath=$_POST['xpath'] ?? '';
	$type=$_POST['type'] ?? '';
	//1为复制，2为剪切
	if(empty($yfile))exit('{"qk":"4","code":"错误！您未选择任何文件！"}');
	if(substr($ypath,0,1)!='/')exit('{"qk":"4","code":"原目录格式错误！"}');
	if(substr($xpath,0,1)!='/')exit('{"qk":"4","code":"新的目录格式错误！"}');
	if(in_array('.user.ini',$yfile))exit('{"qk":"4","code":"错误！您在操作根目录的配置文件(.user.ini)！这是不被允许的！"}');
	if(empty($yfile) || empty($ypath) || empty($xpath) || empty($type))exit('{"qk":"4","code":"错误！禁止留空！"}');
	if($ypath==$xpath)exit('{"qk":"4","code":"错误！原目录与粘贴目录不能相同！"}');
	if($xpath!='/') {
		foreach ($yfile as $val) {
			if(substr($xpath,0,mb_strlen($ypath.$val.'/'))==$ypath.$val.'/')exit('{"qk":"4","code":"错误的逻辑，从'.$ypath.$val.'粘贴到'.$xpath.'有包含关系，存在无限循环复制风险！"}');
		}
	}
	include("../class.php");
	$yes=0;
	$no=0;
	$api = new bt_api($btipe,$btkeye);
	foreach ($yfile as $val) {
		$abc=$api->filecopy($os_xt.$yhc['sqldz'].$ypath.$val,$os_xt.$yhc['sqldz'].$xpath.$val) ?: [];
		if($abc['status']??false) {
			$yes++;
		} else {
			$no++;
		}
	}
	if($type==2) {
		$api->xzdelwj($ypath,json_encode($yfile,256),[$yhc['btid'],$os_xt.$yhc['sqldz']]);
		//删除原文件
		$czname='剪切';
	} else {
		$czname='复制';
	}
	if($no==0) {
		$msg=$czname.'成功！';
		$qk=1;
	} else {
		$msg="<span class='text-success'>{$czname}成功{$yes}个文件，</span>{$czname}失败{$no}个文件";
		$qk=4;
	}
	exit('{"qk":"'.$qk.'","code":"'.$msg.'"}');
	logjl($yhc['user'],'文件操作',$czname.'了'.count($yfile).'个文件',($no==0?'操作成功':'部分失败'),$DB);
	return;
}
if($egn=='fileys') {
	//文件压缩
	$filename=$_POST['file'] ?? [];
	$dpath=$_POST['dpath'] ?? '';
	$type=$_POST['type'] ?? '';
	$path=$_POST['path'] ?? '';
	if(substr($dpath,0,1)!='/')exit('{"qk":"4","code":"目录格式错误！"}');
	if(substr($path,0,1)!='/')exit('{"qk":"4","code":"目录格式错误！"}');
	if(empty($filename) || empty($dpath) || empty($type) || empty($path))exit('{"qk":"4","code":"错误！禁止留空！"}');
	include("../class.php");
	$api = new bt_api($btipe,$btkeye);
	$zfc='';
	foreach ($filename as $val) {
		if($zfc=='') {
			$zfc.=$val;
		} else {
			$zfc.=','.$val;
		}
	}
	$data=$api->fileysr($zfc,$os_xt.$yhc['sqldz'].$dpath,$type,$os_xt.$yhc['sqldz'].$path) ?: [];
	if($data['status']??false) {
		$qk=1;
	} else {
		$qk=4;
	}
	exit('{"qk":"'.$qk.'","code":"'.($data['msg']??'').'"}');
	logjl($yhc['user'],'文件压缩','压缩了文件到'.$dpath,(($data['status']??false)?'压缩成功':'压缩失败'),$DB);
	return;
}
