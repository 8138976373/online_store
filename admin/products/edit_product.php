<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Online Store Dashboard</title>
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
    include('../../dbconnect.php'); // Your database connection file

    // Protect this page: Redirect if not logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    $loggedInUsername = $_SESSION['username'] ?? 'Guest';
    $message = ''; // To store success or error messages
    $product_data = null; // To hold the product's current data
    $productId = null; // Initialize productId

    // --- 1. Fetch Product Data to Pre-fill the Form ---
    if (isset($_GET['id'])) {
        $productId = (int)$_GET['id'];

        // Use prepared statement to fetch product details
        $stmt = $db->prepare("SELECT id, product_name, description, price, stock_quantity, image_url, category_id FROM products WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $product_data = $result->fetch_assoc();
            } else {
                $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Product not found.</div>';
                $productId = null; // Invalidate productId if not found
            }
            $stmt->close();
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Database error preparing statement for fetching product.</div>';
            $productId = null;
        }
    } else {
        $message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">No product ID provided.</div>';
    }

    // --- Fetch Categories for the Dropdown ---
    $categories = [];
    $categoriesResult = mysqli_query($db, "SELECT id, category_name FROM categories ORDER BY category_name ASC");
    if ($categoriesResult) {
        while ($cat = mysqli_fetch_assoc($categoriesResult)) {
            $categories[] = $cat;
        }
    } else {
        $message .= '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Error fetching categories: ' . htmlspecialchars(mysqli_error($db)) . '</div>';
    }


    // --- 2. Handle Form Submission (Update Product) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product']) && $productId !== null) {
        $productIdToUpdate = (int)$_POST['product_id'];
        $productName = trim($_POST['product_name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price']; // Cast to float
        $stockQuantity = (int)$_POST['stock_quantity']; // Cast to int
        $imageUrl = trim($_POST['image_url']);
        $categoryId = (int)$_POST['category_id'];

        // Basic Validation
        if (empty($productName) || empty($description) || $price <= 0 || $stockQuantity < 0 || empty($imageUrl) || $categoryId <= 0) {
            $message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">All fields are required and must have valid values.</div>';
        } else {
            // Use prepared statement for update
            $stmt = $db->prepare("UPDATE products SET product_name = ?, description = ?, price = ?, stock_quantity = ?, image_url = ?, category_id = ? WHERE id = ?");
            if ($stmt) {
                // s: product_name, description, image_url
                // d: price, stock_quantity, category_id, id
                $stmt->bind_param("ssdisii", $productName, $description, $price, $stockQuantity, $imageUrl, $categoryId, $productIdToUpdate);
                
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Product **' . htmlspecialchars($productName) . '** updated successfully!</div>';
                        // Re-fetch product data to update the form with new values
                        $stmt_re_fetch = $db->prepare("SELECT id, product_name, description, price, stock_quantity, image_url, category_id FROM products WHERE id = ?");
                        if ($stmt_re_fetch) {
                            $stmt_re_fetch->bind_param("i", $productIdToUpdate);
                            $stmt_re_fetch->execute();
                            $product_data = $stmt_re_fetch->get_result()->fetch_assoc();
                            $stmt_re_fetch->close();
                        }
                    } else {
                        $message = '<div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">No changes made to product **' . htmlspecialchars($productName) . '** (data was identical).</div>';
                    }
                } else {
                    $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Error updating product: ' . htmlspecialchars($stmt->error) . '</div>';
                }
                $stmt->close();
            } else {
                $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Database error preparing update statement.</div>';
            }
        }
    }
    // Close the database connection
    mysqli_close($db);
    include '../../includes/header.php';
    ?>

   

    <div class="flex flex-1">
        <aside class="w-64 bg-gray-800 text-white p-4 shadow-lg flex-shrink-0">
            <nav>
                <ul>
                    <li class="mb-3">
                        <a href="/online_store/admin/dashboard.php" class="flex items-center py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-200">
                            <i class="fas fa-tachometer-alt mr-3"></i>Dashboard Home
                        </a>
                    </li>
                    <li class="mb-3">
                        <a href="/online_store/admin/category/
                        categories.php" class="flex items-center py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-200">
                            <i class="fas fa-tags mr-3"></i>Categories
                        </a>
                    </li>
                    <li class="mb-3">
                        <a href="/online_store/admin/productsproducts.php" class="flex items-center py-2 px-4 rounded-lg bg-gray-700 text-white transition duration-200">
                            <i class="fas fa-box-open mr-3"></i>Products
                        </a>
                    </li>
                    <li class="mb-3">
                        <a href="/online_store/admin/orders.php" class="flex items-center py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-200">
                            <i class="fas fa-shopping-cart mr-3"></i>Orders
                        </a>
                    </li>
                    <li class="mb-3">
                        <a href="users.php" class="flex items-center py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-200">
                            <i class="fas fa-users mr-3"></i>Users
                        </a>
                    </li>
                    </ul>
            </nav>
        </aside>

        <main class="flex-1 p-8 overflow-y-auto">
            <h1 class="text-4xl font-extrabold text-gray-900 mb-6">Edit Product: <?= htmlspecialchars($product_data['product_name'] ?? 'N/A') ?></h1>

            <?= $message ?>

            <?php if ($product_data): ?>
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Product Details</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product_data['id']) ?>">

                        <div class="mb-4">
                            <label for="product_name" class="block text-gray-700 text-sm font-bold mb-2">Product Name</label>
                            <input type="text" name="product_name" id="product_name"
                                class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                                value="<?= htmlspecialchars($product_data['product_name']) ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                            <textarea name="description" id="description" rows="4"
                                class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required><?= htmlspecialchars($product_data['description']) ?></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="mb-4">
                                <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Price ($)</label>
                                <input type="number" name="price" id="price" step="0.01" min="0.01"
                                    class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    value="<?= htmlspecialchars($product_data['price']) ?>" required>
                            </div>

                            <div class="mb-4">
                                <label for="stock_quantity" class="block text-gray-700 text-sm font-bold mb-2">Stock Quantity</label>
                                <input type="number" name="stock_quantity" id="stock_quantity" min="0"
                                    class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    value="<?= htmlspecialchars($product_data['stock_quantity']) ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="image_url" class="block text-gray-700 text-sm font-bold mb-2">Image URL</label>
                            <input type="url" name="image_url" id="image_url"
                                class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                                value="<?= htmlspecialchars($product_data['image_url']) ?>" required>
                            <?php if (!empty($product_data['image_url'])): ?>
                                <div class="mt-2 text-sm text-gray-600">
                                    Current Image: 
                                       <img src="/online_store/dataimg/<?php echo htmlspecialchars($product_data['image_url']); ?>" alt="Product Image" class="img-thumbnail" style="max-width: 150px; height: auto;">

                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-6">
                            <label for="category_id" class="block text-gray-700 text-sm font-bold mb-2">Category</label>
                            <select name="category_id" id="category_id"
                                class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                                <option value="">Select a Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category['id']) ?>"
                                        <?= ($category['id'] == $product_data['category_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($categories)): ?>
                                <p class="text-red-500 text-xs italic mt-1">No categories found. Please add categories first.</p>
                            <?php endif; ?>
                        </div>

                        <button type="submit" name="update_product"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out transform hover:scale-105">
                            <i class="fas fa-save mr-2"></i>Update Product
                        </button>
                        <a href="products.php" class="ml-4 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded-lg transition duration-300 ease-in-out">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Products List
                        </a>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <footer class="bg-gray-800 text-white p-4 text-center text-sm shadow-inner">
        <div class="container mx-auto">
            &copy; 2025 Online Store. All rights reserved.
        </div>
    </footer>
</body>

</html>