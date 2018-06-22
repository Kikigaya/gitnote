<?php
namespace Home\Controller;

/**
 * 首页控制台
 *
 */

class UsersController extends CommonController{ 
	function __construct()
	{
		parent::__construct();
		$this->usid = $this->check();
		$this->comid = session("comid");
	}
	public function showUsers()
	{
		$uid = $this->usid;
		$comid = $this->comid;
		$page = I("get.page",1);
		$get = I("get.");
		if($get['name']){
			$where['a.nick'] = array("like","%$get[name]%");
		}
		if($get['phone']){
			$where['a.hd'] = array("eq",$get['phone']);
		}
		// if($get['dropBuyAmount']){
		// 	$where['dropBuyAmount'] = array("gt",$get['dropBuyAmount']);
		// }
		// if($get['txtCashLeft'] && $get['txtCashRight']){
		// 	$where['indate'] = array("between",array($get['txtCashLeft'],$get['txtCashRight']));
		// }
		// if($get['txtPointLeft'] && $get['txtPointRight']){
		// 	$where['edate'] = array("between",array($get['txtPointLeft'],$get['txtPointRight']));
		// }
		$where['b.comid'] = $comid;
		$pages = get_page($get,$page,$orders = M("gnpUs a")->join("gnp_us_gs b on b.xsid=a.id")->where($where)->count());
		$USER = M("gnpUs a");
		$users = $USER->join("gnp_us_gs b on b.xsid=a.id")->where($where)->page($page,10)->order("a.id desc")->select();
		$this->assign("users",$users);
		$this->assign("pages",$pages);
		$this->assign("page",$page);
		$this->assign("comtxt",session("comtxt"));
		$this->display();
	}
	public function showUser()
	{
		$usid = $this->usid;
		$uid = I("get.uid");
		$where['id'] = $uid;
		if($uid > 0){
			$user = M("gnpUs")->where($where)->find();
			$this->assign("user",$user);
			$this->display();
			$this->assign("comtxt",session("comtxt"));
		}else{
			$this->redirect("showUsers");
		}
	}
}