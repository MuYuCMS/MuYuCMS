<?php
/**
 * 
 * 会员管理验证器
 * 
 * */
namespace app\admin\validate;
use think\Validate;

class Member extends Validate
{
    //规则
    protected $rule = [
        'name'          => 'require|unique:member',
        'account'       => 'require|unique:member',
        'phone'         => 'mobile|unique:member',
        'email'         => 'email|unique:member',
        'password'      => 'require|min:6',
        'passwords'     => 'require|confirm:password',
    ];
    
    //自定义提示信息
    protected $message = [
        'name.require'          =>  '用户名不能为空!',
        'name.unique'           =>  '用户名已存在!',
        'account.require'       =>  '账户名不能为空!',
        'account.unique'        =>  '账户名已存在!',
        'phone.mobile'          =>  '手机号码格式不正确!',
        'phone.unique'          =>  '手机号码已存在!',
        'email.email'           =>  '邮箱格式不正确!',
        'email.unique'          =>  '邮箱已存在!',
        'password.require'      =>  '密码不能为空!',
        'password.min'          =>  '密码不能小于6个字符!',
        'passwords.require'     =>  '确认密码不能为空!',
        'passwords.confirm'     =>  '两次密码不一致!',
        
        ];
}