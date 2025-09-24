<?php
session_start();

// Check if admin is logged in
$loggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Hardcoded admin credentials
$adminUsername = 'admin';
$adminPassword = 'admin123';

// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if ($username === $adminUsername && $password === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
        $loggedIn = true;
    } else {
        $loginError = 'Invalid username or password';
    }
}

// Handle logout
if (isset($_POST['logout'])) {
    unset($_SESSION['admin_logged_in']);
    $loggedIn = false;
}

// Load data from api/data.json
$jsonFile = 'api/data.json';
$json = @file_get_contents($jsonFile);
if ($json === false) {
    error_log('Failed to read api/data.json in admin.php');
    $dataError = 'Error reading data file. Please check file permissions.';
    $data = ['products' => [], 'users' => [], 'orders' => []];
} else {
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON decode error in admin.php: ' . json_last_error_msg());
        $dataError = 'Error processing data file.';
        $data = ['products' => [], 'users' => [], 'orders' => []];
    }
}
$products = $data['products'] ?? [];
$users = $data['users'] ?? [];
$orders = $data['orders'] ?? [];

// Handle actions
if ($loggedIn && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_product' || $_POST['action'] === 'edit_product') {
        $id = $_POST['action'] === 'edit_product' ? (int)$_POST['id'] : (max(array_column($products, 'id')) + 1);
        $newProduct = [
            'id' => $id,
            'name' => $_POST['name'] ?? '',
            'price' => (float)($_POST['price'] ?? 0),
            'image' => $_POST['image'] ?? '',
            'description' => $_POST['description'] ?? '',
            'category' => $_POST['category'] ?? '',
            'stock' => (int)($_POST['stock'] ?? 0),
            'rating' => min(max((float)($_POST['rating'] ?? 0), 0), 5)
        ];
        if ($_POST['action'] === 'edit_product') {
            foreach ($products as &$product) {
                if ($product['id'] === $id) {
                    $product = $newProduct;
                    break;
                }
            }
        } else {
            $products[] = $newProduct;
        }
        $data['products'] = $products;
        if (!file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT))) {
            error_log('Failed to write to api/data.json in admin.php add/edit_product');
            $dataError = 'Error saving product data. Please check file permissions.';
        }
    } elseif ($_POST['action'] === 'delete_product') {
        $id = (int)$_POST['id'];
        $products = array_filter($products, function($product) use ($id) {
            return $product['id'] !== $id;
        });
        $data['products'] = array_values($products);
        if (!file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT))) {
            error_log('Failed to write to api/data.json in admin.php delete_product');
            $dataError = 'Error saving product data. Please check file permissions.';
        }
    } elseif ($_POST['action'] === 'delete_user') {
        $id = (int)$_POST['id'];
        $users = array_filter($users, function($user) use ($id) {
            return $user['id'] !== $id;
        });
        $data['users'] = array_values($users);
        if (!file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT))) {
            error_log('Failed to write to api/data.json in admin.php delete_user');
            $dataError = 'Error saving user data. Please check file permissions.';
        }
    } elseif ($_POST['action'] === 'edit_order') {
        $id = (int)$_POST['id'];
        $index = array_search($id, array_column($orders, 'id'));
        if ($index !== false) {
            $orders[$index]['status'] = $_POST['status'] ?? 'Pending';
            $data['orders'] = $orders;
            if (!file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT))) {
                error_log('Failed to write to api/data.json in admin.php edit_order');
                $dataError = 'Error saving order data. Please check file permissions.';
            }
        }
    } elseif ($_POST['action'] === 'delete_order') {
        $id = (int)$_POST['id'];
        $orders = array_filter($orders, function($order) use ($id) {
            return $order['id'] !== $id;
        });
        $data['orders'] = array_values($orders);
        if (!file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT))) {
            error_log('Failed to write to api/data.json in admin.php delete_order');
            $dataError = 'Error saving order data. Please check file permissions.';
        }
    }
    if (!isset($dataError)) {
        header('Location: admin.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RedStore | Admin Panel</title>
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
                    <li><a href="account.php">Account</a></li>
                    <?php if ($loggedIn): ?>
                        <li><form method="POST"><button type="submit" name="logout" class="btn">Logout</button></form></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <a href="cart.php"><img src="RedStore_Img/cart.png" width="30px" height="30px"></a>
            <img src="RedStore_Img/menu.png" class="menu-icon" onclick="menutoggle()">
        </div>
    </div>
    <div class="small-container">
        <?php if (isset($dataError)): ?>
            <p class="alert alert-danger"><?php echo htmlspecialchars($dataError); ?></p>
        <?php endif; ?>
        <?php if (!$loggedIn): ?>
            <h2>Admin Login</h2>
            <?php if (isset($loginError)): ?>
                <p class="alert alert-danger"><?php echo htmlspecialchars($loginError); ?></p>
            <?php endif; ?>
            <form method="POST" class="admin-form">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
                <button type="submit" name="login" class="btn">Login</button>
            </form>
        <?php else: ?>
            <h2>Admin Panel - Manage Products And Orders</h2>
            <button class="btn" onclick="openAddProductModal()">Add New Product</button>
            <div id="addProductModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeAddProductModal()">&times;</span>
                    <h3>Add New Product</h3>
                    <form method="POST" class="admin-form" id="addProductForm">
                        <input type="hidden" name="action" value="add_product">
                        <label for="name">Name:</label>
                        <input type="text" name="name" id="name" required>
                        <label for="price">Price (tk):</label>
                        <input type="number" name="price" id="price" step="0.01" required>
                        <label for="image">Image Path:</label>
                        <input type="text" name="image" id="image" required>
                        <label for="description">Description:</label>
                        <textarea name="description" id="description" required></textarea>
                        <label for="category">Category:</label>
                        <select name="category" id="category" required>
                            <option value="T-shirts">T-shirts</option>
                            <option value="Shoes">Shoes</option>
                            <option value="Joggers">Joggers</option>
                            <option value="Socks">Socks</option>
                            <option value="Watches">Watches</option>
                            <option value="Smart Wearables">Smart Wearables</option>
                        </select>
                        <label for="stock">Stock:</label>
                        <input type="number" name="stock" id="stock" min="0" required>
                        <label for="rating">Rating (0-5):</label>
                        <input type="number" name="rating" id="rating" min="0" max="5" step="0.1" required>
                        <button type="submit" class="btn">Add Product</button>
                    </form>
                </div>
            </div>
            <h3>Product List</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price (tk)</th>
                        <th>Stock</th>
                        <th>Rating</th>
                        <th>Image</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo $product['price']; ?></td>
                            <td><?php echo $product['stock']; ?></td>
                            <td><?php echo $product['rating'] ?? 0; ?></td>
                            <td><img src="<?php echo htmlspecialchars($product['image']); ?>" style="width: 50px;"></td>
                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="edit_product">
                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                    <input type="number" name="price" value="<?php echo $product['price']; ?>" step="0.01" required>
                                    <input type="number" name="stock" value="<?php echo $product['stock']; ?>" min="0" required>
                                    <input type="number" name="rating" value="<?php echo $product['rating'] ?? 0; ?>" min="0" max="5" step="0.1" required>
                                    <input type="text" name="image" value="<?php echo htmlspecialchars($product['image']); ?>" required>
                                    <textarea name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                    <select name="category" required>
                                        <option value="T-shirts" <?php echo $product['category'] === 'T-shirts' ? 'selected' : ''; ?>>T-shirts</option>
                                        <option value="Shoes" <?php echo $product['category'] === 'Shoes' ? 'selected' : ''; ?>>Shoes</option>
                                        <option value="Joggers" <?php echo $product['category'] === 'Joggers' ? 'selected' : ''; ?>>Joggers</option>
                                        <option value="Socks" <?php echo $product['category'] === 'Socks' ? 'selected' : ''; ?>>Socks</option>
                                        <option value="Watches" <?php echo $product['category'] === 'Watches' ? 'selected' : ''; ?>>Watches</option>
                                        <option value="Smart Wearables" <?php echo $product['category'] === 'Smart Wearables' ? 'selected' : ''; ?>>Smart Wearables</option>
                                    </select>
                                    <button type="submit" class="btn">Update</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_product">
                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" class="btn" onclick="return confirm('Are you sure you want to delete this product?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h3>User List</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h3>Order List</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Items</th>
                        <th>Contact Number</th>
                        <th>Address</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Total (tk)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['user'] ?? $order['user_id'] ?? 'Unknown'); ?></td>
                            <td>
                                <?php
                                foreach ($order['items'] ?? [] as $item) {
                                    echo 'Product: ' . htmlspecialchars($item['name'] ?? 'Unknown') . 
                                         ', Size: ' . (isset($item['size']) ? htmlspecialchars($item['size']) : 'N/A') . 
                                         ', Qty: ' . (isset($item['quantity']) ? $item['quantity'] : 'N/A') . '<br>';
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($order['contact_number'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($order['address'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($order['status'] ?? 'Pending'); ?></td>
                            <td><?php echo htmlspecialchars($order['total'] ?? 0); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="edit_order">
                                    <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                                    <select name="status" required>
                                        <option value="Pending" <?php echo ($order['status'] ?? 'Pending') === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Processing" <?php echo ($order['status'] ?? 'Pending') === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="Shipped" <?php echo ($order['status'] ?? 'Pending') === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="Delivered" <?php echo ($order['status'] ?? 'Pending') === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="Cancelled" <?php echo ($order['status'] ?? 'Pending') === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn">Update</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_order">
                                    <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" class="btn" onclick="return confirm('Are you sure you want to delete this order?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
        function openAddProductModal() {
            var modal = document.getElementById("addProductModal");
            var form = document.getElementById("addProductForm");
            modal.style.display = "block";
            form.reset();
        }
        function closeAddProductModal() {
            document.getElementById("addProductModal").style.display = "none";
        }
        window.onclick = function(event) {
            var modal = document.getElementById("addProductModal");
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };
    </script>
</body>
</html>