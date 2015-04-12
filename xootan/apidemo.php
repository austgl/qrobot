<?php
define('APPURL', 'https://xootan.sinaapp.com/api/'); // API 接口地址
define('APPKEY', 'XT99965B2082D157'); // 安全效验码


if ($_POST['module'] == 'valid') { // 验证测试模块（返回 正确的 APPKEY 字样）
    echo APPKEY;
} elseif ($_GET['module'] == 'Logs') { // 仅显示最近 10 条 通知接口 记录
    $kv = new SaeKV();
    // 初始化KVClient对象
    if (!($kv->init())) {
        die('SAE KVDB...Error');
    }

    $ret = $kv->pkrget('Log_', 20);

    if (!$ret) {
        exit('暂无记录');
    }


    foreach ($ret as $f) {
        echo $f . "<br /><hr />";
    }
} elseif ($_GET['module'] == 'SearchGroup') { // 搜索 QQ 群，可以搜索其他 Q 群的基本信息
    $args = array(
        'key' => APPKEY,
        'groupid' => '427834876',
    );
    echo dfopen($_GET['module'], $args);
} elseif ($_GET['module'] == 'SearchFriend') { // 搜索一个 QQ 的资料信息
    $args = array(
        'key' => APPKEY,
        'qq' => '209796322',
    );
    echo dfopen($_GET['module'], $args);
} elseif ($_GET['module'] == 'GetGroup') { // 获取本群信息、成员信息（刷新群）
    $args = array(
        'key' => APPKEY,
    );
    echo dfopen($_GET['module'], $args);
} elseif ($_GET['module'] == 'GetGroupMember') { // 获取本群群成员资料
    $args = array(
        'key' => APPKEY,
        'qq' => '209796322',
    );
    echo dfopen($_GET['module'], $args);
} elseif ($_GET['module'] == 'AgreeMember') { // 同意他人加入群或者拒绝 （需要管理员权限）
    $args = array(
        'key' => APPKEY,
        'qq' => '209796322',
        'agree' => 'false',
        'message' => '你真的不可以加入这个群'
    );
    echo dfopen($_GET['module'], $args);
} elseif ($_GET['module'] == 'RemoveMember') { // 踢人，支持不再接收加群申请（需要管理员权限）
    $args = array(
        'key' => APPKEY,
        'qq' => '209796322',
        'black' => 'false'
    );
    echo dfopen($_GET['module'], $args);
} elseif ($_GET['module'] == 'SendMessage') { // 发送群消息（仅限本群）
    $args = array(
        'key' => APPKEY,
        'qq' => '209796322',
        'message' => '我是内容sssbbb' . "\n" . '呵呵哒！！！',
        'timeout' => 60 * 60 * 24 * 365
    );
    echo dfopen($_GET['module'], $args);
} elseif ($_GET['module'] == 'EditCard') { // 修改群成员名片（仅限本群）
    $args = array(
        'key' => APPKEY,
        'qq' => '209796322',
        'card' => '小糖去哪儿',
            //'timeout' => 60
    );
    echo dfopen($_GET['module'], $args);
} elseif ($_POST['module'] && $_POST['appkey'] == APPKEY) { // 接受群消息
    $_POST['appkey'] = substr_replace($_POST['appkey'], ' ******** ', 4, 8);

    dlogs(print_r($_POST, 'TRUE'));
    echo 'success';
} else {
    ?>

    <h2>读取接口</h2>

    <a href="?module=SearchGroup" target="_blank">搜索 QQ 群，可以搜索其他 Q 群的基本信息</a><br />
    <a href="?module=SearchFriend" target="_blank">搜索一个 QQ 的资料信息</a><br />
    <a href="?module=GetGroup" target="_blank">获取本群信息、成员信息（刷新群）</a><br />
    <a href="?module=GetGroupMember" target="_blank">获取本群群成员资料</a><br />

    <h2>写入接口</h2>

    <a href="?module=AgreeMember" target="_blank">同意他人加入群或者拒绝 （需要管理员权限）</a><br />
    <a href="?module=RemoveMember" target="_blank">踢人，支持不再接收加群申请（需要管理员权限）</a><br />
    <a href="?module=SendMessage" target="_blank">发送群消息（仅限本群）</a><br />
    <a href="?module=EditCard" target="_blank">修改群成员名片（仅限本群）</a><br />

    <h2>通知接口</h2>
    <a href="?module=Logs" target="_blank">仅显示最近 10 条记录</a>
    <?php
}

function dfopen($module, $args = array()) {

    $arg = '';

    while (list ($key, $val) = each($args)) {
        $arg .= $key . "=" . $val . "&";
    }

    $arg = substr($arg, 0, count($arg) - 2);

    //$text = file_get_contents(APPURL. $module.'?'.stripslashes($arg));


    $ch = curl_init();

    curl_setopt_array(
            $ch, array(
        CURLOPT_URL => APPURL . $module,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $arg
            )
    );
    $content = curl_exec($ch);
    if (curl_errno($ch)) {
        $text = curl_error($ch);
    } else {
        $text = $content;
    }
    curl_close($ch);


    $json = print_r(json_decode($text, TRUE), TRUE);
    if ($json) {
        $text .= '<hr />';
        $text .= $json;
    }
    return $text;
}

function dlogs($post) {
    $kv = new SaeKV();
    $kv->init();
    if (!($kv->init())) {
        die('SAE KVDB...Error');
    }
    $text = '执行日期：' . strftime("%Y%m%d%H%M%S", time()) . "\n";
    $text .= $post;

    $kv->set('Log_' . hexsub('fffffffffffff', uniqid()), $text);
}

function hexsub($left, $right) {
    $left = ltrim(strtolower($left), 0);
    $right = ltrim(strtolower($right), 0);
    $let = false;
    if (strlen($left) > strlen($right)) {
        $max = strlen($left);
        $right = implode(array_pad(array(), $max - strlen($right), '0')) . $right;
    } else if (strlen($left) == strlen($right)) {
        $max = strlen($left);
        for ($i = 0; $i < $max && !$let; $i++) {
            if (strcmp($left{$i}, $right{$i}) < 0) {
                $let = true;
            }
        }
    } else {
        $let = true;
        $max = strlen($right);
        $left = implode(array_pad(array(), $max - strlen($left), '0')) . $left;
    }

    if ($let) {
        $swp = $left;
        $left = $right;
        $right = $swp;
    }

    $str = '';
    for ($i = 0; $i < $max; $i++) {
        $str .= dechex(hexdec($left{$i}) - hexdec($right{$i}));
    }

    return ($let ? '-' : '') . ltrim($str, 0);
}
?>
