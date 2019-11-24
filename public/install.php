<?php
/**
 * EasyAdmin安装程序
 *
 * 安装完成后建议删除此文件
 * @author xiaoyaor
 * @website https://www.easyadmin.vip
 */
// error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
// ini_set('display_errors', '1');

// 定义目录分隔符
define('DS', DIRECTORY_SEPARATOR);

// 定义根目录
define('ROOT_PATH', __DIR__ . DS . '..' . DS);

// 定义应用目录
define('APP_PATH', ROOT_PATH . 'app' . DS);

// 定义应用目录
define('CONFIG_PATH', ROOT_PATH . 'config' . DS);

// 安装包目录
define('INSTALL_PATH', APP_PATH . 'admin' . DS . 'command' . DS . 'Install' . DS);

// 判断文件或目录是否有写的权限
function is_really_writable($file)
{
    if (DIRECTORY_SEPARATOR == '/' AND @ ini_get("safe_mode") == false) {
        return is_writable($file);
    }
    if (!is_file($file) OR ($fp = @fopen($file, "r+")) === false) {
        return false;
    }

    fclose($fp);
    return true;
}

//写ini文件
function write_ini_file($assoc_arr, $path)
{
    $content = "";

        foreach ($assoc_arr as $key=>$elem)
        {
            if(!is_array($elem))
            {
                $content .= $key." = ".$elem."\n";
            }
            else
            {
                $content .= "[".$key."]\n";
                foreach ($elem as $key2=>$elem2)
                {
                    if(is_array($elem2))
                    {
                        for($i=0;$i<count($elem2);$i++)
                        {
                            $content .= $key2."[] = ".$elem2[$i]."\n";
                        }
                    }
                    else if($elem2=="") $content .= $key2." = \n";
                    else $content .= $key2." = ".$elem2."\n";
                }
            }
            $content .= "\n";
        }

    if (!$handle = fopen($path, 'w'))
    {
        return false;
    }
    if (!fwrite($handle, $content))
    {
        return false;
    }
    fclose($handle);
    return true;
}


$sitename = "EasyAdmin";

$link = array(
    'qqun'  => "https://shang.qq.com/wpa/qunwpa?idkey=ce12bc3cbc9a2ccbca97d287609f61dffc0347a62a204780271be3ef12f70129",
    'gitee' => 'https://gitee.com/',
    'github' => 'https://github.com/xiaoyaor/EasyAdmin',
    'home'  => 'https://www.easyadmin.vip?ref=install',
    'ask' => 'https://ask.easyadmin.vip?ref=install',
    'doc'   => 'https://doc.easyadmin.vip?ref=install',
);

// 检测目录是否存在
$checkDirs = [
    'app',
    'vendor',
    'public' . DS . 'assets' . DS . 'libs'
];
//缓存目录
$runtimeDir = APP_PATH . 'runtime';

//错误信息
$errInfo = '';

//数据库配置文件
$dbConfigFile = APP_PATH . '../'.'config/'.'database.php';
$dbConfigFileEnv = APP_PATH . '../.env';

//后台入口文件
$adminFile = ROOT_PATH . 'public' . DS . 'admin.php';

// 锁定的文件
$lockFile = INSTALL_PATH . 'install.lock';
if (is_file($lockFile)) {
    $errInfo = "当前已经安装{$sitename}，如果需要重新安装，请手动移除app/admin/command/Install/install.lock文件";
} else {
    if (version_compare(PHP_VERSION, '7.1.0', '<')) {
        $errInfo = "当前版本(" . PHP_VERSION . ")过低，请使用PHP7.1以上版本";
    } else {
        if (!extension_loaded("PDO")) {
            $errInfo = "当前未开启PDO，无法进行安装";
        } else {
            if (!is_really_writable($dbConfigFile)) {
                $open_basedir = ini_get('open_basedir');
                if ($open_basedir) {
                    $dirArr = explode(PATH_SEPARATOR, $open_basedir);
                    if ($dirArr && in_array(__DIR__, $dirArr)) {
                        $errInfo = '当前服务器因配置了open_basedir，导致无法读取父目录<br><a href="https://ask.easyadmin.vip/thread/1145?ref=install" target="_blank">点击查看解决办法</a>';
                    }
                }
                if (!$errInfo) {
                    $errInfo = '当前权限不足，无法写入配置文件app/database.php<br><a href="https://ask.easyadmin.vip/thread/1145?ref=install" target="_blank">点击查看解决办法</a>';
                }
            } else {
                $dirArr = [];
                foreach ($checkDirs as $k => $v) {
                    if (!is_dir(ROOT_PATH . $v)) {
                        $errInfo = '当前代码仅包含核心代码，请前往官网下载完整包或资源包覆盖后再尝试安装，<a href="https://www.easyadmin.vip/download.html?ref=install" target="_blank">立即前往下载</a>';
                        break;
                    }
                }
            }
        }
    }
}
// 当前是POST请求
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($errInfo) {
        echo $errInfo;
        exit;
    }
    $err = '';
    $mysqlHostname = isset($_POST['mysqlHost']) ? $_POST['mysqlHost'] : '127.0.0.1';
    $mysqlHostport = isset($_POST['mysqlHostport']) ? $_POST['mysqlHostport'] : 3306;
    $hostArr = explode(':', $mysqlHostname);
    if (count($hostArr) > 1) {
        $mysqlHostname = $hostArr[0];
        $mysqlHostport = $hostArr[1];
    }
    $mysqlUsername = isset($_POST['mysqlUsername']) ? $_POST['mysqlUsername'] : 'root';
    $mysqlPassword = isset($_POST['mysqlPassword']) ? $_POST['mysqlPassword'] : '';
    $mysqlDatabase = isset($_POST['mysqlDatabase']) ? $_POST['mysqlDatabase'] : 'easyadmin';
    $mysqlPrefix = isset($_POST['mysqlPrefix']) ? $_POST['mysqlPrefix'] : 'ea_';
    $adminUsername = isset($_POST['adminUsername']) ? $_POST['adminUsername'] : 'admin';
    $adminPassword = isset($_POST['adminPassword']) ? $_POST['adminPassword'] : '123456';
    $adminPasswordConfirmation = isset($_POST['adminPasswordConfirmation']) ? $_POST['adminPasswordConfirmation'] : '123456';
    $adminEmail = isset($_POST['adminEmail']) ? $_POST['adminEmail'] : 'admin@admin.com';

    if (!preg_match("/^\w{3,12}$/", $adminUsername)) {
        echo "用户名只能由3-12位数字、字母、下划线组合";
        exit;
    }
    if (!preg_match("/^[\S]{6,16}$/", $adminPassword)) {
        echo "密码长度必须在6-16位之间，不能包含空格";
        exit;
    }
    if ($adminPassword !== $adminPasswordConfirmation) {
        echo "两次输入的密码不一致";
        exit;
    }

    try {
        //检测能否读取安装文件
        $sql = @file_get_contents(INSTALL_PATH . 'easyadmin.sql');
        if (!$sql) {
            throw new Exception("无法读取app/admin/command/Install/easyadmin.sql文件，请检查是否有读权限");
        }
        $sql = str_replace("`ea_", "`{$mysqlPrefix}", $sql);
        $pdo = new PDO("mysql:host={$mysqlHostname};port={$mysqlHostport}", $mysqlUsername, $mysqlPassword, array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        ));

        //检测是否支持innodb存储引擎
        $pdoStatement = $pdo->query("SHOW VARIABLES LIKE 'innodb_version'");
        $result = $pdoStatement->fetch();
        if (!$result) {
            throw new Exception("当前数据库不支持innodb存储引擎，请开启后再重新尝试安装");
        }

        $pdo->query("CREATE DATABASE IF NOT EXISTS `{$mysqlDatabase}` CHARACTER SET utf8 COLLATE utf8_general_ci;");

        $pdo->query("USE `{$mysqlDatabase}`");

        $pdo->exec($sql);

        $EnvData = array(
            'APP_DEBUG' => 'true',
            'APP' => array(
                'DEFAULT_TIMEZONE' => 'sia/Shanghai',
            ),
            'DATABASE' => array(
                'TYPE'     => 'mysql',
                'HOSTNAME' => $mysqlHostname,
                'DATABASE' => $mysqlDatabase,
                'USERNAME' => $mysqlUsername,
                'PASSWORD' => $mysqlPassword,
                'HOSTPORT' => $mysqlHostport,
                'CHARSET'  => 'utf8',
                'DEBUG'    => 'true',
                'PREFIX'   => $mysqlPrefix,
            ),
            'LANG' => array(
                'default_lang' => 'zh-cn',
            )
        );
        $result=write_ini_file($EnvData, $dbConfigFileEnv);

        if (!$result) {
            throw new Exception("无法写入数据库信息到.env文件，请检查是否有写权限");
        }

        //检测能否成功写入lock文件
        $result = @file_put_contents($lockFile, 1);
        if (!$result) {
            throw new Exception("无法写入安装锁定到app/admin/command/Install/install.lock文件，请检查是否有写权限");
        }

        $newSalt = substr(md5(uniqid(true)), 0, 6);
        $newPassword = md5(md5($adminPassword) . $newSalt);
        $pdo->query("UPDATE {$mysqlPrefix}admin SET username = '{$adminUsername}', email = '{$adminEmail}',password = '{$newPassword}', salt = '{$newSalt}' WHERE username = 'admin'");

        $x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $adminName = substr(str_shuffle(str_repeat($x, ceil(10 / strlen($x)))), 1, 10);
        //检测能否读取配置文件
        $app = @file_get_contents(CONFIG_PATH . 'app.php');
        if (!$app) {
            throw new Exception("无法读取config/app.php文件，请检查是否有读权限");
        }
        $app = str_replace("XnZUrsbCijk", $adminName, $app);
        //检测能否成功写入配置文件
        $result = @file_put_contents(CONFIG_PATH . 'app.php', $app);
        if (!$result) {
            throw new Exception("无法写入到config/app.php文件，请检查是否有写权限");
        }

        echo "success|{$adminName}";
    } catch (PDOException $e) {
        $err = $e->getMessage();
    } catch (Exception $e) {
        $err = $e->getMessage();
    }
    echo $err;
    exit;
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>安装<?php echo $sitename; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1">
    <meta name="renderer" content="webkit">

    <style>
        body {
            background: #fff;
            margin: 0;
            padding: 0;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body, input, button {
            font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, 'Microsoft Yahei', Arial, sans-serif;
            font-size: 14px;
            color: #7E96B3;
        }

        .container {
            max-width: 480px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }

        a {
            color: #18bc9c;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        h1 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        h2 {
            font-size: 28px;
            font-weight: normal;
            color: #3C5675;
            margin-bottom: 0;
            margin-top: 0;
        }

        form {
            margin-top: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group .form-field:first-child input {
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }

        .form-group .form-field:last-child input {
            border-bottom-left-radius: 4px;
            border-bottom-right-radius: 4px;
        }

        .form-field input {
            background: #EDF2F7;
            margin: 0 0 1px;
            border: 2px solid transparent;
            transition: background 0.2s, border-color 0.2s, color 0.2s;
            width: 100%;
            padding: 15px 15px 15px 180px;
            box-sizing: border-box;
        }

        .form-field input:focus {
            border-color: #18bc9c;
            background: #fff;
            color: #444;
            outline: none;
        }

        .form-field label {
            float: left;
            width: 160px;
            text-align: right;
            margin-right: -160px;
            position: relative;
            margin-top: 18px;
            font-size: 14px;
            pointer-events: none;
            opacity: 0.7;
        }

        button, .btn {
            background: #3C5675;
            color: #fff;
            border: 0;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            padding: 15px 30px;
            -webkit-appearance: none;
        }

        button[disabled] {
            opacity: 0.5;
        }

        .form-buttons {
            height: 52px;
            line-height: 52px;
        }

        .form-buttons .btn {
            margin-right: 5px;
        }

        #error, .error, #success, .success, #warmtips, .warmtips {
            background: #D83E3E;
            color: #fff;
            padding: 15px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        #success {
            background: #3C5675;
        }

        #error a, .error a {
            color: white;
            text-decoration: underline;
        }

        #warmtips {
            background: #ffcdcd;
            font-size: 14px;
            color: #e74c3c;
        }

        #warmtips a {
            background: #ffffff7a;
            display: block;
            height: 30px;
            line-height: 30px;
            margin-top: 10px;
            color: #e21a1a;
            border-radius: 3px;
        }
    </style>
</head>

<body>
<div class="container">
    <h1>
        <img src="/assets/img/qrcode.png" width="150px" height="150px" >
    </h1>
    <h2>安装 <?php echo $sitename; ?></h2>
    <div>

        <p>若你在安装中遇到麻烦可点击 <a href="<?php echo $link['doc']; ?>" target="_blank">安装文档</a> <a
                    href="<?php echo $link['ask']; ?>" target="_blank">问答社区</a> <a
                    href="<?php echo $link['qqun']; ?>">QQ交流群</a></p>
        <!--<p><?php echo $sitename; ?>还支持在命令行php think install一键安装</p>-->

        <form method="post">
            <?php if ($errInfo): ?>
                <div class="error">
                    <?php echo $errInfo; ?>
                </div>
            <?php endif; ?>
            <div id="error" style="display:none"></div>
            <div id="success" style="display:none"></div>
            <div id="warmtips" style="display:none"></div>

            <div class="form-group">
                <div class="form-field">
                    <label>MySQL 数据库地址</label>
                    <input type="text" name="mysqlHost" value="127.0.0.1" required="">
                </div>

                <div class="form-field">
                    <label>MySQL 数据库名</label>
                    <input type="text" name="mysqlDatabase" value="easyadmin" required="">
                </div>

                <div class="form-field">
                    <label>MySQL 用户名</label>
                    <input type="text" name="mysqlUsername" value="root" required="">
                </div>

                <div class="form-field">
                    <label>MySQL 密码</label>
                    <input type="password" name="mysqlPassword">
                </div>

                <div class="form-field">
                    <label>MySQL 数据表前缀</label>
                    <input type="text" name="mysqlPrefix" value="ea_">
                </div>

                <div class="form-field">
                    <label>MySQL 端口号</label>
                    <input type="number" name="mysqlHostport" value="3306">
                </div>
            </div>

            <div class="form-group">
                <div class="form-field">
                    <label>管理者用户名</label>
                    <input name="adminUsername" value="admin" required=""/>
                </div>

                <div class="form-field">
                    <label>管理者Email</label>
                    <input name="adminEmail" value="admin@admin.com" required="">
                </div>

                <div class="form-field">
                    <label>管理者密码</label>
                    <input type="password" name="adminPassword" required="">
                </div>

                <div class="form-field">
                    <label>重复密码</label>
                    <input type="password" name="adminPasswordConfirmation" required="">
                </div>
            </div>

            <div class="form-buttons">
                <button type="submit" <?php echo $errInfo ? 'disabled' : '' ?>>点击安装</button>
            </div>
        </form>

        <!-- jQuery -->
        <script src="https://cdn.staticfile.org/jquery/2.1.4/jquery.min.js"></script>

        <script>
            $(function () {
                $('form :input:first').select();

                $('form').on('submit', function (e) {
                    e.preventDefault();
                    var form = this;
                    var $button = $(this).find('button')
                        .text('安装中...')
                        .prop('disabled', true);

                    $.post('', $(this).serialize())
                        .done(function (ret) {
                            if (ret.substr(0, 7) === 'success') {
                                var retArr = ret.split(/\|/);
                                $('#error').hide();
                                $(".form-group", form).remove();
                                $button.remove();
                                $("#success").text("安装成功！开始你的<?php echo $sitename; ?>之旅吧！").show();

                                $buttons = $(".form-buttons", form);
                                $('<a class="btn" href="./">访问首页</a>').appendTo($buttons);

                                if (typeof retArr[1] !== 'undefined' && retArr[1] !== '') {
                                    var url = location.href.replace(/install\.php/, retArr[1]);
                                    $("#warmtips").html('温馨提示：请将以下后台登录入口添加到你的收藏夹，为了你的安全，不要泄漏或发送给他人！如有泄漏请及时修改！<a href="' + url + '">' + url + '</a>').show();
                                    $('<a class="btn" href="' + url + '" id="btn-admin" style="background:#18bc9c">访问后台</a>').appendTo($buttons);
                                }
                                localStorage.setItem("fastep", "installed");
                            } else {
                                $('#error').show().text(ret);
                                $button.prop('disabled', false).text('点击安装');
                                $("html,body").animate({
                                    scrollTop: 0
                                }, 500);
                            }
                        })
                        .fail(function (data) {
                            $('#error').show().text('发生错误:\n\n' + data.responseText);
                            $button.prop('disabled', false).text('点击安装');
                            $("html,body").animate({
                                scrollTop: 0
                            }, 500);
                        });

                    return false;
                });
            });
        </script>
    </div>
</div>
</body>
</html>