<?php
//多线程任务处理
class m_rw_base
{
    public $xz = [
        'emailfa'=>[ //发送邮件
            'limit'=>10,//每次任务获取条数
            'max'=>60,//每个服务器每个任务最大运行数量
        ],
    ];

    //发送任务处理请求
    public function rw_send($huo,$d){
        $huo=glwb($huo);
        $diao=[
            'emailfa' => 'rwbase/fa', //处理发送EMAIL
        ];
        if(!isset($diao[$huo])){
            return '发送失败，任务获取方法名不正确';
        }
        $diao=$diao[$huo];
        $mh = curl_multi_init();
        $conn=[];
        foreach ($d as $i => $v){
            $cs=http_build_query($v);
            $url="http://127.0.0.1/{$diao}?{$cs}&fs={$huo}";
            $conn[$i]=curl_init($url);
            curl_setopt($conn[$i], CURLOPT_CUSTOMREQUEST,"HEAD"); //设置请求方式
            curl_setopt($conn[$i], CURLOPT_TIMEOUT_MS, 2);
            curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($conn[$i], CURLOPT_FRESH_CONNECT,true);
            curl_multi_add_handle ($mh,$conn[$i]);
        }
        do{$n=curl_multi_exec($mh,$active); } while ($active);
        $res=[];
        foreach ($d as $i => $v) {
            $res[$i]=curl_multi_getcontent($conn[$i]);
            curl_multi_remove_handle($mh,$conn[$i]);
            curl_close($conn[$i]);
        }
        curl_multi_close($mh);
        return $res;
    }

    //获取任务
    public function huo($fs)
    {
        $hcm = "suo:htrw_huo_".$fs;
        $suo = lock($hcm,600);
        if($suo == 0){
            return rs('系统繁忙！');
        }
        $limit = $this->xz[$fs]['limit'];
        $where = ['zt'=>0,'fs'=>$fs];
        $data = M()->select('jm_renwu','id',$where,['gxsj','asc'],$limit);
        if(empty($data)){
            unlock($hcm,$suo);
            return rs('没有数据！');
        }
        M()->update('jm_renwu',['zt'=>9],['id'=>array_column($data,'id')]);
        $this->rw_send($fs, $data);
        unlock($hcm,$suo);
        return rs('已处理'.count($data).'条', 1);
    }

    //请求外部
    public function fa($id,$fs)
    {
        $id = ints($id);
        if($id <= 0){
            return rs('ID错误');
        }
        $hcm = "suo:rw_send_{$id}";
        $suo = lock($hcm,60);
        if($suo == 0){
            return rs('系统繁忙！');
        }
        $redis = new redisx();
        $hcm2 = "bingfa:rw_send_{$fs}";
        $rwsl = $redis->incrby($hcm2,1);
        $maxrwsl = $this->xz[$fs]['max'];
        if ($rwsl>$maxrwsl){
            $redis->decrby($hcm2,1);
            return rs('任务已满');
        }
        $v = M()->get('jm_renwu','id,wb_data',['id'=>$id,'zt'=>9]);
        if(empty($v)){
            $redis->decrby($hcm2,1);
            unlock($hcm,$suo);
            return rs('没有数据');
        }
        $wb_data = json_decode($v['wb_data'],1);
        //这里写上逻辑 比如调外部接口
        $wbrs = D("m_rw_{$fs}")->do($wb_data);
        //更新数据库
        $update = ['jg'=>$wbrs['msg'],'gxsj'=>getsj()];
        if($wbrs['code'] ==1)
        {
            // 明确成功 code=1 改zt=1 已发送
            $update['zt'] = 1;
        }
        else if($wbrs['code'] == -1)
        {
            // 明确失败 code=-1 改zt=2 失败,根据情况报警
            $update['zt'] = 2;
        }
        else
        {
            //未知情况，人工处理【失败（zt=2）、成功（zt=1）、重提（zt=0）】
            $update['zt'] = 99;
        }
        M()->update('jm_renwu',$update,['id'=>$id]);

        $redis->decrby($hcm2,1);
        unlock($hcm,$suo);
        return rs($wbrs['msg'],$wbrs['code']);
    }
}