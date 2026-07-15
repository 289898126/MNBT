<?php
if($egn=='logsc') {
	$id=$_POST['id'];
	if($DB->query_prepare("DELETE FROM MN_log WHERE id=? limit 1", [$id]))json_exit('删除成功'); else json_exit('删除失败'.$DB->error());
	return;
}
if($egn=='logscxz') {
	$idsz=$_POST['idsz'];
	preg_match_all("/[,]{1}/",$idsz,$arrNum);
	$szgs=count($arrNum[0]);
	$str_arr = explode(',',$idsz);
	$er=0;
	$scqke=0;
	$scqkr=0;
	for ($er=0;$er<$szgs;$er++) {
		$id = $str_arr[$er];
		if($DB->query_prepare("DELETE FROM MN_log WHERE id=? limit 1", [$id])) $scqke++; else $scqkr++;
	}
	json_exit($scqke, ['codr' => $scqkr]);
	return;
}
if($egn=='listlog') {
    $sorting=strtoupper(($_POST['sortOrder']??''))==='DESC'?'DESC':'ASC';
    $paixu=preg_replace('/[^a-zA-Z0-9_]/','',$_POST['sort']??'id')?:'id';
    $pagesize=intval($_POST['limit']);
    $pageu=(intval($_POST['page'])-1) * $pagesize;
    $where=json_decode($_POST['where'],true);
    $pswhere='';
    $param_arr=[];
    if($where && $where['name']!=false && $where['type']!=false && $where['value']!=false){
        if($where['type']!='1' && $where['type']!='2')exit(json_encode(['code'=>4,'msg'=>'搜索方式错误！']));
        $zdm=['czuser','lx','lr','qk'];
        if(!in_array($where['name'],$zdm))exit(json_encode(['code'=>4,'msg'=>'不存在的搜索字段！']));
        $val=$where['value'];
        $pswhere='and '.$where['name'].($where['type']=='1'?"=?":" LIKE ?");
        $param_arr[]=$where['type']=='1'?$val:'%'.$val.'%';
    }
    $countdata=$DB->count_prepare("SELECT count(*) from MN_log WHERE 1 {$pswhere}", $param_arr);
    $data=["total"=>$countdata];
    $data["rows"]=$DB->get_all_prepare("SELECT * FROM MN_log WHERE 1 {$pswhere} order by $paixu $sorting limit $pageu,$pagesize", $param_arr);
    exit(json_encode($data));
	return;
}
if($egn=='logclear') {
    if($DB->query_prepare("DELETE FROM MN_log"))json_exit('清空成功'); else json_exit('清空失败');
	return;
}
return;
