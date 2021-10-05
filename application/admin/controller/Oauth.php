<?php
/**
 * 第三方快捷登录类
 * */
namespace app\admin\controller;
use think\Request;
use think\Db;
use think\facade\Config;

class Oauth extends Base
{
    //第三方登录参数配置
    public function index()
    {
        $info = Config::get('social.');
        return view('index',['oauth'=>$info]);
    }
    
    //第三方登录参数编辑
    public function indexEdit(Request $request)
    {
        if(request()->isAjax()){
            $data = $request -> param();
            $content = "
            <?php
            /**
             * User: liliuwei
             * Date: 2017/5/23
             */
            return [
                //腾讯QQ登录配置
                'qq' => [
                    'app_key' => '{$data['qq_app_key']}', //应用注册成功后分配的 APP ID
                    'app_secret' => '{$data['qq_app_secret']}',  //应用注册成功后分配的KEY
                    'callback' => '{$data['qq_callback']}', // 应用回调地址
                ],
                //微信扫码登录配置
                'weixin' => [
                    'app_key' => '{$data['weixin_app_key']}', //应用注册成功后分配的 APP ID
                    'app_secret' => '{$data['weixin_app_secret']}',  //应用注册成功后分配的KEY
                    'callback' => '{$data['weixin_callback']}', // 应用回调地址
                ],
                //新浪登录配置
                'sina' => [
                    'app_key' => '{$data['sina_app_key']}', //应用注册成功后分配的 APP ID
                    'app_secret' => '{$data['sina_app_secret']}',  //应用注册成功后分配的KEY
                    'callback' => '{$data['sina_callback']}', // 应用回调地址
                ],
                //Baidu登录配置
                'baidu' => [
                    'app_key' => '{$data['baidu_app_key']}', //应用注册成功后分配的 APP ID
                    'app_secret' => '{$data['baidu_app_secret']}',  //应用注册成功后分配的KEY
                    'callback' => '{$data['baidu_callback']}', // 应用回调地址
                ],
                //Gitee登录配置
                'gitee' => [
                    'app_key' => '{$data['gitee_app_key']}', //应用注册成功后分配的 APP ID
                    'app_secret' => '{$data['gitee_app_secret']}',  //应用注册成功后分配的KEY
                    'callback' => '{$data['gitee_callback']}', // 应用回调地址
                ],
                //Github登录配置
                'github' => [
                    'app_key' => '{$data['github_app_key']}', //应用注册成功后分配的 APP ID
                    'app_secret' => '{$data['github_app_secret']}',  //应用注册成功后分配的KEY
                    'callback' => '{$data['github_callback']}', // 应用回调地址
                ],
                //Douyin登录配置
                'douyin' => [
                    'app_key' => '{$data['douyin_app_key']}', //应用注册成功后分配的 APP ID
                    'app_secret' => '{$data['douyin_app_secret']}',  //应用注册成功后分配的KEY
                    'callback' => '{$data['douyin_callback']}', // 应用回调地址
                ],
                //Dingtalk登录配置
                'dingtalk' => [
                    'app_key' => '{$data['dingtalk_app_secret']}', //应用注册成功后分配的 APP ID
                    'app_secret' => '{$data['dingtalk_app_secret']}',  //应用注册成功后分配的KEY
                    'callback' => '{$data['dingtalk_callback']}', // 应用回调地址
                ]
            ];
            ";
            //赋予权限
            @chmod($_SERVER['DOCUMENT_ROOT'].'/config/social.php',0777);
            //写入操作
            $res = file_put_contents($_SERVER['DOCUMENT_ROOT']."/config/social.php",$content);
            if($res){
                //安全起见，恢复权限
                @chmod($_SERVER['DOCUMENT_ROOT'].'/config/social.php',0644);
                //记录日志
                $this->logs("修改了快捷登录信息!");
                $this->success("修改成功！");
            }else{
                //记录日志
                $this->logs("修改快捷登录信息失败!");
                $this->error("修改失败！");
            }
        }
    }
    
    //第三方登录设置
    public function setOauth(Request $request)
    {
        $info = Db::name('login_set')->where('id',1)->find();
        return view('setoauth',['oauth'=>$info]);
    }
    
    //第三方登录设置编辑
    public function setOauthEdit(Request $request)
    {
        if(request()->isAjax()){
            //接收前端的数据
            $data = $request->param();
            //更新数据
            $res = Db::name('login_set')->where('id',1)->update($data);
            if($res){
                //记录日志
                $this->logs("更新了快捷登录设置信息!");
                $this->success("更新成功!");
            }else{
                //记录日志
                $this->logs("更新快捷登录设置信息失败!");
                $this->error("更新失败!");
            }
        }
    }
    
}
