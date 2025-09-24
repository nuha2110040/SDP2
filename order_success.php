<?php
session_start();
$total = $_SESSION['order_total'] ?? 0;
unset($_SESSION['order_total']); // Clean up
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - RedStore</title>
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
                    <li><a href="user_details.php"><?php echo 'Welcome, ' . htmlspecialchars($_SESSION['user']); ?></a></li>
                </ul>
            </nav>
            <a href="cart.php"><img src="RedStore_Img/cart.png" width="30px" height="30px"></a>
            <img src="RedStore_Img/menu.png" class="menu-icon" onclick="menutoggle()">
        </div>
    </div>
    <div class="small-container" style="text-align: center; margin-top: 100px;">
        <i class="fa-solid fa-check-circle" style="font-size: 100px; color: #28a745;"></i>
        <h2>Order Placed Successfully!</h2>
        <p>Thank you for your purchase. Your total was <?php echo $total; ?> tk.</p>
        <p>We'll send a confirmation email soon. You can track your order in your account.</p>
        <a href="index.php" class="btn">Continue Shopping</a>
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
    </script>
</body>
</html>