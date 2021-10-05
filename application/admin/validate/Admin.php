<?php
/**
 * 
 * 管理员验证器
 * 
 * */
namespace app\admin\validate;
use think\Validate;

class Admin extends Validate
{
    //规则
    protected $rule = [
        'name'          => 'require|min:5|max:16|alphaNum|unique:admin',
        'intro'         => 'max:50',
        'password'      => 'require|min:6',
        'passwords'     => 'require|confirm:password',
        'phone'         => 'mobile|unique:admin',
        'email'         => 'email|unique:admin',
    ];
    
    //自定义提示信息
    protected $message = [
        'name.require'          =>  '用户名不能为空!',
        'name.min'              =>  '用户名不能小于5个字符!',
        'name.max'              =>  '用户名不能大于16个字符!',
        'name.unique'           =>  '用户名已存在!',
        'name.alphaNum'         =>  '用户名只能是字母和数子组成!',
        'intro.max'             =>  '简介不能超过50个字符!',
        'password.require'      =>  '密码不能为空!',
        'password.min'          =>  '密码不能小于6个字符!',
        'password.max'          =>  '密码不能超过20个字符!',
        'passwords.require'     =>  '确认密码不能为空!',
        'passwords.confirm'     =>  '两次密码不一致!',
        'phone.mobile'          =>  '手机号码格式不正确!',
        'phone.unique'          =>  '手机号码已存在!',
        'email.email'           =>  '邮箱格式不正确!',
        'email.unique'          =>  '邮箱已存在!',
        ];
}