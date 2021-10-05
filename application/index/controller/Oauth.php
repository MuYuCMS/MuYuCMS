<?php
/**
 * 第三方快捷登录
 * */
namespace app\index\controller;
use think\DB;
use think\facade\Session;
class Oauth extends Base
{
    //登录地址
        public function login($type = null)
        {
            if ($type == null) {
                $this->error('参数错误');
            }
            $close = Db::name('login_set')->where('id',1)->field($type."_close")->find();
            if($close[$type."_close"] !== 1){
                $this->error("该快捷登录已关闭!");
            }
            // 获取对象实例
            $sns = \liliuwei\social\Oauth::getInstance($type);
            //跳转到授权页面
            $this->redirect($sns->getRequestCodeURL());
        }
    
        //授权回调地址
        public function callback($type = null, $code = null)
        {
            if ($type == null || $code == null) {
                $this->error('参数错误');
            }
            $sns = \liliuwei\social\Oauth::getInstance($type);
            // 获取TOKEN
            $token = $sns->getAccessToken($code);
            if (is_array($token)) {
                //统一使用$sns->openid()获取openid
                $openid = $sns->openid();
                //判断会员的session是否已经存在，存在则为绑定
                if(Session::has('Member')){
                    $id = Session::get('Member.id');
                    Db::name('member')->where('id',$id)->update([$type.'_openid'=>$openid]);
                    $this->redirect("User/index");
                }else{
                    $info = Db::name('member')->where($type.'_openid',$openid)->find();
                    //存在会员信息直接登录，否则注册并登录
                    if($info){
                        //登录自增1
                        Db::name('member')->where('id',$info['id'])->setInc('count');
                        //设置session值
                        Session::set('Member',$info);
                        $this->redirect("User/index");
                    }else{
                        //设置当前openid为该会员的账号
                        $data['account'] = $openid;
                        //哈希加密
                        $data['password'] = password_hash(time(),PASSWORD_DEFAULT);
                        $data[$type.'_openid'] = $openid;
                        $data['create_time'] = time();
                        $data['name'] = "会员".$openid;
                        //插入操作
                        $res = Db::name('Member')->data($data)->insert();
                         //取得新增数据id
                        $uid = Db::name('Member')->getLastInsID();
                        if($res){
                            //建立新增会员附属表
                            Db::name('member_data')->insert(['uid'=>$uid]);
                            //登录自增1
                            Db::name('member')->where('id',$uid)->setInc('count');
                            //存在session中去
                            $data['id'] = $uid;
                            //设置session值
                            Session::set('Member',$data);
                            $this->redirect("User/index");
                        }else{
                            $this->error("登录失败!");
                        }
                    }
                }
                
            } else {
                $this->error("获取第三方用户的基本信息失败!");
            }
        }
}