<?php
namespace app\admin\controller;
use app\admin\controller\Base;
use think\Request;
use think\facade\Session;
use think\Db;
use think\File;//文件操作类
use think\facade\Config;//获取配置类

class Update extends Base
{
	
	/**
     * 读取远程文件内容
     * @return string
     */
    public function get_url_content($url){
        //判断当前IP是否允许操作后台
    	$ip = $this->ip_info();
    	//判断是否登录
    	$user = $this -> user_info();
      if(function_exists("curl_init")){
        $ch = curl_init();
        $timeout = 30;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $file_contents = curl_exec($ch);
        curl_close($ch);
      }else{
        $is_auf=ini_get('allow_url_fopen')?true:false;
        if($is_auf){
          $file_contents = file_get_contents($url);
        }
      }
      return $file_contents;
    }
	
	//系统更新版本检测
	public function get_version()
    {
    	//判断是否登录
    	$user = $this -> user_info();
        //当前系统的版本 这个在实际应用中应该是虫数据库获取得到的
        $sys_version_num = Db::name('system')->where('id',1)->field('version')->find();
        //更新日志内容接口
        $update_log = 'http://www.muyucms.com/version/update.log';
    	//获取更新日志内容
    	$content = $this->get_url_content($update_log);
    	//字符串转数组
    	$arr = explode(',',$content);
    	//截取版本号
    	$version = array_shift($arr);
    	//判断是否获取版本号
    	if(!$version){
    	    return ["status"=>0,"msg"=>"获取版本号失败!"];
    	}
    	//$arr截取掉版本号，剩余的为本次更新日志
    	$log = $arr;
    	//版本对比
    	if((float)$version > $sys_version_num['version'])
    	{
    	    //更新标志位
    	    Session::set('update',$version);
    	    //返回版本号及更新日志
    	    return ['status'=>1,'version'=>(float)$version,'update_log' => $log];
    	}else{
    	    //删除更新标志位
    	    Session::delete('update');
    		return ["status"=>0,"msg"=>"当前已是最新版本!"];
    	    }
        }
        
    //更新入口
    public function entrance()
    {
        //判断当前IP是否允许操作后台
    	$ip = $this->ip_info();
    	//判断是否登录
    	$user = $this -> user_info();
    	//当前系统的版本
        $sys_version = Db::name('system')->where('id',1)->field('version')->find();
    	//写入日志文件
    	$this->write_log("读取系统版本信息!");
        //更新日志内容接口
        $update_log = 'http://www.muyucms.com/version/update.log';
        //获取更新日志内容
    	$content = $this->get_url_content($update_log);
    	//字符串转数组
    	$arr = explode(',',$content);
    	//截取版本号
    	$version = array_shift($arr);
    	//$version==$sys_version['version'] 返回 0 | $version<$sys_version['version'] 返回 -1 | $version>$sys_version['version'] 返回 1
    	if(bccomp((float)$version - $sys_version['version'],0.1,2) == 1){
    	    //累计更新
    	    for($i=$sys_version['version'] + 0.1 ; $i < (float)$version + 0.1; )
    	    {
    	        //更新日志内容接口
                $update_log = "http://www.muyucms.com/version/$i/$i.log";
                //获取更新日志内容
            	$content = $this->get_url_content($update_log);
            	//字符串转数组
            	$arr = explode(',',$content);
            	//截取更新包状态，true表示是要更新，false为否
                $zip_status =array_shift($arr);
                $zip_status =explode(':',$zip_status);
                $zip_status = $zip_status[1];
                //截取数据库状态，true表示是要更新，false为否
                $sql_status =explode(':',$arr[0]);
                $sql_status = $sql_status[1];
    	        //写入日志文件
    	        $this->write_log("进行第".$i."版本更新!");
    	        //执行更新
    	        $res = $this->execute_update($i,(bool)$zip_status,(bool)$sql_status);
    	        if(!$res){
    	            //写入日志文件
    	            $this->write_log("第".$i."版本更新失败!");
    	            //更新失败
    	            exit();
    	        }
    	        sleep(5);
    	        $i = $i + 0.1;
    	    }
    	    //写入日志文件
    	    $this->write_log("累计更新成功!");
    	    //删除更新标志位
    	    Session::delete('update');
    	}else{
    	    //更新日志内容接口
            $update_log = "http://www.muyucms.com/version/$version/$version.log";
            //获取更新日志内容
        	$content = $this->get_url_content($update_log);
        	//字符串转数组
    	    $arr = explode(',',$content);
    	    //截取更新包状态，true表示是要更新，false为否
        	$zip_status =array_shift($arr);
        	$zip_status =explode(':',$zip_status);
        	$zip_status = $zip_status[1];
        	//截取数据库状态，true表示是要更新，false为否
        	$sql_status =explode(':',$arr[0]);
        	$sql_status = $sql_status[1];
    	    //写入日志文件
    	    $this->write_log("进行平滑更新!");
    	    //执行更新
    	    $res = $this->execute_update($version,(bool)$zip_status,(bool)$sql_status);
    	    if($res){
    	        //写入日志文件
    	        $this->write_log("平滑更新成功!");
    	        //删除更新标志位
    	        Session::delete('update');
    	    }else{
    	        //写入日志文件
    	        $this->write_log("平滑更新失败!");
    	    }
    	}
    	
    }
    
	/* 执行更新操作
	 * @$version 需要更新的版本
	 * @$zip_status 更新代码标志位
	 * @$sql_status 数据库更新标志位
	 */
	public function execute_update($version,$zip_status,$sql_status)
	{
	    //判断当前IP是否允许操作后台
    	$ip = $this->ip_info();
    	//判断是否登录
    	$user = $this -> user_info();
    	//对php.ini参数进行修改
        ini_set("max_execution_time", "360");
        ini_set('memory_limit', '100M');
        // PclZip类库不支持命名空间
        include_once $_SERVER['DOCUMENT_ROOT'] . '/extend/pclzip/PclZip.php';
        //引入数据库操作类
        include_once $_SERVER['DOCUMENT_ROOT'] . '/extend/baksql/Baksql.php';
        //获取时间戳
        $time = time();
        //当前系统的版本
        $sys_version = Db::name('system')->where('id',1)->field('version')->find();
        //定义备份路径
        $folder = $_SERVER['DOCUMENT_ROOT'].'/mdata/update/backup_dir/'.$time;
        //创建文件
        mkdir($folder);
    	//远程获取升级包url
        $zip_url = "http://www.muyucms.com/version/$version/$version.zip";
        //远程获取数据库更新文件url
        $sql_url = "http://www.muyucms.com/version/$version/$version.sql";
        //定义升级包和数据库文件下载保存地址
        $save_dir = $_SERVER['DOCUMENT_ROOT'].'/mdata/update/upload_dir/'.$time;
        //创建文件
        mkdir($save_dir);
        //判断是否需要更新网站代码
        if($zip_status == true){
            sleep(1);
        	//备份网站代码
        	$backup_code = $this->backup_code($folder,$sys_version['version']);
            //写入日志文件
        	$this->write_log("备份网站代码!");
        	if($backup_code == false)
            {
                //写入日志文件
        	   $this->write_log("代码备份失败!");
                //删除备份文件夹
                $this->rmdirr($folder);
                //写入日志文件
                $this->write_log("删除备份文件夹!");
                //代码备份失败
                exit();
            }
            //升级包别名
            $filename = $version.'.zip';
            sleep(1);
            //调用文件下载函数下载升级包
            $zip_data=$this->getFile($zip_url, $save_dir, $filename, 1);
            //对更新包解压
            $zip = new \ZipArchive;
            if ($zip->open($zip_data['save_path']) === true) {
                sleep(1);
                $zip->extractTo($save_dir);
                $zip->close();
                //写入日志文件
                $this->write_log("更新包解压成功!");
            } else {
                //写入日志文件
        	    $this->write_log("更新包解压失败!");
                //删除放置更新文件夹
                $this->rmdirr($save_dir);
                //写入日志文件
                $this->write_log("删除更新文件夹!");
                //删除备份文件夹
                $this->rmdirr($folder);
                //写入日志文件
                $this->write_log("删除备份文件夹!");
                //更新包解压失败
                exit();
            }
            //写入日志文件
        	$this->write_log("将解压的文件复制到根目录覆盖!");
            //将解压的文件复制到根目录覆盖
            $copy_file = $this->copy_to_file($save_dir . '/temp_folder', $_SERVER['DOCUMENT_ROOT']);
            if($copy_file == false){
                //写入日志文件
        	    $this->write_log("解压的文件复制到根目录覆盖失败!");
                //还原路径
                $reduction_path = dirname($_SERVER['DOCUMENT_ROOT']);
                //删除网站所有文件
                $this->rmdirr($_SERVER['DOCUMENT_ROOT']);
                sleep(1);
                //写入日志文件
        	    $this->write_log("对网站代码进行回滚!");
                //代码回滚,备份代码解压到根目录的上一层目录
                $zips = $zip->extract(PCLZIP_OPT_PATH, $reduction_path);
                if($zips == 0){
                    $this->write_log("网站代码进行回滚失败!");
                    //代码还原失败
                    exit();
                }
                //写入日志文件
                $this->write_log("网站代码进行回滚成功!");
                //删除放置更新文件夹
                $this->rmdirr($save_dir);
                //写入日志文件
                $this->write_log("删除更新文件夹!");
                //删除备份文件夹
                $this->rmdirr($folder);
                //写入日志文件
                $this->write_log("删除备份文件夹!");
                //复制更新文件覆盖失败
                exit();
            }
            //写入日志文件
            $this->write_log("解压的文件复制到根目录覆盖成功，代码更新完成!");
        }
        //判断是否需要更新数据库
        if($sql_status == true){
            //写入日志文件
        	$this->write_log("备份数据库!");
        	//备份数据库
        	$backup_sql = $this->backup_sql($folder,$sys_version['version']);
        	if($backup_sql == false)
            {
                //写入日志文件
        	   $this->write_log("数据库备份失败!");
                //删除备份文件夹
                $this->rmdirr($folder);
                //写入日志文件
                $this->write_log("删除备份文件夹!");
                //数据库备份失败
                exit();
            }
            sleep(1);
            //数据库别名
            $sqlname = $version.'.sql';
            //调用文件下载函数下载数据库更新文件
            $sql_data=$this->getFile($sql_url, $save_dir, $sqlname, 1);
            sleep(1);
            //数据库更新操作
            $zsql = new \org\Baksql(Config::get("database."),$folder,$sys_version['version']);
            if(is_file($sql_data['save_path'])){
                if (file_exists($sql_data['save_path'])){
                    //写入日志文件
                    $this->write_log("数据库更新操作!");
                    //动态获取数据库配置信息
                    $db = Db::connect();
                    //写入日志文件
                    $this->write_log("读取数据库更新文件内容!");
                    $sql = file_get_contents($sql_data['save_path']);
                    sleep(1);
                    foreach (explode(";", trim($sql)) as $query) {
                        $query = $db->query(trim($query));
                        if($query === false){
                            //写入日志文件
                            $this->write_log("数据库更新失败，数据库数据回滚!");
                            //数据库回滚(还原)
                            $info = $zsql->restore($folder .'/' .$sys_version['version'].'.sql');
                            if($info == false){
                                //写入日志文件
                                $this->write_log("数据库回滚失败!");
                                //数据库还原失败
                                exit();
                            }
                            sleep(1);
                            //还原路径
                            $reduction_path = dirname($_SERVER['DOCUMENT_ROOT']);
                            //删除网站所有文件
                            $this->rmdirr($_SERVER['DOCUMENT_ROOT']);
                            sleep(1);
                            //写入日志文件
                            $this->write_log("网站代码数据回滚!");
                            //代码回滚,备份代码解压到根目录的上一层目录
                            $zips = $zip->extract(PCLZIP_OPT_PATH, $reduction_path);
                            if($zips == 0){
                                //写入日志文件
                                $this->write_log("网站代码数据回滚失败!");
                                //代码还原失败
                                exit();
                            }
                            //数据库更新失败
                            exit();
                        }
                    }
                    //写入日志文件
                    $this->write_log("数据库更新成功!");
                }
            }
        }
        //写入日志文件
        $this->write_log("更新系统版本号!");
        //更新版本号
        $ver = Db::name('system')->where('id',1)->update(['version' => (float)$version]);
        //删除放置更新文件夹
        $this->rmdirr($save_dir);
        //写入日志文件
        $this->write_log("删除更新文件夹!");
        sleep(1);
        //删除备份文件夹
        $this->rmdirr($folder);
        //写入日志文件
        $this->write_log("删除备份文件夹!");
        return $ver;
	}
	
    /*
     * 获得目录下的所有文件路径并复制到指定的目录下面
     * $old_dir：目标文件目录 
     * $new_dir：需要复制到的文件目录
     * $quanxian：设置权限
     */
    public function copy_to_file($old_dir,$new_dir){
        //判断当前IP是否允许操作后台
    	$ip = $this->ip_info();
    	//判断是否登录
    	$user = $this -> user_info();
        //判断有没有目录，没有则创建
    	if(!is_dir($new_dir)){
		    @mkdir($new_dir,true);
		} 
        $res = '';
        $temp = scandir($old_dir);
        if(is_array($temp) && count($temp)>2){
            unset($temp[0],$temp[1]);
            foreach($temp as $key=>$val){
                $file_url=$old_dir.DIRECTORY_SEPARATOR.$val;
                //组件新的目录
                $xin_dir = $new_dir.DIRECTORY_SEPARATOR.$val;
                //是否是目录
                if(is_dir($file_url)){ 
                    $this->copy_to_file($file_url,$xin_dir);
                }elseif(is_file($file_url)){
                    $res = copy($file_url,$xin_dir);
                }
            }
        }
        return $res;
    }

    /* 删除目录文件
     * @$dirname 目录路径
     */
    public function rmdirr($dirname)
    {
        //判断当前IP是否允许操作后台
    	$ip = $this->ip_info();
    	//判断是否登录
    	$user = $this -> user_info();
         // Sanity check
         if (!file_exists($dirname)) {
          return false;
         }
         // Simple delete for a file
         if (is_file($dirname) || is_link($dirname)) {
          return unlink($dirname);
         }
         // Loop through the folder
         $dir = dir($dirname);
         while (false !== $entry = $dir->read()) {
          // Skip pointers
          if ($entry == '.' || $entry == '..') {
           continue;
          }
          // Recurse
          $this->rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
         }
         // Clean up
         $dir->close();
         return rmdir($dirname);
    }
    
    
	/* 备份代码
	 * @$folder 更新目录
	 * @$zipname 更新包名称
	 */
    public function backup_code($folder,$zipname)
    {
        //判断当前IP是否允许操作后台
    	$ip = $this->ip_info();
    	//判断是否登录
    	$user = $this -> user_info();
        //定义压缩包所在路径
        $zipnames = $folder.'/'.$zipname.'.zip';
        //实例化这个PclZip类
        $zip = new \PclZip($zipnames); 
        
        //要备份的根目录文件夹
        //$back_dir = 'application,config,error,extend,install,mdata,public,route,runtime,template,thinkphp,ueditor,vendor';
        //要备份的根目录文件
        //$back_file = ',.gitignore,.htaccess,.travis.yml,composer.json,composer.lock,think,404.html,admin.php,build.php,index.php,nginx.htaccess';
        
        //将网站根目录压缩，去掉路径中的/www/wwwroot部分（宝塔）
        $back_all = $zip->create($_SERVER['DOCUMENT_ROOT'], PCLZIP_OPT_REMOVE_PATH, dirname($_SERVER['DOCUMENT_ROOT'])); 
        if($back_all){
            return true;
        }else{
            return false;
        }
    }
    
    
    /* 备份数据库
	 * @$folder 更新目录
	 * @$zipname 更新包名称
	 */
    public function backup_sql($folder,$version)
    {
        //判断当前IP是否允许操作后台
    	$ip = $this->ip_info();
    	//判断是否登录
    	$user = $this -> user_info();
        $sql = new \org\Baksql(Config::get("database."),$folder,$version);
        //备份处理
        $info = $sql->backup();
        if($info){
            return true;
        }else{
            return false;
        }
    }
    
    /* 远程下载文件到指定目录
     * @$url 文件下载地址
     * @$save_dir 文件保存路径
     * @$filename 定义文件名称
     */
    public function getFile($url, $save_dir = '', $filename = '', $type = 0) {
        //判断当前IP是否允许操作后台
    	$ip = $this->ip_info();
    	//判断是否登录
    	$user = $this -> user_info();
        if (trim($url) == '') {
            return false;
        }
        if (trim($save_dir) == '') {
            $save_dir = './';
        }
        if (0 !== strrpos($save_dir, '/')) {
            $save_dir.= '/';
        }
        //创建保存目录
        if (!file_exists($save_dir) && !mkdir($save_dir)) {
            return false;
        }
        //获取远程文件所采用的方法
        if ($type) {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $content = curl_exec($ch);
            curl_close($ch);
        } else {
            ob_start();
            readfile($url);
            $content = ob_get_contents();
            ob_end_clean();
        }
        $size = strlen($content);
        //文件大小
        $fp2 = @fopen($save_dir . $filename, 'a');
        fwrite($fp2, $content);
        fclose($fp2);
        unset($content, $url);
        return array('file_name' => $filename,'save_path' => $save_dir . $filename);
    }
    
    
    /**
     * [write_log 写入日志]
     * @param  [type] $data [写入的数据]
     * @return [type]       [description]
     */
    public function write_log($data){ 
        //判断当前IP是否允许操作后台
    	$ip = $this->ip_info();
    	//判断是否登录
    	$user = $this -> user_info();
        $time = date('Y-m-d');
        //设置路径目录信息
        $url = $_SERVER['DOCUMENT_ROOT'].'/mdata/update/log/'.$time.'/'.'update_log.log';  
        $dir_name=dirname($url);
          //目录不存在就创建
          if(!file_exists($dir_name))
          {
            //iconv防止中文名乱码
           $res = mkdir(iconv("UTF-8", "GBK", $dir_name),0777,true);
          }
          $fp = fopen($url,"a");//打开文件资源通道 不存在则自动创建       
        fwrite($fp,date("Y-m-d H:i:s").var_export($data,true)."\r\n");//写入文件
        fclose($fp);//关闭资源通道
    }
    
    
    /**
     * 实时返回显示进度信息
     * @param  string $msg 提示信息
     */
    public function showMsg($msg)
    {
        echo $msg;
    }

}