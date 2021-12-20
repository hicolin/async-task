<?php
class m_rw_emailfa {
    public function do($data) {
        $wbrs = chttp(['url'=>'www.emailfa.com/sendemail','data'=>$data]);
        $wbrs = json_decode($wbrs,1);
        return $wbrs;
    }

    public function sign(Type $var = null)
    {
        # code...
    }
    // todo.....
}