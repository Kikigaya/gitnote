<?php
namespace Home\Controller;

/**
 * 首页控制台
 *
 */

class BaseController extends CommonController{ 
	function __construct()
	{
		parent::__construct();
		$this->usid = $this->check();
		$this->comid = session("comid");
	}
	public function imagesMage()
	{
		$usid = $this->usid;
		$comid = $this->comid;
		$IMG = M("gnpImg");
		$IMGSORTS = M("gnpImgSorts");
		$imgs = $IMG->where("comid=$comid")->order("id desc")->select();
		$sorts = $IMGSORTS->where("comid=$comid")->select();
		$none = $IMG->where("comid=$comid and sid=1")->count();
		$total = $IMG->where("comid=$comid")->count();
		$sort[] = array("id"=>1,"un"=>"未分组","comid"=>$comid,"IsMain"=>true,"amount"=>$none,"total"=>$total);
		foreach ($sorts as $k => $v) {
			$v['IsMain'] = false;
			$v['amount'] = $IMG->where("comid=$comid and sid=".$v['id'])->count();
			$v['total'] = $total;
			$sort[] = $v;
		}
		$this->assign("sort",json_encode($sort));
		$this->assign("imgs",json_encode($imgs));
		$this->display();
	}
	public function imgMageEdit()
	{
		$usid = $this->usid;
		$comid = $this->comid;
		if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
			$data = $GLOBALS['HTTP_RAW_POST_DATA'];
		} else {
			$data = file_get_contents('php://input');
		}
		$data = json_decode($data,true);
		$id = $data['id'];
		$act = I("get.act");
		if($act == "DeleteImage"){
			$img = M("gnpImg")->where("id=$id")->find();
			M("gnpImg")->where("id=$id")->delete();
			unlink($img['path']);
		}else if($act == "ImageGrouping"){
			$list = explode(',', $data['IdList']);
			if($list[0] > 0){
				$id = $data['NewGroupId'];
				foreach ($list as $k => $v) {
					M("gnpImg")->where("id=$v")->setField('sid',$id);
				}
			}else{
				$sid = $data['NewGroupId'];
				$id = $data['id'];
				M("gnpImg")->where("id=$id")->setField('sid',$sid);
			}
			$res['data'] = array();
		}else if($act == "RenamingGroup"){
			M("gnpImgSorts")->where("id=$id")->setField('un',$data['GroupName']);
			$sorts = M("gnpImgSorts")->where("comid=$comid")->select();
			$none = M("gnpImg")->where("comid=$comid and sid=1")->count();
			$total = M("gnpImg")->where("comid=$comid")->count();
			$sort[] = array("id"=>1,"un"=>"未分组","comid"=>$comid,"IsMain"=>true,"amount"=>$none,"total"=>$total);
			foreach ($sorts as $k => $v) {
				$v['IsMain'] = false;
				$v['amount'] = M("gnpImg")->where("comid=$comid and sid=".$v['id'])->count();
				$v['total'] = $total;
				$sort[] = $v;
			}
			$res['data'] = $sort;
		}else if($act == "DelImageList"){
			$list = explode(',', $data['IdList']);
			foreach ($list as $k => $v) {
				M("gnpImg")->where("id=$v")->delete();
			}
			$res['data'] = array();
		}else if($act == "DeleteGroup"){
			M("gnpImgSorts")->where("id=$id")->delete();
			M("gnpImg")->where("sid=$id")->setField('sid',1);
			$sorts = M("gnpImgSorts")->where("comid=$comid")->select();
			$none = M("gnpImg")->where("comid=$comid and sid=1")->count();
			$total = M("gnpImg")->where("comid=$comid")->count();
			$sort[] = array("id"=>1,"un"=>"未分组","comid"=>$comid,"IsMain"=>true,"amount"=>$none,"total"=>$total);
			foreach ($sorts as $k => $v) {
				$v['IsMain'] = false;
				$v['amount'] = M("gnpImg")->where("comid=$comid and sid=".$v['id'])->count();
				$v['total'] = $total;
				$sort[] = $v;
			}
			$res['data'] = $sort;
		}else if($act == "GetImageDataPageList"){
			$data['GroupId']>0?$where['sid']=$data['GroupId']:'';
			$where['comid'] = array('eq',$comid);
			$page = $data['PageIndex']?$data['PageIndex']:0;
			$where['un'] = array('like','%'.$data['Alias'].'%');
			$res['data'] = M("gnpImg")->where($where)->page($page,20)->order("id desc")->select();
		}else if($act == "GetImageDataPageList"){
			$res['data'] = array();
		}else if($act == "FindGroupList"){
			$sorts = M("gnpImgSorts")->where("comid=$comid")->select();
			$none = M("gnpImg")->where("comid=$comid and sid=1")->count();
			$total = M("gnpImg")->where("comid=$comid")->count();
			$sort[] = array("id"=>1,"un"=>"未分组","comid"=>$comid,"IsMain"=>true,"amount"=>$none,"total"=>$total);
			foreach ($sorts as $k => $v) {
				$v['IsMain'] = false;
				$v['amount'] = M("gnpImg")->where("comid=$comid and sid=".$v['id'])->count();
				$v['total'] = $total;
				$sort[] = $v;
			}
			$res['data'] = $sort;
		}else if($act == "AddGroup"){
			$tdata['comid'] = $comid;
			$tdata['un'] = $data['GroupName'];
			M("gnpImgSorts")->add($tdata);
			$sorts = M("gnpImgSorts")->where("comid=$comid")->select();
			$none = M("gnpImg")->where("comid=$comid and sid=1")->count();
			$total = M("gnpImg")->where("comid=$comid")->count();
			$sort[] = array("id"=>1,"un"=>"未分组","comid"=>$comid,"IsMain"=>true,"amount"=>$none,"total"=>$total);
			foreach ($sorts as $k => $v) {
				$v['IsMain'] = false;
				$v['amount'] = M("gnpImg")->where("comid=$comid and sid=".$v['id'])->count();
				$v['total'] = $total;
				$sort[] = $v;
			}
			$res['data'] = $sort;
		}else if($act == "RenamingImage"){
			M("gnpImg")->where("id=$id")->setField('un',$data['ImageName']);
		}
		$res['code'] = 0;
		$res['message'] = "查询成功";
		$res1['d'] = $res;
		$this->response($res1,"json");
	}
	public function upload()
	{
		$usid = $this->usid;
		$comid = session('comid');
		$sid = I("get.sid");
		$path = "$comid/".date("Y/m",time())."/";
		$uploaddir ="H:/web/aimg/upload/".$path;
		if (!file_exists($uploaddir)) {
			$result = mkdir($uploaddir,777,true);
		}
		$post = I('post.');
		$file = $_FILES;
		$filename=explode(".",$_FILES['upimg']['name']);
		$un=$filename[0];
		do {
			$filename[0]=getRandStr(4);
			$name=implode(".",$filename);
			$name = date("His",time()).$name;
			$pic = "http://a.img.chinacxwl.com/upload/".$path.$name;
		}while(file_exists($uploadfile));
		$uploadfile=$uploaddir.$name;;
		if (move_uploaded_file($_FILES['upimg']['tmp_name'],$uploadfile)){
			$data['comid'] = $comid;
			$data['url'] = $pic;
			$data['sid'] = $sid?$sid:1;
			$data['un'] = $un;
			$data['path'] = $uploadfile;
			$data['indate'] = now();
			$data['size'] = $_FILES['upimg']['size'];
			$img = M('gnpImg');
			$imgid = $img->add($data);
			$res = "{ status : 0, msg: { Origin : '[{\"status\":1,\"msg\":\"$pic\"}]' } }";
			$this->response($res);
		}
	}
	public function ppage()
	{
		$usid = $this->usid;
		$comid = $this->comid;
		$IMG = M("gnpImg");
		$IMGSORTS = M("gnpImgSorts");
		$img = $IMG->where("comid=$comid")->select();
		$sorts = $IMGSORTS->where("comid=$comid")->select();
		$none = $IMG->where("comid=$comid and sid=1")->count();
		$total = $IMG->where("comid=$comid")->count();
		$sort[] = array("id"=>1,"un"=>"未分组","comid"=>$comid,"IsMain"=>true,"amount"=>$none,"total"=>$total);
		foreach ($sorts as $k => $v) {
			$v['IsMain'] = false;
			$v['amount'] = $IMG->where("comid=$comid and sid=".$v['id'])->count();
			$v['total'] = $total;
			$sort[] = $v;
		}
		$imgs = $IMG->where("comid=$comid")->select();
		$this->assign("sort",json_encode($sort));
		$this->assign("imgs",json_encode($imgs));
		$this->assign("comtxt",session("comtxt"));
		$this->display();
	}
	public function setting()
	{
		$usid = $this->usid;
		$comid = $this->comid;
		$post = I("post.");
		if($post){
			$data1['notice'] = $post['txtShopFeatures'];
			$data1['descri'] = $post['txtVendorInfo'];
			$data1['head_img'] = $post['hdWapImgimgShow'];
			$data1['back_img'] = $post['hdImgBackShow'];
			M("wcAppInfo")->where("comid=$comid")->save($data1);
			$data2['contact'] = $post['txtContactName'];
			$data2['phone'] = $post['txtPhone'];
			$data2['address'] = $post['txtLegalAdress'];
			$data2['email'] = $post['txtEmail'];
			M("wcUs")->where("id=$usid")->save($data2);
		}
		$com = M("wcUser")->where("id=$usid")->find();
		$app = M("wcApp a")->join("wc_app_info b on a.id=b.comid")->where("a.id=$comid")->find();
		$this->assign("com",$com);
		$this->assign("app",$app);
		$this->assign("comtxt",session("comtxt"));
		$this->display();
	}
}