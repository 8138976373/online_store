<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Products - Online Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">
    <?php
    session_start();
    include('dbconnect.php'); // Your database connection file

    $loggedInUsername = $_SESSION['username'] ?? null; // Check if a user is logged in
    $message = ''; // To store any temporary messages

    // --- Fetch Products ---
    $products = [];
    $categoryId = $_GET['category_id'] ?? null;
    $categoryName = "All Products"; // Default title

    $sql = "SELECT id, product_name, description, price, stock_quantity, image_url, category_id FROM products";
    $params = [];
    $types = "";

    if ($categoryId) {
        $sql .= " WHERE category_id = ?";
        $params[] = $categoryId;
        $types = "i"; // 'i' for integer (category_id)

        // Fetch category name for the title
        $stmtCat = $db->prepare("SELECT category_name FROM categories WHERE id = ?");
        if ($stmtCat) {
            $stmtCat->bind_param("i", $categoryId);
            $stmtCat->execute();
            $catResult = $stmtCat->get_result();
            if ($catResult->num_rows > 0) {
                $catData = $catResult->fetch_assoc();
                $categoryName = htmlspecialchars($catData['category_name']);
            }
            $stmtCat->close();
        }
    }

    $sql .= " ORDER BY product_name ASC";

    $stmt = $db->prepare($sql);
    if ($stmt) {
        if ($categoryId) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($product = $result->fetch_assoc()) {
                $products[] = $product;
            }
        } else {
            $message = '<div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mt-4" role="alert">No products found in this category.</div>';
        }
        $stmt->close();
    } else {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Database error fetching products: ' . htmlspecialchars($db->error) . '</div>';
    }

    // --- Fetch Categories for Navigation ---
    $categories = [];
    $categoriesResult = mysqli_query($db, "SELECT id, category_name FROM categories ORDER BY category_name ASC");
    if ($categoriesResult) {
        while ($cat = mysqli_fetch_assoc($categoriesResult)) {
            $categories[] = $cat;
        }
    }
    // Close the database connection
    mysqli_close($db);
    ?>

    <header class="bg-white text-gray-800 shadow-md p-4 sticky top-0 z-50">
        <div class="container mx-auto flex items-center justify-between">
            <a class="text-3xl font-extrabold text-blue-700 flex items-center" href="index.php">
                <i class="fas fa-store mr-2"></i>My Online Store
            </a>
            <nav class="flex items-center space-x-6">
                <a href="index.php" class="text-gray-700 hover:text-blue-700 font-semibold text-lg transition duration-200">Home</a>
                
                <div class="relative group">
                    <button class="text-gray-700 hover:text-blue-700 font-semibold text-lg transition duration-200 focus:outline-none">
                        Categories <i class="fas fa-caret-down ml-1"></i>
                    </button>
                    <div class="absolute hidden group-hover:block bg-white shadow-lg rounded-md mt-2 py-2 w-48 z-10">
                        <a href="index.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">All Products</a>
                        <?php foreach ($categories as $cat_nav): ?>
                            <a href="index.php?category_id=<?= htmlspecialchars($cat_nav['id']) ?>" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                <?= htmlspecialchars($cat_nav['category_name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <a href="/online_store/store/cart.php" class="text-gray-700 hover:text-blue-700 font-semibold text-lg transition duration-200 relative">
                    <i class="fas fa-shopping-cart text-xl"></i>
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full px-2 py-1">0</span> </a>
                
                <?php if ($loggedInUsername): ?>
                    <div class="relative group">
                        <button class="text-blue-700 font-semibold text-lg transition duration-200 focus:outline-none">
                            <i class="fas fa-user-circle mr-1"></i> <?= htmlspecialchars($loggedInUsername) ?> <i class="fas fa-caret-down ml-1"></i>
                        </button>
                        <div class="absolute hidden group-hover:block bg-white shadow-lg rounded-md mt-2 py-2 w-48 right-0 z-10">
                            <a href="my_account.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">My Account</a>
                            <a href="order_history.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Order History</a>
                            <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-300">
                        Login / Register
                    </a>
                <?php endif; ?>
                
                <a href="/online_store/admin/admin_dashboard.php" class="text-gray-700 hover:text-blue-700 font-semibold text-lg transition duration-200">
                    <i class="fas fa-cogs mr-1"></i> Admin
                </a>
            </nav>
        </div>
    </header>

    <main class="flex-1 container mx-auto p-8">
        <h1 class="text-5xl font-extrabold text-gray-900 mb-8 text-center"><?= $categoryName ?></h1>

        <?= $message ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden transition-transform transform hover:scale-105 duration-300 ease-in-out">
                        <a href="product_detail.php?id=<?= htmlspecialchars($product['id']) ?>">
                            <img src="/online_store/dataimg/<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" class="w-full h-48 object-cover">
                        </a>

                        <div class="p-5">
                            <h2 class="text-xl font-semibold text-gray-900 mb-2 truncate">
                                <a href="product_detail.php?id=<?= htmlspecialchars($product['id']) ?>" class="hover:text-blue-700"><?= htmlspecialchars($product['product_name']) ?></a>
                            </h2>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?= htmlspecialchars($product['description']) ?></p>
                            <div class="flex items-center justify-between">
                                <span class="text-2xl font-bold text-blue-700">$<?= number_format(htmlspecialchars($product['price']), 2) ?></span>
                                <?php if ($product['stock_quantity'] > 0): ?>
                                    <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-full transition duration-300 ease-in-out flex items-center">
                                        <i class="fas fa-cart-plus mr-2"></i> Add to Cart
                                    </button>
                                <?php else: ?>
                                    <span class="bg-gray-400 text-white font-bold py-2 px-4 rounded-full text-sm">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php if (empty($message)): // Only show if no other specific message is present ?>
                    <p class="text-gray-700 text-center col-span-full">No products available at the moment.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-gray-800 text-white p-6 text-center text-sm mt-8">
        <div class="container mx-auto">
            <p>&copy; 2025 My Online Store. All rights reserved.</p>
            <p class="mt-2">
                <a href="#" class="hover:text-blue-400 mx-2">Privacy Policy</a> | 
                <a href="#" class="hover:text-blue-400 mx-2">Terms of Service</a> | 
                <a href="#" class="hover:text-blue-400 mx-2">Contact Us</a>
            </p>
        </div>
    </footer>
</body>

</html>