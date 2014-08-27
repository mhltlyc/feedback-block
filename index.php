<?php
include './view/commonheader.view.php';
?>
<body>
    <header class="navbar navbar-inverse navbar-fixed-top bs-docs-nav" role="banner">
        <div class="container">
            <div class="navbar-header">
                <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a href="" class="navbar-brand">需求/问题反馈接口</a>
            </div>
            <nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
                <ul class="nav navbar-nav">
                    <li class="active">
                        <a href="">处理过程</a>
                    </li>
                    <li>
                        <a href="">问题解决流程</a>
                    </li>

                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a href="../about">关于</a>
                    </li>
                </ul>
            </nav>
            <iframe id="tmp_downloadhelper_iframe" style="display: none;"></iframe></div>
    </header>
    <div class="container" style="height: 50px;"> </div>
    <div class="container">
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-5"></div>
        </div>
        <div class="row">
            <div class="col-md-9">
                <div class="row">
                    <!-- Nav tabs -->
                    <div class="col-md-3">
                        <ul class="nav nav-tabs" id='templateType'>
                            <li><a href="javascript:void(0)" id="bug" onclick='setTemplate("bug")' data-toggle="tab">问题</a></li>
                            <li><a href="javascript:void(0)" id="requirement" onclick='setTemplate("requirement")' data-toggle="tab">需求</a></li>
                            <li><a href="javascript:void(0)" onclick='setTemplate("other")' data-toggle="tab">其它</a></li>
                            <script>
                                var ftype = '';//问题反馈，还是提新需求
                                var createNew = 1;
                                var currentFid = 0;//当前反馈id，实时更新
                                var statusFilter = 'all';
                                var keywordFilter;

                                $(function() {
                                    //初始化编辑器和tab选项卡
                                    editor = UE.getEditor('editor');
                                    editor.addListener('ready', function(editor) {
                                        $('#templateType li:eq(0) a').tab('show'); // Select third tab (0-indexed)
                                        setTemplate("bug");
                                    });
                                    //初始化反馈信息列表
                                    getFeedbackList('all', keywordFilter);
                                    //搜索框绑定事件
                                    var searchInput = $('input.searchInput');
                                    $('.searchBtn').click(function() {
                                        getFeedbackList(statusFilter, searchInput.val());
                                    });
                                    searchInput.focusin(function() {
                                        searchInput.val('');
                                        keywordFilter = '';
                                    });
                                    searchInput.keydown(function(event) {
                                        if (event.keyCode === 13) {
                                            getFeedbackList(statusFilter, searchInput.val());
                                        }
                                    });
                                });
                                //加载模板到编辑器中
                                function setTemplate(tmpType) {
                                    if (tmpType === 'bug') {
                                        editor.setContent(bugTemplate, false);
                                    } else if (tmpType === 'requirement') {
                                        editor.setContent(requirementTemplate, false);
                                    } else {
                                        editor.setContent('', false);
                                    }
                                    //初始化反馈信息
                                    ftype = tmpType;
                                    currentFid = 0;
                                    createNew = 1;
                                    $('#handler').text('');
                                    $('#status').text('未创建');
                                    $('#create_time').text('')
                                }
                            </script>
                        </ul>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-3">
                                <span>状态:<span class="label label-danger"><span id="status">未创建</span></span></span>
                            </div>
                            <div class="col-md-4">
                                <span><span class="glyphicon glyphicon-user"></span>操作人:<span id="handler"></span>&nbsp;<span id="create_time"></span></span>
                            </div>
                            <div class="col-md-5" style="white-space: nowrap;">
                                <span><span class="glyphicon glyphicon-tags"></span>版本:<span id="version"></span></span>  
                            </div>
                        </div>
                    </div>
                </div>
                <script id="editor" type="text/plain" style="width:100%;height:700px;border-right: 1px solid #000;">请选择问题类型</script>
            </div>
            <div class="col-md-3 right-bar">
                <div class="panel panel-default feedback-list" style="position: fixed;width: 278px;">
                    <div class="panel-heading">
                        <span class="all">反馈单列表：</span>
                        <span class="label label-danger" onclick="getFeedbackList('created')">已创建</span>
                        <span class="label label-warning" onclick="getFeedbackList('confirmed')">已确认</span>
                        <span class="label label-success" onclick="getFeedbackList('solved')">已解决</span>
                        <span class="label label-info" onclick="getFeedbackList('reviewed')">已评审</span>
                        <span class="label label-default" onclick="getFeedbackList('closed')">已关闭</span>
                        <span class="label label-primary" onclick="getFeedbackList('all')">全部</span>
                        <div class="input-group input-group-sm" style="margin-top: 13px;">
                            <input type="text" value="" class="form-control searchInput"/>
                            <span class="input-group-btn">
                                <button class="btn btn-default searchBtn" type="button">Go!</button>
                            </span>
                        </div><!-- /input-group -->
                    </div>
                    <div class="panel-body">
                        <dl id="feedback-list">
                        </dl>
                    </div>
                    <script>
                                //按状态加载反馈信息列表
                                function getFeedbackList(status, keyword) {
                                    //若为传参，则导入缓存中的参数状态
                                    if (typeof(keyword) === 'undefined') {
                                        keyword = keywordFilter;
                                    }
                                    if (typeof(status) === 'undefined') {
                                        status = statusFilter;
                                    }
                                    //更新缓存中的参数
                                    statusFilter = status;
                                    keywordFilter = keyword;
                                    $('#feedback-list').hide(0);
                                    $.post('ajaxHandle.php', {action: 'getFeedbackList', status: status, keyword: keyword}, function(data) {
                                        $('#feedback-list').html(data);
                                        $('#feedback-list').show(150);
                                    });
                                }

                                //点击反馈列表中的条目后，加载对应条目的最新版本到编辑框
                                function loadContent(fid, version) {
                                    $.post("ajaxHandle.php", {fid: fid, action: 'loadContent', version: version}, function(data) {
                                        var feedbackContent = eval("(" + data + ")");
                                        editor.setContent(feedbackContent[0].fbody, false);
                                        ftype = feedbackContent[0].ftype;
                                        $('a#' + ftype).tab('show');
                                        $('#handler').text(feedbackContent[0].handler);
                                        $('#status').text(feedbackContent[0].status);
                                        $('#create_time').text('[' + feedbackContent[0].create_time + ']')
                                        //创建版本字符串
                                        var maxVersion = feedbackContent[0].maxVersion;
                                        var version = '';
                                        var i = 0;
                                        //设置要现实的版本
                                        var mixVersion = 1;
                                        if (maxVersion >= 10) {
                                            mixVersion = maxVersion - 8;
                                        }
                                        for (i = maxVersion; i >= mixVersion; i--) {
                                            version += '<a href="javascript:void(0)" onclick="loadContent(' + fid + ',' + i + ')">#' + i + '</a>';
                                        }
                                        $('#version').html(version);
                                        currentFid = feedbackContent[0].fid;
                                        createNew = 0;
                                    });
                                }
                    </script>
                </div>

            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <p class="text-primary">收件人：产品经理</p>
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon">抄送给</span>
                    <input type="text" class="form-control" placeholder="以;分隔,只填邮箱用户名.示例：ljy;yxf;qjf;"/>
                </div><!-- /input-group -->
            </div>
            <div class="col-md-1"></div>
            <div class="col-md-4">
                <div class="btn-group dropup" id='submit'>
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                        保存并邮件通知 <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu">             
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(function() {
            //按钮单机事件
            $('#submit button').click(function() {
                var buttonList = '';
                var list = '<li><a href="javascript:void(0)" onclick="submit(\'changed\')">文档变更</a></li><li class="divider"></li>';
                buttonList = $('#status').text();
                switch (buttonList) {
                    case '未创建':
                        list += '<li><a href="javascript:void(0)" onclick="submit(\'created\')">已创建 (售前/售后)</a></li>';
                        break;
                    case 'created':
                        list += '<li><a href="javascript:void(0)" onclick="submit(\'confirmed\')">已确认 (产品经理)</a></li>';
                        break;
                    case 'confirmed':
                        list += '<li><a href="javascript:void(0)" onclick="submit(\'solved\')">已解决 (项目组长)</a></li>';
                        break;
                    case 'solved':
                        list += '<li><a href="javascript:void(0)" onclick="submit(\'reviewed\')">已评审 (研发经理)</a></li>';
                        break;
                    case 'reviewed':
                        list += '<li><a href="javascript:void(0)" onclick="submit(\'closed\')">已关闭 (产品经理)</a></li>';
                        break;
                        break;
                    default:
                        break;
                }
                $('#submit ul.dropdown-menu').html(list);

            })
        });
        function submit(status) {

            switch (ftype) {
                case 'bug':
                    var feedbackContent = editor.getContent();
                    var title = $('td#title', feedbackContent).text();
                    $.post("ajaxHandle.php", {action: 'submitFeedback', fid: currentFid, createNew: createNew, ftype: ftype, status: status, fbody: feedbackContent, title: title}, function(data) {
                        currentFid = parseInt(data);
                        $('#message').html('提交成功：状态已更新为' + status);
                        getFeedbackList();
                    });
                    break;
                case 'requirement':
                    var feedbackContent = editor.getContent();
                    var title = $('td#title', feedbackContent).text();
                    $.post("ajaxHandle.php", {action: 'submitFeedback', fid: currentFid, createNew: createNew, ftype: ftype, status: status, fbody: feedbackContent, title: title}, function(data) {
                        currentFid = parseInt(data);
                        $('#message').html('提交成功：状态已更新为' + status);
                        getFeedbackList();
                    });
                    break;
                default:
                    break;
            }

            if (status !== 'changed')
                $('#status').text(status);
            $('#myModal').modal();
            createNew = 0;

        }
    </script>

    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">信息</h4>
                </div>
                <div class="modal-body" id="message">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">我知道啦~</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <?php
    include './view/commonfooter.view.php';
    ?>

