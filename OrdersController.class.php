<?php
namespace Home\Controller;

/**
 * 首页控制台
 *
 */

class OrdersController extends CommonController{ 
	function __construct()
	{
		parent::__construct();
		$this->usid = $this->check();
		$this->comid = session("comid");
	}
	public function showOrders()
	{
		$usid = $this->usid;
		$comid = $this->comid;
		$page = I("get.page",1);
		$get = I("get.");
		if($get['txtProductName']){
			$where['b.title'] = array("like","%".$get['title']."%");
		}
		if($get['ddlOrderStatus']>0){
			$where['a.sta'] = array("eq",intval($get['ddlOrderStatus']));
		}
		if($get['ddlTime']>0){
			switch ($get['ddlTime']) {
				case 1:
				$where['a.indate'] = array("gt",getDateArea(1));
				break;
				case 2:
				$date = getDateArea(2);
				$where['a.indate'] = array("between",array($date[0],$date[1]));
				break;
				case 3:
				$where['a.indate'] = array("gt",getDateArea(4));
				break;
				default:
				null;
				break;
			}
		}
		if($get['txtOrderNum']){
			$where['a.sn'] = array("eq",intval($get['txtOrderNum']));
		}
		if($get['txtUserName']){
			$where['a.hd'] = array("eq",intval($get['txtUserName']));
		}
		if($get['txtconsignee']){
			$where['a.un'] = array("eq",intval($get['txtconsignee']));
		}
		if($get['txtStartTime'] && $get['txtEndTime']){
			$where['a.indate'] = array("between",array(date("Y-m-d H:i:s",strtotime($get['txtStartTime'])),date("Y-m-d H:i:s",strtotime($get['txtEndTime']))));
		}
		if($get['uid']){
			$where['a.usid'] = $get['uid'];
		}
		$where['a.comid'] = $comid;
		$where['a.ddtype'] = 1;
		$field = "a.sn,a.sta,a.zfje,a.un,a.hd,a.indate,a.qbzf,a.syzf,a.id";
		$pages = get_page($get,$page,$orders = M("gnpDdXq a")->join("gnp_cp b on a.cpid=b.id")->field($field)->where($where)->count());
		$orders = M("gnpDdXq a")->join("gnp_cp b on a.cpid=b.id")->field($field)->where($where)->page($page,10)->order("a.id desc")->select();
		$this->assign("orders",$orders);
		$this->assign("pages",$pages);
		$this->assign("page",$page);
		$this->assign("comtxt",session("comtxt"));
		$this->display();
	}
	public function orderDetail()
	{
		$usid = $this->usid;
		$oid = I("get.oid");
		if(!$oid){
			$this->redirect("showOrders");
		}
		$where['a.id'] = array('eq',$oid);
		$field = "a.cpid,a.sn,a.indate,a.sta,a.un,a.hd,a.dz,a.ddate,a.cdate,a.kdun,a.kddh,a.kdbh,a.ddate,a.sx,a.zfje,a.sl,a.wxzf,b.nick,a.qbzf,a.pj,a.jg";
		$detail = M("gnpDdXq a")->join("gnp_us b on a.usid=b.id")->field($field)->where($where)->find();
		$detail['sx'] = implode(',',json_decode($detail['sx'],true));
		$good = M("gnpCp")->field()->where("id=$detail[cpid]")->find();
		$url = "https://www.kuaidi100.com/query?type=$detail[kdbh]&postid=$detail[kddh]";
		$postCode = file_get_contents($url);
		$this->assign("detail",$detail);
		$this->assign("good",$good);
		$this->assign("postCode",$postCode);
		$this->display();
		echo $url;
	}
	public function shipmenu()
	{
		$usid = $this->usid;
		$oid = I("get.oid");
		$where['id'] = array('eq',$oid);
		$field = "a.cpid,a.sn,a.indate,a.sta,a.un,a.hd,a.dz,a.ddate,a.cdate,a.kdun,a.kddh,a.kdbh,a.ddate,a.sx,a.zfje,a.sl,a.wxzf,";
		$detail = M("gnpDdXq a")->field($field)->where($where)->find();
		$ships = M("gnp_kd100")->select();
		$detail['sx'] = implode(',',json_decode($detail['sx'],true));
		$good = M("gnpCp")->field()->where("id=$detail[cpid]")->find();
		$url = "https://www.kuaidi100.com/query?type=$detail[kdbh]&postid=$detail[kddh]";
		$postCode = file_get_contents($url);
		if($postCode){
			$this->assign("detail",$detail);
			$this->assign("ships",$ships);
			$this->assign("good",$good);
			$this->assign("postCode",$postCode);
			$this->display();
		}
	}
	public function orders()
	{
		$usid = $this->usid;
		$post = I("post.");
		if($post){
			$oid = $post['oid'];
			if($post['act'] == "fahuo"){
				$data['kdbh'] = $post['ddlLogistics$ddlSelect'];
				$data['kddh'] = str_replace(' ','',$post['txtBill']);
				$ship = m("gnp_kd100")->where("bh='$data[kdbh]'")->find();
				$data['kdun'] = $ship['un'];
				$data['fee'] = $post['txtMoney'];
				$data['sta'] = 3;
				$data['dusid'] = $usid;
				M("gnpDdXq")->where("id=$oid")->save($data);
			}
			$url = U("orders/orderdetail",array("oid"=>$oid));
			echo "<script>top.location.href='$url'</script>";
		}
	}
}