<?php
namespace Home\Controller;

/**
 * 首页控制台
 *
 */

class GnpData{ 
	function __construct()
	{
		$this->usid = $this->check();
		$this->comid = session("comid");
	}
	public function showOrders()
	{
		$usid = $this->usid;
		$comid = $this->comid;
		$page = I("get.p",1);
		$where['a.comid'] = $comid;
		$where['a.ddtype'] = 1;
		$field = "a.sn,a.sta,a.zfje,a.un,a.hd,a.indate,a.qbzf,a.syzf,a.id";
		$orders = M("gnpDdXq a")->field($field)->where($where)->page($page,10)->order("a.id desc")->select();
		$this->assign("orders",$orders);
		$this->assign("comtxt",session("comtxt"));
		$this->display();
	}
	public function orderDetail()
	{
		$usid = $this->usid;
		
	}
}