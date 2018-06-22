<?php
namespace Home\Controller;

/**
 * 首页控制台
 *
 */

class IndexController extends CommonController{ 
	function __construct()
	{
		parent::__construct();
	}
	public function index()
	{
		$usid = $this->check();
		$comid = session("comid");
		$statistics[1]['text'] = '昨天';
		$statistics[2]['text'] = '近3天';
		$statistics[3]['text'] = '近7天';
		$statistics[4]['text'] = '近30天';
		if($comid>0){
			$where['comid'] = session("comid");
			$where['ddtype'] = 1;
			//昨日订单
			$date = getDateArea(2);
			$where['indate'] = array("between",array($date[0],$date[1]));
			$order1 = M("gnpDdXq");
			$statistics[1]['amount'] = $order1->where($where)->count();
			$statistics[1]['money'] = $order1->where($where)->sum('zfje');
			$statistics[1]['average'] = ceil($statistics[1]['money']/$statistics[1]['amount']);
			$where['sta'] =5;
			$statistics[1]['statu1'] = $order1->where($where)->count();
			$where['sta'] =2;
			$statistics[1]['statu2'] = $order1->where($where)->count();
			$where['sta'] =3;
			$statistics[1]['statu3'] = $order1->where($where)->count();
			$where['sta'] =11;
			$statistics[1]['statu4'] = $order1->where($where)->count();
			//近3日订单
			$where['indate'] = array("gt",getDateArea(3));
			unset($where['sta']);
			$order2 = M("gnpDdXq");
			$statistics[2]['amount'] = $order2->where($where)->count();
			$statistics[2]['money'] = $order2->where($where)->sum('zfje');
			$statistics[2]['average'] = ceil($statistics[2]['money']/$statistics[2]['amount']);
			$where['sta'] =5;
			$statistics[2]['statu1'] = $order2->where($where)->count();
			$where['sta'] =2;
			$statistics[2]['statu2'] = $order2->where($where)->count();
			$where['sta'] =3;
			$statistics[2]['statu3'] = $order2->where($where)->count();
			$where['sta'] =11;
			$statistics[2]['statu4'] = $order2->where($where)->count();
			//近7日订单
			$where['indate'] = array("gt",getDateArea(4));
			unset($where['sta']);
			$order3 = M("gnpDdXq");
			$statistics[3]['amount'] = $order3->where($where)->count();
			$statistics[3]['money'] = $order3->where($where)->sum('zfje');
			$statistics[3]['average'] = ceil($statistics[3]['money']/$statistics[3]['amount']);
			$where['sta'] =5;
			$statistics[3]['statu1'] = $order3->where($where)->count();
			$where['sta'] =2;
			$statistics[3]['statu2'] = $order3->where($where)->count();
			$where['sta'] =3;
			$statistics[3]['statu3'] = $order3->where($where)->count();
			$where['sta'] =11;
			$statistics[3]['statu4'] = $order3->where($where)->count();
			//近1个月订单
			$where['indate'] = array("gt",getDateArea(5));
			unset($where['sta']);
			$order4 = M("gnpDdXq");
			$statistics[4]['amount'] = $order4->where($where)->count();
			$statistics[4]['money'] = $order4->where($where)->sum('zfje');
			$statistics[4]['average'] = ceil($statistics[4]['money']/$statistics[4]['amount']);
			$where['sta'] =5;
			$statistics[4]['statu1'] = $order4->where($where)->count();
			$where['sta'] =2;
			$statistics[4]['statu2'] = $order4->where($where)->count();
			$where['sta'] =3;
			$statistics[4]['statu3'] = $order4->where($where)->count();
			$where['sta'] =11;
			$statistics[4]['statu4'] = $order4->where($where)->count();
			$this->assign("statistics",$statistics);
		}
		$this->display();
	}
	public function login()
	{
		session('usid',0);
		$post = I('post.');
		if($post){
			$where['us'] = $post['un'];
			$where['pw'] = md58($post['pw'],"tdcheml");
			$chk = M('gnpShanghuUs')->where($where)->find();
			if($chk){
				$usid = $chk['id'];
				$com = M("gnpShanghu")->where("id=$chk[comid]")->find();
				session('comtxt',$com['com']);
				session('cmoid',$com['id']);
				session('usid',$usid);
				session('comid',$chk['comid']);
				$data['ldate'] = date("Y-m-d H:i:s",time());
				M("gnpShanghuUs")->where("id=$chk[id]")->save($data);
				$this->redirect('index');
			}else{
				$this->error('账号或密码错误');
			}
		}else{
			$this->display('login');
		}
	}
	public function logout()
	{
		session('usid',false);
		$this->redirect('login');
	}
	public function edit()
	{
		$usid = $this->check();
		$post = I('post.');
		$aid = $post['aid'];
		if($post){
			if($aid>0){

			}else{
				$data['appid'] = $post['APPID'];
				$data['secret'] = $post['SECRET'];
				$aid = M('wc_app')->add();
				if($aid>0){
					$this->redirect('home/index?aid='.$aid);
				}else{
					$this->error('edit');
				}
			}
		}else{
			$this->assign('aid',$aid);
			$this->assign("comtxt",session("comtxt"));
			$this->display();
		}
	}
	public function test()
	{
		$goods = new \Home\Model\UserModel();
		$data = $goods->limit(10)->select();
		dump($data);
	}
}