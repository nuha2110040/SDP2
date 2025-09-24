<?php
session_start();
$login_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $json = file_get_contents('api/data.json');
    $data = json_decode($json, true);
    foreach ($data['users'] as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $username;
            $login_message = '<p class="alert alert-success">Login successful! Welcome, ' . htmlspecialchars($username) . '</p>';
            if (isset($_SESSION['login_redirect'])) {
                $redirect = $_SESSION['login_redirect'];
                unset($_SESSION['login_redirect']);
                header('Location: ' . $redirect);
                exit;
            }
            header('Location: index.php');
            exit;
        }
    }
    $login_message = '<p class="alert alert-danger">Invalid username or password.</p>';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    if ($username && $email && $password) {
        $json = file_get_contents('api/data.json');
        $data = json_decode($json, true);
        foreach ($data['users'] as $user) {
            if ($user['username'] === $username) {
                $login_message = '<p class="alert alert-danger">Username already exists.</p>';
                break;
            }
            if ($user['email'] === $email) {
                $login_message = '<p class="alert alert-danger">Email already exists.</p>';
                break;
            }
        }
        if (empty($login_message)) {
            $data['users'][] = [
                'id' => count($data['users']) + 1,
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ];
            file_put_contents('api/data.json', json_encode($data, JSON_PRETTY_PRINT));
            $login_message = '<p class="alert alert-success">Registration successful! You can now log in.</p>';
        }
    } else {
        $login_message = '<p class="alert alert-danger">Please fill all fields.</p>';
    }
}
if (isset($_GET['msg']) && $_GET['msg'] === 'login_required') {
    $login_message = '<p class="alert alert-info">Please log in to continue.</p>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account - RedStore</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="navbar">
            <div class="logo">
                <a href="index.php"><img src="RedStore_Img/logo.png" width="125px"></a>
            </div>
            <nav>
                <ul id="MenuItems">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="index.php#about">About</a></li>
                    <li><a href="index.php#order">Contact</a></li>
                    <li><a href="account.php"><?php echo isset($_SESSION['user']) ? 'Welcome, ' . htmlspecialchars($_SESSION['user']) : 'Account'; ?></a></li>
                </ul>
            </nav>
            <a href="cart.php"><img src="RedStore_Img/cart.png" width="30px" height="30px"></a>
            <img src="RedStore_Img/menu.png" class="menu-icon" onclick="menutoggle()">
        </div>
    </div>
    <div class="account-page">
        <div class="container">
            <div class="row">
                <div class="col-2">
                    <img src="RedStore_Img/image1.png" width="100%">
                </div>
                <div class="col-2">
                    <div class="form-container">
                        <div class="form-btn">
                            <span onclick="login()">LogIn</span>
                            <span onclick="register()">Register</span>
                            <hr id="Indicator">
                        </div>
                        <?php echo $login_message; ?>
                        <form id="LoginForm" method="POST" action="account.php">
                            <input type="text" name="username" placeholder="Username" required>
                            <input type="password" name="password" placeholder="Password" required>
                            <button type="submit" name="login" class="btn">LogIn</button>
                            
                        </form>
                        <form id="RegForm" method="POST" action="account.php">
                            <input type="text" name="username" placeholder="Username" required>
                            <input type="email" name="email" placeholder="Email" required>
                            <input type="password" name="password" placeholder="Password" required>
                            <button type="submit" name="register" class="btn">Register</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="footer-col-1">
                    <h3>Download Our App</h3>
                    <p>Download the app for Android and iOS mobile phones.</p>
                    <div class="app-logo">
                        <img src="RedStore_Img/play-store.png">
                        <img src="RedStore_Img/app-store.png">
                    </div>
                </div>
                <div class="footer-col-2">
                    <img src="RedStore_Img/logo-white.png" alt="Logo">
                    <p>Our purpose is to sustainably make the pleasure and benefits of sports accessible to the many.</p>
                </div>
                <div class="footer-col-3">
                    <h3>Useful Links</h3>
                    <ul>
                        <li>Coupons</li>
                        <li>Blog Posts</li>
                        <li>Return Policy</li>
                        <li>Join Affiliate</li>
                    </ul>
                </div>
                <div class="footer-col-4">
                    <h3>Follow Us</h3>
                    <ul>
                        <li>Facebook</li>
                        <li>Twitter</li>
                        <li>Instagram</li>
                        <li>YouTube</li>
                    </ul>
                </div>
            </div>
            <hr>
            <p class="copyright">Copyright 2025 - Samanta Ahmed</p>
        </div>
    </div>
    <script>
        var MenuItems = document.getElementById("MenuItems");
        MenuItems.style.maxHeight = "0px";
        function menutoggle() {
            if (MenuItems.style.maxHeight == "0px") {
                MenuItems.style.maxHeight = "200px";
            } else {
                MenuItems.style.maxHeight = "0px";
            }
        }
        var loginForm = document.getElementById("LoginForm");
        var RegForm = document.getElementById("RegForm");
        var Indicator = document.getElementById("Indicator");
        function register() {
            RegForm.style.transform = "translateX(0px)";
            loginForm.style.transform = "translateX(0px)";
            Indicator.style.transform = "translateX(100px)";
        }
        function login() {
            RegForm.style.transform = "translateX(300px)";
            loginForm.style.transform = "translateX(300px)";
            Indicator.style.transform = "translateX(0px)";
        }
    </script>
</body>
</html>