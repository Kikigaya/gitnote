<?php
namespace Home\Controller;

/**
 * 首页控制台
 *
 */

class GoodsController extends CommonController{ 
	function __construct()
	{
		parent::__construct();
		$this->usid = $this->check();
		$this->comid = session("comid");
	}
	public function showGoods()
	{
		$usid = $this->usid;
		$comid = $this->comid;
		$page = I("get.page",1);
		$where['comid'] = $comid;
		$get = I("get.");
		if($get['title']){
			$where['title'] = array("like","%".$get['title']."%");
		}
		if($get['cdate1'] && $get['cdate2']){
			$where['indate'] = array("between",array(strtotime($get['cdate1']),strtotime($get['cdate2'])));
		}
		// if($get['edate1'] && $get['edate2']){
		// 	$where['edate'] = array("between",array(strtotime($get['edate1']),strtotime($get['edate2'])));
		// }
		$pages = get_page($get,$page,M("gnp_cp")->field($field)->where($where)->count());
		$goods = M("gnpCp")->where($where)->page($page,10)->order("id desc")->select();
		$this->assign("get",$get);
		$this->assign("goods",$goods);
		$this->assign("page",$page);
		$this->assign("pages",$pages);
		$this->assign("comtxt",session("comtxt"));
		$this->display();
	}
	public function goodsDetail()
	{
		$usid = $this->usid;
		$comid = $this->comid;
		$cpid = I("get.gid");
		if($cpid > 0){
			$good = M("gnpCp a")->join("gnp_cp_fl b on a.id=b.cpid")->where("a.id=$cpid")->find();
			$info = M("gnpCpXq")->where("cpid=$cpid")->find();
			$imgs = json_decode($info['lb'],true);
			$info['details'] =  htmlspecialchars_decode($info['details']);

			$fl = M("gnpCpFl")->where("cpid=$cpid")->find();
			$paramS = str_replace("&quot;",'"',$info['param']);
			// $sku = M("wcAppGoodsSku")->where("cpid=$cpid")->order("id asc")->select();
			$sortd = M("gnp_fl_xl")->where("id=$fl[xid]")->select();

			$jsonData = M("gnp_json_jg")->where("cpid=$cpid")->find();

			$sx = json_decode($jsonData['sx'],true);
			foreach ($sx as $k1 => $v1) {
				$param[$k1]['ParamName'] = $v1['theme'];
				$param[$k1]['ContainImage'] = false;
				foreach ($v1['item'] as $k2 => $v2) {
					$param[$k1]['ChildrenParamList'][$k2]['ParamName'] = $v2['text'];
					$param[$k1]['ChildrenParamList'][$k2]['ParamImage'] = $v2['img'];
					if(!$param[$k1]['ContainImage'] && $v2['img']){
						$param[$k1]['ContainImage'] = true;
					}
				}
			}
			$detail = json_encode($info['xq']);
			$param = json_encode($param);

			$jg = M("gnp_cp_jg")->where("cpid=$cpid")->select();
			foreach ($jg as $k => $v) {
				$txt = json_decode($v['sx'],true);
				foreach ($txt as $key => $value) {
					$sku[$k]['title'][$key]['text'] = $value;
					$sku[$k]['title'][$key]['row'] =1;
					if($k > 0){
						if($sku[$k-1]['title'][$key]['text'] == $sku[$k]['title'][$key]['text']){
							$sku[$k]['title'][$key]['row'] =0;
							for ($i=0; $i < count($sku); $i++) { 
								if($sku[$k - ($i + 1)]['title'][$key]['row'] != 0){
									$sku[$k - ($i + 1)]['title'][$key]['row'] = $sku[$k - ($i + 1)]['title'][$key]['row']+1;
									break;
								}
							}
						}
					}
				}
				$sku[$k]['mprice'] = $v['xj'];
				$sku[$k]['pprice'] = $v['yj'];
				$sku[$k]['rprice'] = $v['pj'];
				$sku[$k]['id'] = $v['id'];
				$sku[$k]['stock'] = $v['kc'];
				$sku[$k]['text'] = implode(json_decode($v['sx'],true));
			}
			$sku = json_encode($sku);
		}
		$sort = M("gnpFlDl")->where("comid=$comid")->order("list")->select();
		$this->assign('sku',$sku);
		$this->assign('param',$param);
		$this->assign('good',$good);
		$this->assign('info',$info);
		$this->assign('imgs',$imgs);
		$this->assign('sort',$sort);
		$this->assign('detail',$detail);
		$this->assign('paramS',$paramS);
		$this->assign('sortd',$sortd);
		$this->assign("comtxt",session("comtxt"));
		$this->display();
	}
	public function editGoods()
	{
		die;
		$usid = $this->usid;
		$comid = $this->comid;
		$post = I("post.");
		if($post){
			//实例化
			$cp = M("gnp_cp");
			$xq = M("gnp_cp_xq");
			$fl = M("gnp_cp_fl");
			$jg = M("gnp_cp_jg");
			$cpid = $post['cpid'];
			//gnp_cp商品基本信息
			$data_cp['bz'] = 0;
			$data_cp['title'] = $post['title'];
			list($data_cp['img']) = json_decode(str_replace('&quot;','"', $post['jsonImages']),true);
			$data_cp['zk'] = $post['discount'];
			$data_cp['pl'] = $post['rlimit'];
			$data_cp['pj'] = $post['rprice'];
			$data_cp['yj'] = $post['pprice'];
			$data_cp['xj'] = $post['mprice'];
			$data_cp['xg'] = $post['blimit'];
			$data_cp['kc'] = $post['stock'];
			$data_cp['zl'] = $post['weight'];
			$data_cp['txm'] = $post['goodsCode'];
			$data_cp['edate'] =now();
			$data_cp['sta'] = 2;
			//gnp_cp_fl商品分类
			$data_fl['did'] = $post['dlun'];
			$data_fl['xid'] = $post['xlun'];
			//gnp_cp_xq商品详情
			$data_xq['xq'] = htmlspecialchars_decode($post['content']);
			$data_xq['lb'] = str_replace('&quot;','"', $post['jsonImages']);
			$data_xq['cs'] = '';
			$text2 = json_decode(htmlspecialchars_decode($post['jsonParam']),true);
			foreach ($text2 as $k1 => $v1) {
				$sx[$k1]['theme'] = $v1['ParamName'];
				foreach ($v1['ChildrenParamList'] as $k2 => $v2) {
					$sx[$k1]['item'][$k2]['text'] = $v2['ParamName'];
					if($v2['ParamImage']){
						$sx[$k1]['item'][$k2]['img'] = $v2['ParamImage'];
					}
				}
			}
			$data_xq['sx'] = json_encode($sx);
			//gnp_cp_jg价格
			$jg->where("cpid=$cpid")->delete();
			$data_jg = json_decode(htmlspecialchars_decode($post['jsonSku']),true);
			foreach ($data_jg as $k1 => $v1) {
				$price[$k1]['cpid'] = $cpid;
				$price[$k1]['sx'] = explode(',',$v1['text']);
				foreach ($price[$k1]['sx'] as $k2 => $v2) {
					$price[$k1]['sx'.($k2+1)] = $v2;
				}
				$price[$k1]['sx'] = json_encode($price[$k1]['sx']);
				$price[$k1]['sxtxt'] = $v1['text'];
				$price[$k1]['yj'] = $v1['pprice'];
				$price[$k1]['xj'] = $v1['mprice'];
				$price[$k1]['pj'] = $v1['rprice'];
				$price[$k1]['sta'] = $v1['stock']>0?1:0;
				$price[$k1]['kc'] = $v1['stock'];
			}
			$jg->addAll($price);
			//gnp_json_jg价格属性
			if($sx[0]){
				foreach ($sx[0]['item'] as $k1 => $v1) {
					$sx2 = $sx1 = '';
					if($sx[1]){
						foreach ($sx[1]['item'] as $k2 => $v2) {
							$where = array();
							$where['cpid'] = $cpid;
							$where['new'] = 1;
							$where['sx1'] = $v1['text'];
							$where['sx2'] = $v2['text'];
							$field = "yj,xj,pj,sta";
							$sx2[] = M("gnp_cp_jg")->field($field)->where($where)->find();
						}
					}else{
						$where = array();
						$where['cpid'] = $cpid;
						$where['new'] = 1;
						$where['sx1'] = $v1['text'];
						$field = "yj,xj,pj,sta";
						$sx2 = M("gnp_cp_jg")->field($field)->where($where)->find();
					}
					$jg_json['sx1'][$k1]['sx1'] = $v1;
					$jg_json['sx1'][$k1]['sx2'] = $sx2;
				}
				if($sx[1]){
					foreach ($sx[1]['item'] as $k1 => $v1) {
						$sx2 = $sx1 = '';
						foreach ($sx[0]['item'] as $k2 => $v2) {
							$where = array();
							$where['cpid'] = $cpid;
							$where['new'] = 1;
							$where['sx2'] = $v1['text'];
							$where['sx1'] = $v2['text'];
							$field = "yj,xj,pj,sta";
							$sx1[] = M("gnp_cp_jg")->field($field)->where($where)->find();
						}
						$jg_json['sx2'][$k1]['sx2'] = $v1;
						$jg_json['sx2'][$k1]['sx1'] = $sx1;
					}
				}
				$data_json['sx'] = json_encode($sx);
				$data_json['jg'] = json_encode($jg_json);
				$data_json['cpid'] = $cpid;
			}
			//录入数据
			if($cpid>0){
				$res = $cp->where("id=$cpid")->save($data_cp);
				$fl->where("cpid=$cpid")->save($data_fl);
				$xq->where("cpid=$cpid")->save($data_xq);
			}else{
				$data_cp['xsxl'] = rand(300,3000);
				$data_cp['indate'] = now();
				$data_cp['comid'] = $comid;
				$res = $cpid = $cp->add($data_cp);
				$data_fl['cpid'] = $cpid;
				$fl->add($data_fl);
				$data_xq['cpid'] = $cpid;
				$xq->add($data_xq);
			}
		}
		if($res){
			$msg = "保存成功";
		}else{
			$msg = "保存失败";
		}
		$this->assign('msg',$msg);
		$this->display();
	}
	public function sorts()
	{
		$usid = $this->usid;
		$comid = $this->comid;
		$where['comid'] = $comid;
		$sorts['fsorts'] = M("gnp_fl_dl")->where($where)->order("list")->select();
		$a = 1;
		foreach ($sorts['fsorts'] as $k => $v) {
			$maps[] = 0;
			$n = $a;
			$a++;
			$where['dlid'] = $v['id'];
			$sorts['fsorts'][$k]['csorts'] = M("gnp_fl_xl")->where($where)->order("list")->select();
			foreach ($sorts['fsorts'][$k]['csorts'] as $k2 => $v2) {
				$maps[] = $n;
				$a++;
			}
		}
		$maps = implode(",",$maps);
		$this->assign("sorts",$sorts);
		$this->assign("maps",$maps);
		$this->display();
	}
	public function sortsEdit()
	{
		$usid = $this->usid;
		$post = I('post.');
		$sid = I("get.sid");
		$fid = I("get.fid");
		$comid = $this->comid;
		$where['comid'] = $comid;
		$fld = M("gnp_fl_dl");
		$flx = M("gnp_fl_xl");
		if($post){
			$sid = $post['sid'];
			$fid = $data['fid'] = $post['fid'];
			$data['un'] = $post['un'];
			$data['comid'] = $comid;
			$data['img'] = $post['img'];
			$data['list'] = $post['list'];
			$data['sta'] = $post['sta'];
			if($sid>0){
				$where['id'] = $sid;
				$fld->where($where)->save($data);
			}else{
				$fld->add($data);
			}
			$this->redirect('sorts');
		}else{
			$sorts = $fld->where($where)->order('list')->select();
			if($sid>0){
				if($fid>0){
					$sort = $flx->where("id=$sid")->find();
				}else{
					$sort = $fld->where("id=$sid")->find();
				}
			}
			$this->assign("sorts",$sorts);
			$this->assign("sort",$sort);
			$this->assign("sid",$sid);
			$this->assign("comtxt",session("comtxt"));
			$this->display();
		}
	}
	public function ppage()
	{
		$usid = $this->usid;
		$comid = $this->comid;
		$img = M("wcAppImg")->where("comid=$comid")->select();
		$sorts = M("wcAppImgSorts")->where("comid=$comid")->select();
		$none = M("wcAppImg")->where("comid=$comid and sid=1")->count();
		$total = M("wcAppImg")->where("comid=$comid")->count();
		$sort[] = array("id"=>1,"un"=>"未分组","comid"=>$comid,"IsMain"=>true,"amount"=>$none,"total"=>$total);
		foreach ($sorts as $k => $v) {
			$v['IsMain'] = false;
			$v['amount'] = M("wcAppImg")->where("comid=$comid and sid=".$v['id'])->count();
			$v['total'] = $total;
			$sort[] = $v;
		}
		$this->assign("sort",json_encode($sort));
		$this->assign("img",$img);
		$this->assign("comtxt",session("comtxt"));
		$this->display();
	}
	public function getSecondSorts()
	{
		$usid = $this->usid;
		$comid = $this->comid;
		$fid = I("post.fatherId");
		if($fid > 0){
			$sorts = M("gnp_fl_xl")->where("dlid=$fid")->select();
			$this->response($sorts,'json');
		}
	}
}