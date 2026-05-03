<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

// Already authenticated — go straight to app
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    header('Location: index.php');
    exit;
}

require_once 'lib/UserStore.php';
require_once 'lib/GoogleAuthenticator.php';

$store = new UserStore();
$ga    = new GoogleAuthenticator();

// No users exist yet — force first-time setup
if (!$store->hasUsers()) {
    header('Location: setup.php');
    exit;
}

$error = '';

// Handle all POST logic before any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedStep = $_POST['step'] ?? '';

    if ($postedStep === 'cancel') {
        unset($_SESSION['login_pending_user']);
        header('Location: login.php');
        exit;

    } elseif ($postedStep === '1') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($store->verifyPassword($username, $password)) {
            $_SESSION['login_pending_user'] = $username;
            // fall through to render step 2
        } else {
            $error = 'Invalid username or password.';
        }

    } elseif ($postedStep === '2') {
        $username = $_SESSION['login_pending_user'] ?? '';
        $code     = preg_replace('/\s+/', '', $_POST['mfa_code'] ?? '');
        $secret   = $store->getTotpSecret($username);

        if ($secret && $ga->verifyCode($secret, $code)) {
            session_regenerate_id(true);
            $_SESSION['authenticated'] = true;
            $_SESSION['username']      = $username;
            unset($_SESSION['login_pending_user']);
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid authenticator code. Please try again.';
        }
    }
}

$step = isset($_SESSION['login_pending_user']) ? 2 : 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NXTM &mdash; Login</title>
    <link rel="stylesheet" href="Bootstrap/bootstrap.min.css">
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
        .login-card {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 380px;
            overflow: hidden;
        }
        .login-header {
            background-color: #001233;
            color: #fff;
            padding: 20px 28px 16px;
        }
        .login-header h1 {
            font-size: 20px;
            margin: 0 0 2px;
            letter-spacing: 1px;
        }
        .login-header p {
            margin: 0;
            font-size: 12px;
            color: #8899bb;
        }
        .login-body {
            padding: 24px 28px 28px;
        }
        .login-body label {
            font-size: 12px;
            font-weight: 600;
            color: #444;
            display: block;
            margin-bottom: 4px;
        }
        .login-body input[type="text"],
        .login-body input[type="password"] {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            margin-bottom: 14px;
            box-sizing: border-box;
        }
        .login-body input:focus {
            outline: none;
            border-color: #001233;
        }
        .btn-login {
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
        .btn-login:hover { background-color: #002060; }
        .error-msg {
            background: #fff0f0;
            border: 1px solid #f5c6cb;
            color: #c00;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 13px;
            margin-bottom: 14px;
        }
        .step-hint {
            font-size: 12px;
            color: #666;
            margin-bottom: 16px;
            line-height: 1.5;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 12px;
            font-size: 12px;
            color: #888;
            cursor: pointer;
            text-decoration: underline;
            background: none;
            border: none;
            padding: 0;
            width: 100%;
        }
        .back-link:hover { color: #333; }
        .mfa-input {
            text-align: center;
            letter-spacing: 6px;
            font-size: 22px !important;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-header">
        <h1>NXTM</h1>
        <p><?php echo $step === 1 ? 'Sign in to continue' : 'Two-factor authentication'; ?></p>
    </div>
    <div class="login-body">

        <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
        <form method="POST" action="login.php" autocomplete="off">
            <input type="hidden" name="step" value="1">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" autofocus autocomplete="username">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="current-password">
            <button type="submit" class="btn-login">Continue</button>
        </form>

        <?php else: ?>
        <form method="POST" action="login.php" autocomplete="off">
            <input type="hidden" name="step" value="2">
            <p class="step-hint">
                Open Google Authenticator and enter the 6-digit code for
                <strong><?php echo htmlspecialchars($_SESSION['login_pending_user'] ?? ''); ?></strong>.
            </p>
            <label for="mfa_code">Authenticator Code</label>
            <input type="text" id="mfa_code" name="mfa_code" class="mfa-input"
                   maxlength="6" inputmode="numeric" pattern="[0-9]{6}"
                   placeholder="000000" autofocus autocomplete="one-time-code">
            <button type="submit" class="btn-login">Verify</button>
        </form>
        <form method="POST" action="login.php">
            <input type="hidden" name="step" value="cancel">
            <button type="submit" class="back-link">&#8592; Back to login</button>
        </form>
        <?php endif; ?>

    </div>
</div>
</body>
</html>
