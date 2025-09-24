<?php
session_start();
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
// Clean up cart to remove invalid items
$_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) {
    return isset($item['id'], $item['name'], $item['image'], $item['price'], $item['quantity'], $item['size']) &&
           is_int($item['id']) && is_string($item['name']) && is_string($item['image']) &&
           is_numeric($item['price']) && is_int($item['quantity']) && $item['quantity'] > 0 &&
           is_string($item['size']);
});
$_SESSION['cart'] = array_values($_SESSION['cart']);


// Add to cart from other pages (no login required for adding)
if (isset($_GET['action']) && $_GET['action'] === 'add' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $json = @file_get_contents('api/data.json');
    if ($json === false) {
        error_log('Failed to read api/data.json in cart.php add action');
        header('Location: cart.php?error=json_read');
        exit;
    }
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON decode error in cart.php add action: ' . json_last_error_msg());
        header('Location: cart.php?error=json_decode');
        exit;
    }
    foreach ($data['products'] as $product) {
        if ($product['id'] === $id && isset($product['name'], $product['image'], $product['price'])) {
            $exists = false;
            for ($i = 0; $i < count($_SESSION['cart']); $i++) {
                if ($_SESSION['cart'][$i]['id'] === $id && $_SESSION['cart'][$i]['size'] === 'Medium') {
                    $_SESSION['cart'][$i]['quantity'] += 1;
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $_SESSION['cart'][] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'image' => $product['image'],
                    'price' => $product['price'],
                    'quantity' => 1,
                    'size' => 'Medium' // Default size
                ];
            }
            break;
        }
    }
    header('Location: cart.php');
    exit;
}
// Remove from cart
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['index'])) {
    $index = (int)$_GET['index'];
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    header('Location: cart.php');
    exit;
}
// Update cart quantities and sizes (no login required)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    foreach ($_POST['quantity'] as $index => $qty) {
        $index = (int)$index;
        $qty = (int)$qty;
        $size = isset($_POST['size'][$index]) ? $_POST['size'][$index] : 'Medium';
        if (isset($_SESSION['cart'][$index])) {
            if ($qty <= 0) {
                unset($_SESSION['cart'][$index]);
            } else {
                $_SESSION['cart'][$index]['quantity'] = $qty;
                $_SESSION['cart'][$index]['size'] = $size;
            }
        }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    header('Location: cart.php');
    exit;
}
// Process order (requires login, redirects to checkout.php for logged-in users)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order'])) {
    if (!isset($_SESSION['user'])) {
        $_SESSION['login_redirect'] = 'cart.php';
        header('Location: account.php?msg=login_required');
        exit;
    }
    header('Location: checkout.php');
    exit;
}
$json = @file_get_contents('api/data.json');
$products = [];
if ($json !== false) {
    $data = json_decode($json, true);
    if (json_last_error() === JSON_ERROR_NONE && isset($data['products'])) {
        $products = $data['products'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - RedStore</title>
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
                    <li><a href="<?php echo isset($_SESSION['user']) ? 'user_details.php' : 'account.php'; ?>">
                        <?php echo isset($_SESSION['user']) ? 'Welcome, ' . htmlspecialchars($_SESSION['user']) : 'Account'; ?>
                    </a></li>
                </ul>
            </nav>
            <a href="cart.php"><img src="RedStore_Img/cart.png" width="30px" height="30px"></a>
            <img src="RedStore_Img/menu.png" class="menu-icon" onclick="menutoggle()">
        </div>
    </div>
    <div class="small-container cart-page">
        <?php if (isset($_GET['error'])): ?>
            <p class="alert alert-danger">
                <?php
                if ($_GET['error'] === 'json_read') echo 'Error reading product data.';
                elseif ($_GET['error'] === 'json_decode') echo 'Error processing product data.';
                ?>
            </p>
        <?php endif; ?>
        <?php if (empty($_SESSION['cart'])): ?>
            <p class="alert alert-info">Your cart is empty. <a href="index.php">Continue shopping</a>.</p>
        <?php else: ?>
            <form method="POST" action="cart.php" id="cart-form">
                <table>
                    <tr>
                        <th>Product</th>
                        <th>Size</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                    <?php
                    $subtotal = 0;
                    foreach ($_SESSION['cart'] as $index => $item) {
                        if (!isset($item['price'], $item['quantity'], $item['name'], $item['image'], $item['size'])) {
                            continue;
                        }
                        $item_subtotal = $item['price'] * $item['quantity'];
                        $subtotal += $item_subtotal;
                        $stock = 0;
                        foreach ($products as $product) {
                            if ($product['id'] === $item['id'] && isset($product['stock'])) {
                                $stock = $product['stock'];
                                break;
                            }
                        }
                        echo '<tr>';
                        echo '<td><div class="cart-info">';
                        echo '<img src="' . htmlspecialchars($item['image']) . '">';
                        echo '<div>';
                        echo '<p>' . htmlspecialchars($item['name']) . '</p>';
                        echo '<small>Price: ' . htmlspecialchars($item['price']) . ' tk</small>';
                        echo '<br>';
                        echo '<a href="cart.php?action=remove&index=' . $index . '" class="remove-link" onclick="return confirm(\'Remove item?\');">Remove</a>';
                        echo '</div></div></td>';
                        echo '<td><select name="size[' . $index . ']">';
                        $sizes = ['XXL', 'XL', 'Large', 'Medium', 'Small'];
                        foreach ($sizes as $size) {
                            echo '<option value="' . $size . '"' . ($item['size'] === $size ? ' selected' : '') . '>' . $size . '</option>';
                        }
                        echo '</select></td>';
                        echo '<td><input type="number" name="quantity[' . $index . ']" value="' . $item['quantity'] . '" min="1" max="' . htmlspecialchars($stock) . '"></td>';
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
                <button type="submit" name="update" class="btn">Update Cart</button>
                <button type="submit" name="order" class="btn"><?php echo isset($_SESSION['user']) ? 'Checkout' : 'Place Order'; ?></button>
            </form>
        <?php endif; ?>
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