<!DOCTYPE HTML>
<html>
<head>
    <title>在线代码编辑 - MuYuCMS内容管理系统</title>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit|ie-comp|ie-stand">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <!--<link rel="shortcut icon" href="./favicon.ico" type="image/x-icon" />-->
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <link rel="stylesheet" type="text/css" href="<?= STATIC_PATH ?>/lib/h-ui/css/H-ui.min.css"/>
    <link rel="stylesheet" type="text/css" href="<?= STATIC_PATH ?>/lib/h-ui.admin/css/H-ui.admin.css"/>
    <link rel="stylesheet" type="text/css" href="<?= STATIC_PATH ?>/lib/Hui-iconfont/1.0.8/iconfont.css"/>
    <link rel="stylesheet" type="text/css" href="<?= STATIC_PATH ?>/lib/h-ui.admin/skin/default/skin.css" id="skin"/>
    <link rel="stylesheet" type="text/css" href="<?= STATIC_PATH ?>/lib/h-ui.admin/css/style.css"/>
    <link rel="stylesheet" type="text/css" href="<?= STATIC_PATH ?>/lib/jstree/jstree.css"/>

    <link rel="stylesheet" type="text/css" href="<?= STATIC_PATH ?>/css/index.css"/>
</head>
<body>
<aside class="Hui-aside" id="left_area" style="background-color: #272822;">
    <div id="user_center" style="width:190px;color:#f3f3ed;">
        当前管理员:<?= \App\Core\UserAuth::session('username'); ?>
        <hr/>
        <button class="btn btn-primary-outline radius size-MINI" onclick="reload_tree()">刷新目录</button>
        <button class="btn btn-primary-outline radius size-MINI" onclick="keyboards()">快捷键帮助</button>
        <hr/>
    </div>
    <div class="" id="tree"></div>
</aside>
<div class="dislpayArrow hidden-xs">
    <a class="pngfix" href="javascript:void(0);" onClick="_displaynavbar(this)"></a>
</div>
<section class="Hui-article-box" style="top:0px">
    <div id="Hui-tabNav" class="Hui-tabNav hidden-xs">
        <div class="Hui-tabNav-wp">
            <ul id="min_title_list" class="acrossTab cl"></ul>
        </div>
        <div class="Hui-tabNav-more btn-group">
            <a id="js-tabNav-prev" class="btn radius btn-default size-S" href="javascript:;">
                <i class="Hui-iconfont">&#xe6d4;</i>
            </a>
            <a id="js-tabNav-next" class="btn radius btn-default size-S" href="javascript:;">
                <i class="Hui-iconfont">&#xe6d7;</i>
            </a>
        </div>
    </div>
    <div id="iframe_box" class="Hui-article"></div>
    
   
 <div id="file_area" style="height: 100%;width: 100%;"></div>
</section>



<script type="text/javascript" src="<?= STATIC_PATH ?>/lib/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="<?= STATIC_PATH ?>/lib/h-ui/js/H-ui.min.js"></script>
<script type="text/javascript" src="<?= STATIC_PATH ?>/lib/h-ui.admin/js/H-ui.admin.js"></script>
<script type="text/javascript" src="<?= STATIC_PATH ?>/lib/jstree/jstree.js"></script>
<script type="text/javascript" src="<?= STATIC_PATH ?>/lib/layer/2.4/layer.js"></script>

<script type="text/javascript">
    var base_url = '<?=BASE_URL?>';
</script>
<script type="text/javascript" src="<?= STATIC_PATH ?>/js/index.js"></script>
<script type="text/javascript" src="<?= STATIC_PATH ?>/js/index_event.js"></script>
<script type="text/javascript" src="<?= STATIC_PATH ?>/js/jstree.js"></script>

<script type="text/javascript" src="<?=STATIC_PATH?>/lib/ace/src-noconflict/ace.js"></script>
<script type="text/javascript" src="<?=STATIC_PATH?>/lib/ace/src-noconflict/ext-settings_menu.js"></script>
<script type="text/javascript" src="<?=STATIC_PATH?>/lib/ace/src-noconflict/ext-language_tools.js"></script>

<script>
    // 添加tab切换事件
    $(document).on("click", "#min_title_list li", function () {
        setTimeout(function(){
            global_getSelectedIframe().contentWindow.editor.focus();
        },100);
    });
    
</script>
<script type="text/javascript" language=JavaScript charset="UTF-8">
    var editor;
    $(function(){
        //初始化对象
        editor = ace.edit( 'file_area' );
                editor.commands.addCommand({
            name: '保存',
            bindKey: {win: 'Ctrl-S',  mac: 'Command-S'},
            exec: function(editor) {
                save_file();
            }
        });
        editor.commands.addCommand({
            name: '关闭',
            bindKey: {win: 'Ctrl-W',  mac: 'Command-W'},
            exec: function(editor) {
                parent.global_closeOpenWindow();
            }
        });
        editor.commands.addCommand({
            name: '退出',
            bindKey: {win: 'Esc',  mac: 'Esc'},
            exec: function(editor) {
                parent.global_closeOpenWindow();
            }
        });
        editor.commands.addCommand({
            name: '到下一行',
            bindKey: {win: 'Shift+Enter',  mac: ''},
            exec: function(_editor) {
                _editor.selection.clearSelection();
                _editor.navigateLineEnd();
                _editor.insert("\n");
            }
        });

        editor.commands.addCommand({
            name: "快捷键使用",
            bindKey: {win: "Ctrl-Alt-h", mac: "Command-Alt-h"},
            exec: function(editor) {
                ace.config.loadModule("ace/ext/keybinding_menu", function(module) {
                    module.init(editor);
                    editor.showKeyboardShortcuts()
                })
            }
        })

        editor.commands.addCommand({
            name: "当前选中全部",
            bindKey: {win: "Alt+J", mac: ""},
            exec: function(editor) {
                editor.selectMore(1);
            }
        })

        editor.commands.addCommands([{
            name: "设置菜单",
            bindKey: {win: "Ctrl-q", mac: "Ctrl-q"},
            exec: function(editor) {
                editor.showSettingsMenu();
            },
            readOnly: true
        }]);
    })
    //快捷键帮助
    function keyboards(){
                ace.config.loadModule("ace/ext/keybinding_menu", function(module) {
                    module.init(editor);
                    editor.showKeyboardShortcuts()
                })
    }
</script>
</body>
</html>