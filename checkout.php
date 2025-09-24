<?php
session_start();
if (!isset($_SESSION['user'])) {
    $_SESSION['login_redirect'] = 'checkout.php';
    header('Location: account.php?msg=login_required');
    exit;
}
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php?error=empty_cart');
    exit;
}
// Process order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $contact_number = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    if (empty($contact_number) || empty($address)) {
        $error = 'Contact number and address are required.';
    } else {
        $json = @file_get_contents('api/data.json');
        if ($json === false) {
            error_log('Failed to read api/data.json in checkout.php');
            $error = 'Error reading data file.';
        } else {
            $data = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('JSON decode error in checkout.php: ' . json_last_error_msg());
                $error = 'Error processing data file.';
            } else {
                $subtotal = array_sum(array_map(function($item) {
                    return isset($item['price'], $item['quantity']) ? $item['price'] * $item['quantity'] : 0;
                }, $_SESSION['cart']));
                if (empty($_SESSION['cart']) || $subtotal <= 0) {
                    $error = 'Cannot place order with an empty cart or zero total.';
                } else {
                    // Validate stock availability
                    $insufficient_stock = false;
                    foreach ($_SESSION['cart'] as $cart_item) {
                        if (!isset($cart_item['id'], $cart_item['quantity'])) {
                            continue;
                        }
                        foreach ($data['products'] as $product) {
                            if ($product['id'] === $cart_item['id']) {
                                if (!isset($product['stock']) || $product['stock'] < $cart_item['quantity']) {
                                    $insufficient_stock = true;
                                    $error = 'Insufficient stock for ' . htmlspecialchars($cart_item['name']);
                                    break 2;
                                }
                                break;
                            }
                        }
                    }
                    if (!$insufficient_stock) {
                        // Update stock
                        foreach ($_SESSION['cart'] as $cart_item) {
                            if (!isset($cart_item['id'], $cart_item['quantity'])) {
                                continue;
                            }
                            for ($i = 0; $i < count($data['products']); $i++) {
                                if ($data['products'][$i]['id'] === $cart_item['id']) {
                                    $data['products'][$i]['stock'] -= $cart_item['quantity'];
                                    break;
                                }
                            }
                        }
                        $order = [
                            'id' => (empty($data['orders']) ? 1 : max(array_column($data['orders'], 'id')) + 1),
                            'user' => $_SESSION['user'],
                            'items' => array_map(function($item) {
                                return [
                                    'id' => $item['id'],
                                    'name' => $item['name'],
                                    'image' => $item['image'],
                                    'price' => $item['price'],
                                    'quantity' => $item['quantity'],
                                    'size' => $item['size']
                                ];
                            }, $_SESSION['cart']),
                            'total' => $subtotal * 1.1, // Include 10% tax
                            'date' => date('Y-m-d H:i:s'),
                            'contact_number' => $contact_number,
                            'address' => $address,
                            'payment_method' => 'Cash on Delivery',
                            'status' => 'Pending'
                        ];
                        $data['orders'][] = $order;
                        if (!file_put_contents('api/data.json', json_encode($data, JSON_PRETTY_PRINT))) {
                            error_log('Failed to write to api/data.json in checkout.php');
                            $error = 'Error saving order. Please check file permissions.';
                        } else {
                            $_SESSION['order_total'] = $subtotal * 1.1; // Include 10% tax
                            $_SESSION['cart'] = [];
                            header('Location: order_success.php');
                            exit;
                        }
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - RedStore</title>
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
    <div class="small-container cart-page">
        <?php if (isset($error)): ?>
            <p class="alert alert-danger"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <h2>Checkout</h2>
        <h3>Order Summary</h3>
        <table>
            <tr>
                <th>Product</th>
                <th>Size</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>
            <?php
            $subtotal = 0;
            foreach ($_SESSION['cart'] as $item) {
                if (!isset($item['price'], $item['quantity'], $item['name'], $item['image'], $item['size'])) {
                    continue;
                }
                $item_subtotal = $item['price'] * $item['quantity'];
                $subtotal += $item_subtotal;
                echo '<tr>';
                echo '<td><div class="cart-info">';
                echo '<img src="' . htmlspecialchars($item['image']) . '">';
                echo '<div>';
                echo '<p>' . htmlspecialchars($item['name']) . '</p>';
                echo '<small>Price: ' . htmlspecialchars($item['price']) . ' tk</small>';
                echo '</div></div></td>';
                echo '<td>' . htmlspecialchars($item['size']) . '</td>';
                echo '<td>' . htmlspecialchars($item['quantity']) . '</td>';
                echo '<td>' . htmlspecialchars($item_subtotal) . ' tk</td>';
                echo '</tr>';
            }
            ?>
        </table>
        <div class="total-price">
            <table>
                <tr>
                    <td>Subtotal</td>
                    <td><?php echo htmlspecialchars($subtotal); ?> tk</td>
                </tr>
                <tr>
                    <td>Tax</td>
                    <td><?php echo htmlspecialchars($subtotal * 0.1); ?> tk</td>
                </tr>
                <tr>
                    <td>Total</td>
                    <td><?php echo htmlspecialchars($subtotal * 1.1); ?> tk</td>
                </tr>
            </table>
        </div>
        <h3>Shipping & Payment Details</h3>
        <form method="POST" action="checkout.php" class="admin-form">
            <label for="contact_number">Contact Number:</label>
            <input type="text" name="contact_number" id="contact_number" required>
            <label for="address">Delivery Address:</label>
            <textarea name="address" id="address" required></textarea>
            <label>Payment Method:</label>
            <p>Cash on Delivery</p>
            <input type="hidden" name="payment_method" value="Cash on Delivery">
            <button type="submit" name="place_order" class="btn">Place Order</button>
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
            MenuItems.style.maxHeight = MenuItems.style.maxHeight == "0px" ? "200px" : "0px";
        }
    </script>
</body>
</html>