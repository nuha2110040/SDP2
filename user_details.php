<?php
session_start();
if (!isset($_SESSION['user'])) {
    $_SESSION['login_redirect'] = 'user_details.php';
    header('Location: account.php?msg=login_required');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    unset($_SESSION['user']);
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details - RedStore</title>
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
                    <li><a href="user_details.php">Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?></a></li>
                </ul>
            </nav>
            <a href="cart.php"><img src="RedStore_Img/cart.png" width="30px" height="30px"></a>
            <img src="RedStore_Img/menu.png" class="menu-icon" onclick="menutoggle()">
        </div>
    </div>
    <div class="small-container">
        <h2 class="title">User Dashboard</h2>
        <?php
        $json = @file_get_contents('api/data.json');
        if ($json === false) {
            echo '<p class="alert alert-danger">Error: Could not read api/data.json.</p>';
        } else {
            $data = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                echo '<p class="alert alert-danger">Error: Invalid JSON format in api/data.json.</p>';
            } else {
                // Personal Details
                $user_data = null;
                foreach ($data['users'] as $user) {
                    if ($user['username'] === $_SESSION['user']) {
                        $user_data = $user;
                        break;
                    }
                }
                if ($user_data): ?>
                    <h3>Personal Details</h3>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user_data['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
                    <p><strong>User ID:</strong> <?php echo htmlspecialchars($user_data['id']); ?></p>
                <?php else: ?>
                    <p class="alert alert-danger">Error: User data not found.</p>
                <?php endif; ?>
                <!-- Order History -->
                <h3>Order History</h3>
                <?php
                $user_orders = array_filter($data['orders'], function($order) {
                    return $order['user'] === $_SESSION['user'];
                });
                if (empty($user_orders)) {
                    echo '<p>No orders found.</p>';
                } else {
                    echo '<table class="admin-table">';
                    echo '<tr>';
                    echo '<th>Order ID</th>';
                    echo '<th>Date</th>';
                    echo '<th>Items</th>';
                    echo '<th>Total (tk)</th>';
                    echo '<th>Status</th>';
                    echo '<th>Contact Number</th>';
                    echo '<th>Address</th>';
                    echo '<th>Payment Method</th>';
                    echo '</tr>';
                    foreach ($user_orders as $order) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($order['id']) . '</td>';
                        echo '<td>' . htmlspecialchars($order['date']) . '</td>';
                        echo '<td>';
                        foreach ($order['items'] as $item) {
                            $size = isset($item['size']) ? ' (Size: ' . htmlspecialchars($item['size']) . ')' : '';
                            echo htmlspecialchars($item['name']) . ' (Qty: ' . htmlspecialchars($item['quantity']) . ')' . $size . '<br>';
                        }
                        echo '</td>';
                        echo '<td>' . htmlspecialchars($order['total']) . '</td>';
                        echo '<td>' . htmlspecialchars($order['status'] ?? 'N/A') . '</td>';
                        echo '<td>' . htmlspecialchars($order['contact_number'] ?? 'N/A') . '</td>';
                        echo '<td>' . htmlspecialchars($order['address'] ?? 'N/A') . '</td>';
                        echo '<td>' . htmlspecialchars($order['payment_method'] ?? 'N/A') . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                }
            }
        }
        ?>
        <form method="POST" action="">
            <button type="submit" name="logout" class="btn">Logout</button>
        </form>
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