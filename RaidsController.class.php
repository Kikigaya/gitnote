<?php
namespace Home\Controller;

/**
 * 首页控制台
 *
 */

class RaidsController extends CommonController{ 
	function __construct()
	{
		parent::__construct();
		$this->usid = $this->check();
		$this->comid = session("comid");
	}
	public function showRaids()
	{
		$usid = $this->usid;
		$comid = $this->comid;
		$page = I("get.page",1);
		$get = I("get.");
		// if($get['txtProductName']){
		// 	$where['b.title'] = array("like","%".$get['title']."%");
		// }
		// if($get['ddlOrderStatus']>0){
		// 	$where['a.sta'] = array("eq",intval($get['ddlOrderStatus']));
		// }
		// if($get['ddlTime']>0){
		// 	switch ($get['ddlTime']) {
		// 		case 1:
		// 			$where['a.indate'] = array("gt",getDateArea(1));
		// 			break;
		// 		case 2:
		// 			$date = getDateArea(2);
		// 			$where['a.indate'] = array("between",array($date[0],$date[1]));
		// 			break;
		// 		case 3:
		// 			$where['a.indate'] = array("gt",getDateArea(4));
		// 			break;
		// 		default:
		// 			null;
		// 			break;
		// 	}
		// }
		// if($get['txtOrderNum']){
		// 	$where['a.sn'] = array("eq",intval($get['txtOrderNum']));
		// }
		// if($get['txtUserName']){
		// 	$where['a.hd'] = array("eq",intval($get['txtUserName']));
		// }
		// if($get['txtconsignee']){
		// 	$where['a.un'] = array("eq",intval($get['txtconsignee']));
		// }
		// if($get['txtStartTime'] && $get['txtEndTime']){
		// 	$where['a.indate'] = array("between",array(date("Y-m-d H:i:s",strtotime($get['txtStartTime'])),date("Y-m-d H:i:s",strtotime($get['txtEndTime']))));
		// }
		// if($get['uid']){
		// 	$where['a.usid'] = $get['uid'];
		// }
		$where['b.comid'] = $comid;
		$where['a.pdtype'] = 1;
		$field = "a.id,a.pds,a.wcs,a.indate,a.sta,b.title,c.nick,a.usid";
		$pages = get_page($get,$page,M("gnpDdPd a")->join("gnp_dd_xq b on b.pdid=a.id")->join("gnp_us c on a.usid=c.id")->field($field)->where($where)->count());
		$raids = M("gnpDdPd a")->join("gnp_dd_xq b on b.pdid=a.id")->join("gnp_us c on a.usid=c.id")->field($field)->where($where)->page($page,10)->order("a.id desc")->select();
		$this->assign("raids",$raids);
		$this->assign("pages",$pages);
		$this->assign("page",$page);
		$this->assign("comtxt",session("comtxt"));
		$this->display();
	}
	public function raidDetail()
	{
		$usid = $this->usid;
		$rid = I("get.rid");
		if(!$rid){
			$this->redirect("showRaids");
		}
		$where['a.id'] = array('eq',$rid);
		$field = "a.sta,b.nick,a.indate,c.title";
		$detail = M("gnpDdPd a")->join("gnp_us b on a.usid=b.id")->join("gnp_cp c on a.cpid=c.id")->field($field)->where($where)->find();
		$field = "a.sl,a.jg,b.nick,b.protrait,a.un,a.hd,a.dz,a.kdun,a.kdbh,a.sta";
		$members = M("gnp_dd_xq a")->join('gnp_us b on a.usid=b.id')->field($field)->where("a.pdid=$rid")->order("a.id asc")->select();
		$this->assign("detail",$detail);
		$this->assign("members",$members);
		$this->display();
	}
}