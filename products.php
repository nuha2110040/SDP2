<?php
session_start();
$json = @file_get_contents('api/data.json');
if ($json === false) {
    $products = [];
    $error = 'Error: Could not read api/data.json.';
} else {
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data) || !isset($data['products'])) {
        $products = [];
        $error = 'Error: Invalid JSON format in api/data.json.';
    } else {
        $products = $data['products'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RedStore | All Products</title>
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
    <div class="small-container">
        <div class="row row-2">
            <h2>All Products</h2>
            <div class="filter-sort">
                <form method="GET" action="products.php">
                    <label for="category">Filter by Category: </label>
                    <select name="category" id="category" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <option value="T-shirts" <?php echo isset($_GET['category']) && $_GET['category'] == 'T-shirts' ? 'selected' : ''; ?>>T-shirts</option>
                        <option value="Shoes" <?php echo isset($_GET['category']) && $_GET['category'] == 'Shoes' ? 'selected' : ''; ?>>Shoes</option>
                        <option value="Joggers" <?php echo isset($_GET['category']) && $_GET['category'] == 'Joggers' ? 'selected' : ''; ?>>Joggers</option>
                        <option value="Socks" <?php echo isset($_GET['category']) && $_GET['category'] == 'Socks' ? 'selected' : ''; ?>>Socks</option>
                        <option value="Watches" <?php echo isset($_GET['category']) && $_GET['category'] == 'Watches' ? 'selected' : ''; ?>>Watches</option>
                        <option value="Smart Wearables" <?php echo isset($_GET['category']) && $_GET['category'] == 'Smart Wearables' ? 'selected' : ''; ?>>Smart Wearables</option>
                    </select>
                    <label for="sort">Sort by: </label>
                    <select name="sort" id="sort" onchange="this.form.submit()">
                        <option value="name_asc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                        <option value="name_desc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                        <option value="price_asc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                        <option value="price_desc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="row">
            <?php
            if (isset($error)) {
                echo '<p class="alert alert-danger">' . htmlspecialchars($error) . '</p>';
            } elseif (empty($products)) {
                echo '<p class="alert alert-danger">Error: No products found in api/data.json.</p>';
            } else {
                if (isset($_GET['category']) && !empty($_GET['category'])) {
                    $products = array_filter($products, function($product) {
                        return isset($product['category']) && $product['category'] === $_GET['category'];
                    });
                }
                $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
                usort($products, function($a, $b) use ($sort) {
                    if ($sort === 'price_asc') {
                        return $a['price'] - $b['price'];
                    } elseif ($sort === 'price_desc') {
                        return $b['price'] - $a['price'];
                    } elseif ($sort === 'name_desc') {
                        return strcmp($b['name'], $a['name']);
                    } else {
                        return strcmp($a['name'], $b['name']);
                    }
                });
                if (empty($products)) {
                    echo '<p>No products found for the selected category.</p>';
                } else {
                    foreach ($products as $product) {
                        echo '<div class="col-4">';
                        echo '<a href="product_details.php?id=' . $product['id'] . '"><img src="' . htmlspecialchars($product['image']) . '"></a>';
                        echo '<a href="product_details.php?id=' . $product['id'] . '"><h4>' . htmlspecialchars($product['name']) . '</h4></a>';
                        echo '<div class="rating">';
                        $rating = $product['rating'] ?? 0;
                        $fullStars = floor($rating);
                        $halfStar = $rating - $fullStars >= 0.5 ? 1 : 0;
                        $emptyStars = 5 - $fullStars - $halfStar;
                        echo str_repeat('<i class="fa-solid fa-star"></i>', $fullStars);
                        echo $halfStar ? '<i class="fa-solid fa-star-half-alt"></i>' : '';
                        echo str_repeat('<i class="fa-regular fa-star"></i>', $emptyStars);
                        echo '</div>';
                        echo '<p>' . htmlspecialchars($product['price']) . ' tk</p>';
                        if (isset($product['stock']) && $product['stock'] === 0) {
                            echo '<p class="out-of-stock">Out of Stock</p>';
                        } else {
                            echo '<a href="cart.php?action=add&id=' . $product['id'] . '" class="btn">Add to Cart</a>';
                        }
                        echo '</div>';
                    }
                }
            }
            ?>
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
                    <img src="RedStore_Img/logo-white.png">
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