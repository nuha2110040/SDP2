<?php
session_start();
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$json = @file_get_contents('api/data.json');
if ($json === false) {
    $product = null;
    $error = 'Error: Could not read api/data.json.';
} else {
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data) || !isset($data['products'])) {
        $product = null;
        $error = 'Error: Invalid JSON format in api/data.json.';
    } else {
        $product = null;
        foreach ($data['products'] as $p) {
            if ($p['id'] === $product_id) {
                $product = $p;
                break;
            }
        }
        if (!$product) {
            header('Location: products.php');
            exit;
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart']) && $product && isset($product['stock']) && $product['stock'] > 0) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    if ($quantity < 1) $quantity = 1;
    $quantity = min($quantity, $product['stock']); // Ensure quantity doesn't exceed stock
    $size = isset($_POST['size']) && $_POST['size'] !== 'Select Size' ? $_POST['size'] : 'Medium';
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $exists = false;
    for ($i = 0; $i < count($_SESSION['cart']); $i++) {
        if ($_SESSION['cart'][$i]['id'] === $product_id && $_SESSION['cart'][$i]['size'] === $size) {
            $_SESSION['cart'][$i]['quantity'] = min($_SESSION['cart'][$i]['quantity'] + $quantity, $product['stock']);
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
            'quantity' => $quantity,
            'size' => $size
        ];
    }
    header('Location: cart.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name'] ?? 'Product'); ?> - RedStore</title>
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
    <div class="small-container single-product">
        <?php if (isset($error)): ?>
            <p class="alert alert-danger"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif ($product): ?>
            <div class="row">
                <div class="col-2">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" width="100%" id="ProductImg">
                    <?php if ($product_id === 1): ?>
                        <div class="small-img-row">
                            <div class="small-img-col">
                                <img src="RedStore_Img/gallery-1.jpg" width="100%" class="small-img">
                            </div>
                            <div class="small-img-col">
                                <img src="RedStore_Img/gallery-2.jpg" width="100%" class="small-img">
                            </div>
                            <div class="small-img-col">
                                <img src="RedStore_Img/gallery-3.jpg" width="100%" class="small-img">
                            </div>
                            <div class="small-img-col">
                                <img src="RedStore_Img/gallery-4.jpg" width="100%" class="small-img">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-2">
                    <p>Home / <?php echo htmlspecialchars($product['category'] ?? 'T-shirt'); ?></p>
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <h4><?php echo htmlspecialchars($product['price']); ?> tk</h4>
                    <?php if (isset($product['rating'])): ?>
                        <div class="rating">
                            <?php
                            $rating = $product['rating'] ?? 0;
                            $fullStars = floor($rating);
                            $halfStar = $rating - $fullStars >= 0.5 ? 1 : 0;
                            $emptyStars = 5 - $fullStars - $halfStar;
                            echo str_repeat('<i class="fa-solid fa-star"></i>', $fullStars);
                            echo $halfStar ? '<i class="fa-solid fa-star-half-alt"></i>' : '';
                            echo str_repeat('<i class="fa-regular fa-star"></i>', $emptyStars);
                            ?>
                        </div>
                    <?php endif; ?>
                    <p class="stock-status">
                        <?php echo isset($product['stock']) && $product['stock'] > 0 ? 'In Stock: ' . htmlspecialchars($product['stock']) : '<span class="out-of-stock">Out of Stock</span>'; ?>
                    </p>
                    <?php if (isset($product['stock']) && $product['stock'] > 0): ?>
                        <form method="POST" action="product_details.php?id=<?php echo $product_id; ?>">
                            <select name="size" required>
                                <option value="Select Size" disabled selected>Select Size</option>
                                <option value="XXL">XXL</option>
                                <option value="XL">XL</option>
                                <option value="Large">Large</option>
                                <option value="Medium">Medium</option>
                                <option value="Small">Small</option>
                            </select>
                            <input type="number" name="quantity" value="1" min="1" max="<?php echo htmlspecialchars($product['stock']); ?>">
                            <button type="submit" name="add_to_cart" class="btn">Add To Cart</button>
                        </form>
                    <?php else: ?>
                        <p class="out-of-stock">Out of Stock</p>
                    <?php endif; ?>
                    <h3>Product Details <i class="fa fa-indent"></i></h3>
                    <br>
                    <p><?php echo htmlspecialchars($product['description'] ?? 'Give your summer wardrobe a style upgrade with the HRX Men\'s Active T-Shirt. Team it with a pair of shorts for your morning workout or a denim for an evening out with your guys.'); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="small-container">
        <div class="row row-2">
            <h2>Related Products</h2>
            <p><a href="products.php">View More</a></p>
        </div>
        <div class="row">
            <?php
            if ($product) {
                $related = array_filter($data['products'], function($p) use ($product_id) {
                    return $p['id'] !== $product_id;
                });
                $related = array_slice($related, 0, 4);
                foreach ($related as $p): ?>
                    <div class="col-4">
                        <a href="product_details.php?id=<?php echo $p['id']; ?>">
                            <img src="<?php echo htmlspecialchars($p['image']); ?>">
                            <h4><?php echo htmlspecialchars($p['name']); ?></h4>
                        </a>
                        <?php if (isset($p['rating'])): ?>
                            <div class="rating">
                                <?php
                                $rating = $p['rating'] ?? 0;
                                $fullStars = floor($rating);
                                $halfStar = $rating - $fullStars >= 0.5 ? 1 : 0;
                                $emptyStars = 5 - $fullStars - $halfStar;
                                echo str_repeat('<i class="fa-solid fa-star"></i>', $fullStars);
                                echo $halfStar ? '<i class="fa-solid fa-star-half-alt"></i>' : '';
                                echo str_repeat('<i class="fa-regular fa-star"></i>', $emptyStars);
                                ?>
                            </div>
                        <?php endif; ?>
                        <p><?php echo htmlspecialchars($p['price']); ?> tk</p>
                    </div>
                <?php endforeach; ?>
            <?php } ?>
        </div>
    </div>
    <div style="clear: both;"></div>
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
        var ProductImg = document.getElementById("ProductImg");
        var SmallImg = document.getElementsByClassName("small-img");
        for (var i = 0; i < SmallImg.length; i++) {
            SmallImg[i].onclick = function() {
                ProductImg.src = this.src;
            }
        }
    </script>
</body>
</html>