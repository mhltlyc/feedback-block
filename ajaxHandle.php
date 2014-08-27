<?php

include './source/Medoo.class.php';
//include 'source/functions.php';
//include 'accessCtrl.php';
$database = new medoo('db_feedback.db');

//过滤字符串
function strFilter($param) {
    return trim($param);
}

$action = strFilter($_POST['action']);
switch ($action) {
    //提交内容或更改
    case 'submitFeedback':
        $status = strFilter($_POST['status']);
        $fid = (int) strFilter($_POST['fid']);
        if (strFilter($_POST['title'] == '')) {
            die('请填写客户信息');
        }
        //状态变更的特殊处理
        if ($status === 'changed') {
            $status = $database->select('feedback_store', 'status', array(
                'AND' => array('fid[=]' => $fid, 'last_update[=]' => 1)));
            if (!$status)
                die('Line:' . __LINE__ . 'Catch error');
            else {
                $status = $status[0];
            }
        }
        if (strFilter($_POST['createNew']) == 1) {
            $fid = $database->max('feedback_store', 'fid') + 1;
            $currentVersion = 1;
        } elseif (strFilter($_POST['createNew']) == 0) {
            //获取当前的反馈ID
            $currentVersion = $database->max('feedback_store', 'version', array(
                        'fid' => $fid)) + 1;
            //之前改篇反馈信息版本的最新标记
            $database->update('feedback_store', array(
                'last_update' => 0), array('fid[=]' => $fid));
        } else {
            die('Line:' . __LINE__ . 'Catch error');
        }

        //插入新版本内容
        $database->insert('feedback_store', array(
            'fid' => $fid,
            'version' => $currentVersion,
            'status' => $status,
            'ftype' => strFilter($_POST['ftype']),
            'title' => strFilter($_POST['title']),
            'handler' => 'lyc',
            'fbody' => strFilter($_POST['fbody']),
            'last_update' => 1,
            'create_time' => date('Y-m-d')
        ));
        echo $fid;
        break;
    //获取列表
    case 'getFeedbackList':
        //SQL查询的条件

        if (strFilter($_POST['status']) === 'all') {
            if (isset($_POST['keyword']) && $_POST['keyword'] !== '') {
                $whereArr = array('AND' => array("last_update[=]" => 1));
            } else {
                $whereArr = array('AND' => array("last_update[=]" => 1
                        , "status[!]" => 'closed'));
            }
        } else {
            $whereArr = array('AND' => array("last_update[=]" => 1,
                    'status[=]' => strFilter($_POST['status'])));
        }
        if (isset($_POST['keyword']) && $_POST['keyword'] !== '') {
            $whereArr = array_merge($whereArr, array(
                'LIKE' => array('fbody' => '%' . strFilter($_POST['keyword']) . '%')));
        }
        $whereArr = array_merge($whereArr, array("LIMIT" => 20, 'ORDER' => 'id DESC'));
        $data = $database->select('feedback_store', array(
            'fid', 'title', 'status', 'ftype', 'create_time'), $whereArr);

        //按反馈状态过滤
        if (is_array($data)) {
            if (count($data) === 0) {
                die('<dd>Sorry,没有找到记录</dd>');
            }
            foreach ($data as $value) {
                //loadContent ajax加载feedback的详细内容
                $value['ftype'] === 'requirement' ? $value['ftype'] = '需' : FALSE;
                $value['ftype'] === 'bug' ? $value['ftype'] = '糟' : FALSE;
                echo "<dd><a href='javascript:void(0)' onclick='loadContent" .
                "({$value['fid']},0)'><span class='{$value['status']}'>[{$value['ftype']}]{$value['title']}</span>" .
                "</a></dd>";
            }
        } else {
            die('Line:' . __LINE__ . 'Catch error');
        }
        break;
    case 'loadContent':
        //获取反馈的最大版本数
        $fid = strFilter($_POST['fid']);
        $version = strFilter($_POST['version']);
        $maxVersion = $database->max('feedback_store', 'version', array(
            'fid' => $fid));

        $version == 0 ? $version = $maxVersion : FALSE;
        $data = $database->select('feedback_store', array(
            'title', 'ftype', 'fid', 'status', 'version', 'fbody', 'handler', 'create_time'), array(
            'AND' => array('version' => $version, 'fid' => $fid)));
        $data[0]['maxVersion'] = $maxVersion;
        echo json_encode($data);
        break;
    default:
        break;
}



//print($_POST['fbody']);

