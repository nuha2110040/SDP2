<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RedStore | Ecommerce Website Design</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="navbar">
                <div class="logo">
                    <a href="index.php"><img src="RedStore_Img/logo.png" width="125px"></a>
                </div>
                <nav>
                    <ul id="MenuItems">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#order">Contact</a></li>
                        <li><a href="admin.php">Admin</a></li>
                        <li>
                            <?php if (isset($_SESSION['user'])): ?>
                                <a href="user_details.php">Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?></a>
                            <?php else: ?>
                                <a href="account.php">Account</a>
                            <?php endif; ?>
                        </li>
                    </ul>
                </nav>
                <a href="cart.php"><img src="RedStore_Img/cart.png" width="30px" height="30px"></a>
                <img src="RedStore_Img/menu.png" class="menu-icon" onclick="menutoggle()">
            </div>
            <div class="row">
                <div class="col-2">
                    <h1>Give Your Workout<br>A New Style!</h1>
                    <p>Success isn't always about greatness. It's about consistency. Consistent hard work gains success. Greatness will come.</p>
                    <a href="products.php" class="btn">Explore Now &#8594;</a>
                </div>
                <div class="col-2">
                    <img src="RedStore_Img/image1.png">
                </div>
            </div>
        </div>
    </div>
    <div class="categories">
        <div class="small-container">
            <div class="row">
                <div class="col-3">
                    <img src="RedStore_Img/category-1.jpg">
                </div>
                <div class="col-3">
                    <img src="RedStore_Img/category-2.jpg">
                </div>
                <div class="col-3">
                    <img src="RedStore_Img/category-3.jpg">
                </div>
            </div>
        </div>
    </div>
    <div class="small-container">
        <h2 class="title">Featured Products</h2>
        <div class="row">
            <?php
            $json = @file_get_contents('api/data.json');
            if ($json === false) {
                echo '<p class="alert alert-danger">Error: Could not read api/data.json.</p>';
            } else {
                $data = json_decode($json, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($data) || !isset($data['products'])) {
                    echo '<p class="alert alert-danger">Error: Invalid JSON format or missing products in api/data.json.</p>';
                } elseif (empty($data['products'])) {
                    echo '<p class="alert alert-danger">Error: No products found in api/data.json.</p>';
                } else {
                    $featured = array_slice($data['products'], 0, 4);
                    foreach ($featured as $product) {
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
                        echo '<p>' . $product['price'] . ' tk</p>';
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
        <h2 class="title">Latest Products</h2>
        <div class="row">
            <?php
            if ($json !== false && is_array($data) && isset($data['products']) && !empty($data['products'])) {
                $latest = array_slice($data['products'], 4);
                foreach ($latest as $product) {
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
                    echo '<p>' . $product['price'] . ' tk</p>';
                    if (isset($product['stock']) && $product['stock'] === 0) {
                        echo '<p class="out-of-stock">Out of Stock</p>';
                    } else {
                        echo '<a href="cart.php?action=add&id=' . $product['id'] . '" class="btn">Add to Cart</a>';
                    }
                    echo '</div>';
                }
            } else {
                echo '<p class="alert alert-danger">No latest products available.</p>';
            }
            ?>
        </div>
    </div>
    <div class="offer">
        <div class="small-container">
            <div class="row">
                <div class="col-2">
                    <img src="RedStore_Img/exclusive.png" class="offer-img">
                </div>
                <div class="col-2">
                    <p>Exclusively Available on RedStore</p>
                    <h1>Smart Band 4</h1>
                    <small>The Mi Smart Band 4 features a 39.9% larger AMOLED color full-touch display with adjustable brightness, so everything is clear as can be.</small>
                    <br>
                    <?php
                    $smart_band = null;
                    foreach ($data['products'] as $p) {
                        if ($p['id'] === 13) {
                            $smart_band = $p;
                            break;
                        }
                    }
                    if ($smart_band && isset($smart_band['stock']) && $smart_band['stock'] === 0) {
                        echo '<p class="out-of-stock">Out of Stock</p>';
                    } else {
                        echo '<a href="cart.php?action=add&id=13" class="btn">Buy Now &#8594;</a>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="testimonial">
        <div class="small-container">
            <div class="row">
                <div class="col-3">
                    <i class="fa-solid fa-quote-left"></i>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Fuga error laborum, natus odit non corporis.</p>
                    <div class="rating">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                    </div>
                    <img src="RedStore_Img/user-1.png">
                    <h3>Sean Parker</h3>
                </div>
                <div class="col-3">
                    <i class="fa-solid fa-quote-left"></i>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Fuga error laborum, natus odit non corporis.</p>
                    <div class="rating">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                    </div>
                    <img src="RedStore_Img/user-2.png">
                    <h3>Mike Smith</h3>
                </div>
                <div class="col-3">
                    <i class="fa-solid fa-quote-left"></i>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Fuga error laborum, natus odit non corporis.</p>
                    <div class="rating">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                    </div>
                    <img src="RedStore_Img/user-3.png">
                    <h3>Mabel Joe</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="brands">
        <div class="small-container">
            <div class="row">
                <div class="col-5">
                    <img src="RedStore_Img/logo-godrej.png">
                </div>
                <div class="col-5">
                    <img src="RedStore_Img/logo-oppo.png">
                </div>
                <div class="col-5">
                    <img src="RedStore_Img/logo-coca-cola.png">
                </div>
                <div class="col-5">
                    <img src="RedStore_Img/logo-paypal.png">
                </div>
                <div class="col-5">
                    <img src="RedStore_Img/logo-philips.png">
                </div>
            </div>
        </div>
    </div>
    <div class="about-us" id="about">
        <div class="small-container">
            <div class="row">
                <div class="col-2">
                    <h2 class="title">About Us</h2>
                    <h3>Who We Are</h3>
                    <p>RedStore is your premier destination for high-quality sports apparel and accessories, crafted to elevate your active lifestyle. Founded with a passion for fitness and fashion, we partner with top brands like HRX, Puma, and Nike to offer stylish, durable, and comfortable gear for athletes of all levels.</p>
                    <h3>Our Mission</h3>
                    <p>At RedStore, our mission is to make sports accessible and enjoyable for everyone. We are committed to providing affordable, sustainable, and innovative sportswear that supports your fitness journey, whether you're hitting the gym, running outdoors, or embracing a casual sporty look.</p>
                    <h3>Why Choose Us</h3>
                    <p>We prioritize quality, affordability, and customer satisfaction. Our curated selection ensures you find the perfect gear to boost your performance and confidence. Join our community of fitness enthusiasts and experience the RedStore difference.</p>
                    <a href="products.php" class="btn">Shop Now &#8594;</a>
                </div>
            </div>
        </div>
    </div>
    <div class="contact-us" id="order">
        <div class="small-container">
            <div class="row">
                <div class="col-2">
                    <h2 class="title">Get in Touch</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-2">
                    <h3>Contact Us</h3>
                    <p>Any query and info about product, feel free to call anytime. We are available 24x7.</p>
                    <p><i class="fa-solid fa-location-dot"></i> Talaimari, Rajshahi, Bangladesh</p>
                    <p><i class="fa-solid fa-envelope"></i> redstore@gmail.com</p>
                    <p><i class="fa-solid fa-phone"></i> +01 234 567 8900</p>
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
    </script>
</body>
</html>