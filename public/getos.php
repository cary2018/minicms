<?php
// 获取客户端操作系统信息
function GetOs() {
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $os = '未知操作系统'; // 默认值

    // 操作系统检测规则（按优先级排序）
    $rules = [
        // Windows 系列（从新到旧）
        ['patterns' => ['Windows NT 10.0', 'Windows 10'], 'os' => 'Windows 10'],
        ['patterns' => ['Windows NT 6.3', 'Windows 8.1'], 'os' => 'Windows 8.1'],
        ['patterns' => ['Windows NT 6.2', 'Windows 8'], 'os' => 'Windows 8'],
        ['patterns' => ['Windows NT 6.1', 'Windows 7'], 'os' => 'Windows 7'],
        ['patterns' => ['Windows NT 6.0', 'Windows Vista'], 'os' => 'Windows Vista'],
        ['patterns' => ['Windows NT 5.2'], 'os' => 'Windows Server 2003'],
        ['patterns' => ['Windows NT 5.1', 'Windows XP'], 'os' => 'Windows XP'],
        ['patterns' => ['Windows NT 5.0', 'Windows 2000'], 'os' => 'Windows 2000'],
        ['patterns' => ['Win95', 'Windows 95'], 'os' => 'Windows 95'],
        ['patterns' => ['Win98', 'Windows 98'], 'os' => 'Windows 98'],
        ['patterns' => ['WinNT', 'Windows NT'], 'os' => 'Windows NT'],

        // macOS 系列
        ['patterns' => ['Mac OS X 10_15', 'Mac OS X 10.15', 'Catalina'], 'os' => 'macOS Catalina'],
        ['patterns' => ['Mac OS X 10_14', 'Mac OS X 10.14', 'Mojave'], 'os' => 'macOS Mojave'],
        ['patterns' => ['Mac OS X 10_13', 'Mac OS X 10.13', 'High Sierra'], 'os' => 'macOS High Sierra'],
        ['patterns' => ['Mac OS X 10_12', 'Mac OS X 10.12', 'Sierra'], 'os' => 'macOS Sierra'],
        ['patterns' => ['Mac OS X 10_11', 'Mac OS X 10.11', 'El Capitan'], 'os' => 'OS X El Capitan'],
        ['patterns' => ['Mac OS X 10_10', 'Mac OS X 10.10', 'Yosemite'], 'os' => 'OS X Yosemite'],
        ['patterns' => ['Mac OS X 10_9', 'Mac OS X 10.9', 'Mavericks'], 'os' => 'OS X Mavericks'],
        ['patterns' => ['Mac OS X'], 'os' => 'macOS'],
        ['patterns' => ['Macintosh'], 'os' => 'Macintosh'],

        // 移动设备
        ['patterns' => ['iPhone', 'iOS'], 'os' => 'iPhone iOS'],
        ['patterns' => ['iPad', 'iPadOS'], 'os' => 'iPad iPadOS'],
        ['patterns' => ['Android'], 'os' => 'Android'],

        // Linux/Unix 系列
        ['patterns' => ['Linux'], 'os' => 'Linux'],
        ['patterns' => ['Ubuntu'], 'os' => 'Ubuntu Linux'],
        ['patterns' => ['Fedora'], 'os' => 'Fedora Linux'],
        ['patterns' => ['Debian'], 'os' => 'Debian Linux'],
        ['patterns' => ['CentOS'], 'os' => 'CentOS Linux'],
        ['patterns' => ['FreeBSD'], 'os' => 'FreeBSD'],
        ['patterns' => ['OpenBSD'], 'os' => 'OpenBSD'],
        ['patterns' => ['NetBSD'], 'os' => 'NetBSD'],
        ['patterns' => ['SunOS', 'Solaris'], 'os' => 'Solaris'],

        // 其他
        ['patterns' => ['Chrome OS', 'CrOS'], 'os' => 'Chrome OS'],
        ['patterns' => ['Xbox'], 'os' => 'Xbox OS'],
    ];

    // 遍历规则进行匹配
    foreach ($rules as $rule) {
        foreach ($rule['patterns'] as $pattern) {
            if (stripos($agent, $pattern) !== false) {
                $os = $rule['os'];
                break 2; // 跳出两层循环
            }
        }
    }

    return $os;
}

// 获取客户端浏览器信息
function GetBrowser() {
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $browser = '未知浏览器'; // 默认值

    // 浏览器检测规则（按优先级排序）
    $rules = [
        ['patterns' => ['Edg', 'Edge'], 'browser' => 'Microsoft Edge'],
        ['patterns' => ['OPR', 'Opera'], 'browser' => 'Opera'],
        ['patterns' => ['Chrome', 'CriOS'], 'browser' => 'Google Chrome'],
        ['patterns' => ['Firefox', 'FxiOS'], 'browser' => 'Mozilla Firefox'],
        ['patterns' => ['Safari'], 'browser' => 'Apple Safari'],
        ['patterns' => ['MSIE', 'Trident'], 'browser' => 'Internet Explorer'],
        ['patterns' => ['Vivaldi'], 'browser' => 'Vivaldi'],
        ['patterns' => ['Brave'], 'browser' => 'Brave'],
        ['patterns' => ['UCBrowser'], 'browser' => 'UC Browser'],
        ['patterns' => ['SamsungBrowser'], 'browser' => 'Samsung Internet'],
        ['patterns' => ['WeChat', 'MicroMessenger'], 'browser' => '微信内置浏览器'],
    ];

    // 遍历规则进行匹配
    foreach ($rules as $rule) {
        foreach ($rule['patterns'] as $pattern) {
            if (stripos($agent, $pattern) !== false) {
                $browser = $rule['browser'];
                break 2; // 跳出两层循环
            }
        }
    }

    return $browser;
}

// 获取设备类型
function GetDeviceType() {
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if (stripos($agent, 'Mobile') !== false ||
        stripos($agent, 'Android') !== false ||
        stripos($agent, 'iPhone') !== false ||
        stripos($agent, 'iPad') !== false) {
        return '移动设备';
    } elseif (stripos($agent, 'Tablet') !== false || stripos($agent, 'iPad') !== false) {
        return '平板设备';
    } else {
        return '桌面设备';
    }
}

// 获取完整的用户代理信息
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '未获取到用户代理信息';
$osInfo = GetOs();
$browserInfo = GetBrowser();
$deviceType = GetDeviceType();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>客户端操作系统检测工具</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', 'Microsoft YaHei', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
            color: #333;
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background: rgba(255, 255, 255, 0.92);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 800px;
            width: 100%;
            transition: all 0.3s ease;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 2.5rem;
            position: relative;
            padding-bottom: 15px;
        }

        h1:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 4px;
            background: linear-gradient(to right, #3498db, #2c3e50);
            border-radius: 2px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .info-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .info-card h2 {
            font-size: 1.3rem;
            color: #3498db;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .info-card h2 i {
            margin-right: 10px;
            font-size: 1.6rem;
        }

        .info-card p {
            font-size: 1.25rem;
            color: #2c3e50;
            font-weight: 600;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }

        .user-agent {
            background: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-family: monospace;
            overflow-x: auto;
            font-size: 1.1rem;
        }

        .user-agent h3 {
            color: #3498db;
            margin-bottom: 15px;
            font-size: 1.4rem;
        }

        .os-icons {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 30px 0;
            flex-wrap: wrap;
        }

        .os-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.3s ease;
        }

        .os-icon:hover {
            transform: scale(1.1);
        }

        .os-icon i {
            font-size: 2.5rem;
            color: #3498db;
            margin-bottom: 10px;
        }

        .os-icon span {
            font-weight: 600;
            color: #2c3e50;
        }

        .highlight {
            background: linear-gradient(120deg, #3498db, #2ecc71);
            color: white;
            padding: 0 8px;
            border-radius: 4px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: #7f8c8d;
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 25px;
            }

            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>客户端操作系统检测工具</h1>

    <div class="user-agent">
        <h3>用户代理信息</h3>
        <p><?= htmlspecialchars($userAgent) ?></p>
    </div>

    <div class="info-grid">
        <div class="info-card">
            <h2><i>💻</i> 操作系统信息</h2>
            <p><?= $osInfo ?></p>
        </div>

        <div class="info-card">
            <h2><i>🌐</i> 浏览器信息</h2>
            <p><?= $browserInfo ?></p>
        </div>

        <div class="info-card">
            <h2><i>📱</i> 设备类型</h2>
            <p><?= $deviceType ?></p>
        </div>

        <div class="info-card">
            <h2><i>🔍</i> 检测说明</h2>
            <p>本系统可检测Windows 10等30+操作系统，支持桌面和移动设备识别</p>
        </div>
    </div>

    <div class="os-icons">
        <div class="os-icon">
            <i>🪟</i>
            <span>Windows</span>
        </div>
        <div class="os-icon">
            <i>🍎</i>
            <span>macOS</span>
        </div>
        <div class="os-icon">
            <i>🐧</i>
            <span>Linux</span>
        </div>
        <div class="os-icon">
            <i>🤖</i>
            <span>Android</span>
        </div>
        <div class="os-icon">
            <i>📱</i>
            <span>iOS</span>
        </div>
    </div>

    <div class="footer">
        <p>检测时间: <?= date('Y-m-d H:i:s') ?> | 本工具使用PHP检测客户端操作系统信息</p>
    </div>
</div>
</body>
</html>