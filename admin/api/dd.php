<?php
if($egn=='ddsc') {
	$id=$_POST['id'];
	logjl($user,'删除订单','删除了ID为'.$id.'的订单','删除成功',$DB);
	if($DB->query_prepare("DELETE FROM MN_dd WHERE id=? limit 1", [$id]))json_exit('删除成功'); else json_exit('删除失败'.$DB->error());
	return;
}
if($egn=='ddscxz') {
	$idsz=$_POST['idsz'];
	$scqkr=0;
	$scqke=0;
	foreach($idsz as $id) {
		logjl($user,'删除订单','ID为'.$id.'的订单被删除了','删除成功',$DB);
		if($DB->query_prepare("DELETE FROM MN_dd WHERE id=? limit 1", [$id])) $scqke++; else $scqkr++;
	}
	json_exit($scqke, ['codr' => $scqkr]);
	return;
}
if($egn=='listdd') {
	//订单列表
	$sorting=strtoupper($_POST['sortOrder']??'')==='DESC'?'DESC':'ASC';
	$paixu=preg_replace('/[^a-zA-Z0-9_]/','',$_POST['sort']??'id')?:'id';
	$pagesize=intval($_POST['limit']);
	$pageu=(intval($_POST['page'])-1) * $pagesize;
	$countdata=$DB->count_prepare("SELECT count(*) from MN_dd WHERE 1");
	$data=["total"=>$countdata];
	$data["rows"]=$DB->get_all_prepare("SELECT * FROM MN_dd order by $paixu $sorting limit $pageu,$pagesize");
	foreach ($data["rows"] as &$res) {
		if($res['lx']=='yjbs') {
			$ret=json_decode($res['cs'],true);
			$rcx=$ret['gmid'];
			$cres_row=$DB->get_row_prepare("SELECT * FROM MN_bs WHERE id=? limit 1", [$rcx]);
			$cres="一键部署-".$cres_row['name'];
		} else {
			$cres="域名购置";
		}
		$res["spname"]=$cres;
	}
	unset($res);
	exit(json_encode($data));
	return;
}
return;
