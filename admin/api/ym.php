<?php
if($egn=='addym') {
	$ip=daddslashes($_POST['url']);
	$dk=daddslashes($_POST['bt']);
	$key=daddslashes($_POST['jg']);
	$kg=daddslashes($_POST['kg']);
	$ymjs=daddslashes($_POST['ymjs']);
	$rowe=$DB->get_row_prepare("SELECT * FROM MN_ym WHERE 1 order by id desc limit 1");
	$id=$rowe['id']+1;
	logjl($user,'添加域名','添加了'.$url,'添加成功',$DB);
	if($DB->query_prepare("INSERT INTO `MN_ym` (`id`, `url`, `btdh`, `jg`, `date`, `js`, `json`, `qk`) VALUES (?,?,?,?,?,?,?,?)", [$id, $ip, $dk, $key, $date, $ymjs, '[]', $kg]))json_exit('添加成功'); else json_exit('添加失败'.$DB->error());
	return;
}
if($egn=='xgym') {
	$id=daddslashes($_POST['id']);
	$js=daddslashes($_POST['js']);
	$jg=daddslashes($_POST['jg']);
	$kg=daddslashes($_POST['kg']);
	logjl($user,'修改宝塔','对ID为'.$id.'的宝塔进行了修改','修改成功',$DB);
	if($DB->query_prepare("update `MN_ym` set `js` =?,`jg` =?,`qk` =? where `id`=?", [$js,$jg,$kg,$id]))json_exit('修改成功'); else json_exit('修改失败'.$DB->error());
	return;
}
if($egn=='ymsc') {
	$id=$_POST['id'];
	logjl($user,'删除域名','删除了ID为'.$id.'的域名','删除成功',$DB);
	if($DB->query_prepare("DELETE FROM MN_ym WHERE id=? limit 1", [$id]))json_exit('删除成功'); else json_exit('删除失败'.$DB->error());
	return;
}
if($egn=='ymscxz') {
	$idsz=$_POST['idsz'];
	$scqkr=0;
	$scqke=0;
	foreach($idsz as $id) {
		logjl($user,'删除域名','ID为'.$id.'的域名被删除了','删除成功',$DB);
		if($DB->query_prepare("DELETE FROM MN_ym WHERE id=? limit 1", [$id])) $scqke++; else $scqkr++;
	}
	json_exit($scqke, ['codr' => $scqkr]);
	return;
}
if($egn=='listym') {
	//域名列表
	$sorting=strtoupper($_POST['sortOrder']??'')==='DESC'?'DESC':'ASC';
	$paixu=preg_replace('/[^a-zA-Z0-9_]/','',$_POST['sort']??'id')?:'id';
	$pagesize=intval($_POST['limit']);
	$pageu=(intval($_POST['page'])-1) * $pagesize;
	$countdata=$DB->count_prepare("SELECT count(*) from MN_ym WHERE 1");
	$data=["total"=>$countdata];
	$data["rows"]=$DB->get_all_prepare("SELECT * FROM MN_ym order by $paixu $sorting limit $pageu,$pagesize");
	exit(json_encode($data));
	return;
}
return;
