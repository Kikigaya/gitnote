<?php
namespace Home\Controller;

/**
 * 公共库
 *
 */

use Think\Controller\RestController;
use Think\Log\Driver\File;
use Think\Controller;
class CommonController extends RestController {
    /**
    * 服务器请求方法GET											curl_get                                 
    * 服务器请求方法POST										curl_POST                                 
    */
    function __construct(){
    	parent::__construct();
    	$this->file=new File();
    }
    public function check()
    {
    	if (session('usid') == false) {
    		$this->redirect('Index/login');
    	}else{
    		return session('usid');
    	}
    }
    public function curl_get($url) {
    	$curl = curl_init();
    	curl_setopt($curl, CURLOPT_URL, $url);
    	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    	$data = curl_exec($curl);
    	$err = curl_error($curl);
    	curl_close($curl);
    	return $data;
    }
    public function curl_post($url,$data,$dtype='normal',$chinese_encode=false)
    {
    	if($dtype == 'json'){
    		if($chinese_encode){
    			$data = json_encode_self($data);
    		}else{
    			$data = json_encode($data);
    		}
    	}
    	$ch=curl_init();
    	curl_setopt($ch,CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
    	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
    	curl_setopt($ch,CURLOPT_HEADER, 0);
    	curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch,CURLOPT_POST, 1);
    	curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    	$data2 = curl_exec($ch);
    	curl_close($ch);
    	return $data2;
    }
    public function OrdersSettlement() 
    {
        $where['jiesuan'] = 0;
        $where['ddate'] = array('LT',date("Y-m-d H:i:s",time()-864000));
        $field = "id,comid,wxzf,usid";
        $orders = M("gnp_dd_xq")->field($field)->where($where)->limit(10)->select();
        foreach ($orders as $k => $order) {
            $oid = $order['id'];
            $totalFee = $order['wxzf'];
            if($totalFee > 0){
                //商家获得订单金额95%
                $data1['dd'] = $order['id'];
                $data1['ddje'] = $totalFee;
                $data1['tctype'] = 2;
                $rdata1 = M("gnp_ticheng")->field("xje")->where("gstype=2 and gsid=".$order['comid'])->order("id desc")->find();
                $data1['yje'] = $rdata1['xje']?$rdata1['xje']:0;
                $data1['tcje'] = round($totalFee*0.95,2);
                $data1['tcjs'] = 0.95;
                $data1['xje'] = $data1['yje'] + $data1['tcje'];
                $data1['gstype'] = 2;
                $data1['gsid'] = $order['comid'];
                $data1['indate'] = date("Y-m-d H:i:s",time());
                $data1['ms'] = "商家获得订单金额95%";
                $id1 = M("gnp_ticheng")->add($data1);
                //其他分成4%
                $fee = $totalFee - round($totalFee*0.96,2);
                $cominfo = M("gnp_shanghu")->field("dls")->where("id=$order[comid]")->find();
                if($cominfo['dls'] > 0){
                    //代理商分成
                    //高级 普通代理
                    $dlsinfo1 = M("gnp_daili_us")->field("dltype,id,tc,gs")->where("id=$cominfo[dls]")->find();
                    $data2['dd'] = $order['id'];
                    $data2['ddje'] = $totalFee;
                    switch ($dlsinfo1['dltype']) {
                        case 1: $tctype = 3; break;
                        case 2: $tctype = 4; break;
                        case 3: $tctype = 5; break;
                        default: $tctype = 0; break;
                    }
                    $data2['tctype'] = $tctype;
                    $rdata2 = M("gnp_ticheng")->field("xje")->where("gstype=3 and gsid=".$dlsinfo1['id'])->order("id desc")->find();
                    $data2['yje'] = $rdata2['xje']?$rdata2['xje']:0;
                    $fee2 = round($totalFee*$dlsinfo1['tc'],2);
                    $data2['tcje'] = $fee2 > $fee?$fee:$fee2;
                    $data2['tcjs'] = $dlsinfo1['tc'];
                    $fee -= $data2['tcje'];
                    $data2['xje'] = $data2['yje'] + $data2['tcje'];
                    $data2['gstype'] = 3;
                    $data2['gsid'] = $dlsinfo1['id'];
                    $data2['indate'] = date("Y-m-d H:i:s",time());
                    $data2['ms'] = "代理商$tctype分成";
                    if($data2['tcje'] > 0){
                        $id2 = M("gnp_ticheng")->add($data2);
                    }
                    if($dlsinfo1['gs'] > 0 ){
                        //高级代理
                        $dlsinfo2 = M("gnp_daili_us")->field("dltype,id,tc")->where("id=$dlsinfo1[gs]")->find();
                        $data3['dd'] = $order['id'];
                        $data3['ddje'] = $totalFee;
                        switch ($dlsinfo2['dltype']) {
                            case 1: $tctype = 3; break;
                            case 2: $tctype = 4; break;
                            case 3: $tctype = 5; break;
                            default: $tctype = 0; break;
                        }
                        $data3['tctype'] = $tctype;
                        $rdata3 = M("gnp_ticheng")->field("xje")->where("gstype=3 and gsid=".$dlsinfo2['id'])->order("id desc")->find();
                        $data3['yje'] = $rdata3['xje']?$rdata3['xje']:0;
                        $fee3 = round($totalFee*$dlsinfo2['tc'],2);
                        $data3['tcje'] = $fee3 > $fee?$fee:$fee3;
                        $data3['tcjs'] = $dlsinfo2['tc'];
                        $fee -= $data3['tcje'];
                        $data3['xje'] = $data3['yje'] + $data3['tcje'];
                        $data3['gstype'] = 3;
                        $data3['gsid'] = $dlsinfo2['id'];
                        $data3['indate'] = date("Y-m-d H:i:s",time());
                        $data3['ms'] = "代理商$tctype分成";
                        if($data3['tcje']){
                            $id3 = M("gnp_ticheng")->add($data3);
                        }
                    }
                }
                //推廣人分成
                $buyerid = $order['usid'];
                $usergsinfo = M("gnp_us_gs")->where("xsid=$buyerid")->find();
                if($usergsinfo){
                    if($usergsinfo['comid'] > 0){
                        $rdataid = $usergsinfo['comid'];
                        $rdatainfo = M('gnp_shanghu')->where("id=$rdataid")->find();
                        $gstype = 2;
                    }else{
                        $rdataid = $usergsinfo['tgrid'];
                        $rdatainfo = M('gnp_us')->where("id=$rdataid")->find();
                        $gstype = 1;
                    }
                    $data5['dd'] = $order['id'];
                    $data5['ddje'] = $totalFee;
                    $rdata5 = M("gnp_ticheng")->where("gstype=$gstype and gsid=$rdataid")->order("id desc")->find();
                    $data5['tctype'] = 6;
                    $data5['yje'] = $rdata5['xje']?$rdata5['xje']:0;
                    $fee5 = round($totalFee*$rdatainfo['tc'],2);
                    $data5['tcje'] = $fee5 > $fee?$fee:$fee5;
                    $data5['tcjs'] = $rdatainfo['tc'];
                    $fee -= $data5['tcje'];
                    $data5['xje'] = $data5['yje'] + $data5['tcje'];
                    $data5['gstype'] = $gstype;
                    $data5['gsid'] = $rdataid;
                    $data5['indate'] = date("Y-m-d H:i:s",time());
                    $data5['ms'] = "推广获得分成基数分成";
                    if($data5['tcje'] > 0){
                        $id5 = M("gnp_ticheng")->add($data5);
                    }
                }
                //個人返現
                $data6['dd'] = $order['id'];
                $data6['ddje'] = $totalFee;
                $data6['gstype'] = 1;
                $rdata6 = M("gnp_ticheng")->where("gstype=1 and gsid=$buyerid")->order("id desc")->find();
                $data6['tctype'] = 1;
                $data6['yje'] = $rdata6['xje']?$rdata6['xje']:0;
                $js = rand(10,50)/10000;
                $fee6 = round($totalFee*$js,2);
                $data6['tcje'] = $fee6 > $fee ? $fee : $fee6;
                $data6['tcjs'] = $js;
                $data6['xje'] = $data6['yje'] + $data6['tcje'];
                $data6['gsid'] = $buyerid;
                $data6['indate'] = date("Y-m-d H:i:s",time());
                $data6['ms'] = "个人返现";
                if($data6['tcje'] > 0){
                    $id6 = M("gnp_ticheng")->add($data6);
                }
            }
            M("gnp_dd_xq")->where("id=$oid ")->setField("jiesuan",1);
        }
    }
}