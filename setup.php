<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

require_once 'lib/UserStore.php';
require_once 'lib/GoogleAuthenticator.php';

$store = new UserStore();
$ga    = new GoogleAuthenticator();

// If users already exist, this page is off-limits
if ($store->hasUsers()) {
    header('Location: login.php');
    exit;
}

$error   = '';
$step    = 1;

// ── Step 2 POST: verify MFA code and save the new user ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_step']) && $_POST['setup_step'] === '2') {
    $username = $_SESSION['setup_username'] ?? '';
    $password = $_SESSION['setup_password'] ?? '';
    $secret   = $_SESSION['setup_secret']   ?? '';
    $code     = preg_replace('/\s+/', '', $_POST['mfa_code'] ?? '');

    if (!$username || !$password || !$secret) {
        // Session lost — restart
        unset($_SESSION['setup_username'], $_SESSION['setup_password'], $_SESSION['setup_secret']);
        header('Location: setup.php');
        exit;
    }

    if ($ga->verifyCode($secret, $code)) {
        $store->addUser($username, $password, $secret);
        unset($_SESSION['setup_username'], $_SESSION['setup_password'], $_SESSION['setup_secret']);
        header('Location: login.php?message=Account+created.+Please+log+in.');
        exit;
    } else {
        $error = 'Code did not match. Make sure your phone clock is accurate and try again.';
        $step  = 2;
    }
}

// ── Step 1 POST: validate credentials and generate TOTP secret ──────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_step']) && $_POST['setup_step'] === '1') {
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password']  ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($username === '') {
        $error = 'Username is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9_\-]{3,32}$/', $username)) {
        $error = 'Username must be 3–32 characters (letters, numbers, _ or -).';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $password2) {
        $error = 'Passwords do not match.';
    } else {
        $_SESSION['setup_username'] = $username;
        $_SESSION['setup_password'] = $password;
        $_SESSION['setup_secret']   = $ga->createSecret();
        $step = 2;
    }
}

// ── Determine render step from session ──────────────────────────────────────
if ($step === 1 && isset($_SESSION['setup_secret'])) {
    $step = 2;
}

$setupUsername = $_SESSION['setup_username'] ?? '';
$setupSecret   = $_SESSION['setup_secret']   ?? '';
$qrUri         = $step === 2 ? $ga->getQRCodeUri('NXTM', $setupUsername, $setupSecret) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NXTM &mdash; Create Account</title>
    <link rel="stylesheet" href="Bootstrap/bootstrap.min.css">
    <?php if ($step === 2): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"
            integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4fn5L1LaDD3jgTn1FS42w=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <?php endif; ?>
    <style>
        body {
            background-color: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: sans-serif;
        }
        .setup-card {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }
        .setup-header {
            background-color: #001233;
            color: #fff;
            padding: 20px 28px 16px;
        }
        .setup-header h1 { font-size: 20px; margin: 0 0 2px; letter-spacing: 1px; }
        .setup-header p  { margin: 0; font-size: 12px; color: #8899bb; }
        .setup-body { padding: 24px 28px 28px; }
        .setup-body label {
            font-size: 12px;
            font-weight: 600;
            color: #444;
            display: block;
            margin-bottom: 4px;
        }
        .setup-body input[type="text"],
        .setup-body input[type="password"] {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            margin-bottom: 14px;
            box-sizing: border-box;
        }
        .setup-body input:focus { outline: none; border-color: #001233; }
        .btn-setup {
            width: 100%;
            background-color: #001233;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 10px;
            font-size: 14px;
            cursor: pointer;
            margin-top: 4px;
        }
        .btn-setup:hover { background-color: #002060; }
        .error-msg {
            background: #fff0f0;
            border: 1px solid #f5c6cb;
            color: #c00;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 13px;
            margin-bottom: 14px;
        }
        .qr-wrap {
            display: flex;
            justify-content: center;
            margin: 16px 0 12px;
        }
        .secret-box {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px 12px;
            font-family: monospace;
            font-size: 15px;
            letter-spacing: 2px;
            text-align: center;
            margin-bottom: 16px;
            word-break: break-all;
        }
        .hint {
            font-size: 12px;
            color: #666;
            margin-bottom: 16px;
            line-height: 1.5;
        }
        .mfa-input {
            text-align: center;
            letter-spacing: 6px;
            font-size: 22px !important;
            font-weight: bold;
        }
        .divider {
            border: none;
            border-top: 1px solid #eee;
            margin: 16px 0;
        }
    </style>
</head>
<body>
<div class="setup-card">
    <div class="setup-header">
        <h1>NXTM</h1>
        <p><?php echo $step === 1 ? 'Create your account' : 'Set up Google Authenticator'; ?></p>
    </div>
    <div class="setup-body">

        <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
        <!-- ── Step 1: username + password ─────────────────────────────── -->
        <form method="POST" action="setup.php" autocomplete="off">
            <input type="hidden" name="setup_step" value="1">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" autofocus
                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                   autocomplete="username">
            <label for="password">Password <span style="color:#999;font-weight:400;">(min 8 characters)</span></label>
            <input type="password" id="password" name="password" autocomplete="new-password">
            <label for="password2">Confirm Password</label>
            <input type="password" id="password2" name="password2" autocomplete="new-password">
            <button type="submit" class="btn-setup">Next &rarr;</button>
        </form>

        <?php else: ?>
        <!-- ── Step 2: scan QR code + verify ───────────────────────────── -->
        <p class="hint">
            Open <strong>Google Authenticator</strong> on your phone, tap <strong>+</strong>,
            and scan the QR code below. Then enter the 6-digit code to confirm.
        </p>

        <div class="qr-wrap">
            <div id="qrcode"></div>
        </div>

        <hr class="divider">
        <p class="hint" style="margin-bottom:4px;">Can't scan? Enter this key manually:</p>
        <div class="secret-box"><?php echo htmlspecialchars($setupSecret); ?></div>
        <hr class="divider">

        <form method="POST" action="setup.php" autocomplete="off">
            <input type="hidden" name="setup_step" value="2">
            <label for="mfa_code">Verification Code</label>
            <input type="text" id="mfa_code" name="mfa_code" class="mfa-input"
                   maxlength="6" inputmode="numeric" pattern="[0-9]{6}"
                   placeholder="000000" autofocus autocomplete="one-time-code">
            <button type="submit" class="btn-setup">Confirm &amp; Create Account</button>
        </form>

        <script>
            new QRCode(document.getElementById('qrcode'), {
                text: <?php echo json_encode($qrUri); ?>,
                width: 200,
                height: 200,
                colorDark: '#001233',
                colorLight: '#ffffff',
            });
        </script>
        <?php endif; ?>

    </div>
</div>
</body>
</html>
