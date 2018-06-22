<?php
namespace Home\Controller;

/**
 * 首页控制台
 *
 */

class MarketingController extends CommonController{ 
	function __construct()
	{
		parent::__construct();
		$this->usid = $this->check();
		$this->comid = session("comid");
	}
	public function OrdersSettlements()
	{
		$usid = $this->usid;
		$comid = $this->comid;
		$page = I("get.page",1);
		$where['gsid'] = $comid;
		$type = I('get.t');
		$isdls = M("gnp_daili_us")->where("comid=$comid")->find();
		if($type==3 && $isdls){
			$where['gstype'] = 3;
		}else{
			$where['gstype'] = 2;
		}
		$pages = get_page($get,$page,M("gnp_ticheng")->field($field)->where($where)->count());
		$list = M("gnp_ticheng")->where($where)->page($page,10)->order("id desc")->select();
		$this->assign("isdls",$isdls);
		$this->assign("list",$list);
		$this->assign("page",$page);
		$this->assign("pages",$pages);
		$this->display();
	}
}