<?php
$pageTitle = 'Manage Products - Online Store Dashboard';
// Include the common header. Adjust path if common_header.php is in a different directory.
 include '../../includes/header.php';
// dbconnect.php is included here as it's specific to the page's database operations
 include('../../dbconnect.php');  // Adjust path if dbconnect.php is in a different directory

$message = ''; // To store success or error messages

// --- Handle Adding a New Product ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $productName = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price']; // Cast to float for decimal type
    $stockQuantity = (int)$_POST['stock_quantity']; // Cast to int
    $categoryId = (int)$_POST['category_id']; // Cast to int
    $sku = trim($_POST['sku']);
    $weight = empty($_POST['weight']) ? NULL : (float)$_POST['weight']; // Allow NULL for optional field
    $brand = trim($_POST['brand']);

    $imagePath = ''; // Initialize image path
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $rawImageName = $_FILES['image']['name'];
        // Sanitize filename to prevent directory traversal or other issues
        $sanitizedImageName = time() . '_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $rawImageName);
        $tempname = $_FILES['image']['tmp_name'];
        $uploadDir = "../../dataimg/"; // Adjust path to your image storage directory relative to this script

        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // 0777 for full permissions, adjust as needed for production
        }

        if (move_uploaded_file($tempname, $uploadDir . $sanitizedImageName)) {
            $imagePath = $sanitizedImageName; // Store only the filename in the database
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Image upload failed. Error code: ' . htmlspecialchars($_FILES['image']['error']) . '</div>';
        }
    } else if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Image upload failed. Error code: ' . htmlspecialchars($_FILES['image']['error']) . '</div>';
    }


    if (empty($productName) || empty($price) || empty($stockQuantity) || empty($categoryId)) {
        $message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">Please fill in all required product fields (Name, Price, Stock, Category).</div>';
    } else {
        // Use prepared statement for insertion
        $stmt = $db->prepare("INSERT INTO products (product_name, description, price, stock_quantity, category_id, image_url, sku, weight, brand) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            // 's' for string, 'd' for double (float), 'i' for integer
            $stmt->bind_param("ssdiissss", $productName, $description, $price, $stockQuantity, $categoryId, $imagePath, $sku, $weight, $brand);
            if ($stmt->execute()) {
                $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Product **' . htmlspecialchars($productName) . '** added successfully!</div>';
                // Optional: Clear form fields after successful submission
                $_POST = array(); 
            } else {
                $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Error adding product: ' . htmlspecialchars($stmt->error) . '</div>';
            }
            $stmt->close();
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Database error preparing statement for product addition.</div>';
        }
    }
}

// --- Handle Deleting a Product ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $productId = (int)$_GET['id']; // Cast to integer for security

    // Use prepared statement for deletion
    $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $productId); // 'i' for integer
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Product deleted successfully!</div>';
            } else {
                $message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">Product not found or already deleted.</div>';
            }
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Error deleting product: ' . htmlspecialchars($stmt->error) . '</div>';
        }
        $stmt->close();
    } else {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Database error preparing statement for product deletion.</div>';
    }
    // Redirect to clear the GET parameters after deletion to prevent re-deletion on refresh
    header('Location: products.php'); // Changed to relative path for consistency
    exit();
}

// --- Fetch Categories for the Dropdown ---
$categories = [];
$categoriesResult = mysqli_query($db, "SELECT id, category_name FROM categories ORDER BY category_name ASC");
if ($categoriesResult && mysqli_num_rows($categoriesResult) > 0) {
    while ($cat = mysqli_fetch_assoc($categoriesResult)) {
        $categories[] = $cat;
    }
}
// Note: mysqli_close($db) will be called at the end of the script execution.
// For simplicity in this example, $db remains open until the end.
?>

<div class="flex flex-1">
    <aside class="w-64 bg-gray-800 text-white p-4 shadow-lg flex-shrink-0">
        <nav>
            <ul>
                <li class="mb-3">
                    <a href="../dashboard.php" class="flex items-center py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-200">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard Home
                    </a>
                </li>
                <li class="mb-3">
                    <a href="../categories.php" class="flex items-center py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-200">
                        <i class="fas fa-tags mr-3"></i>Categories
                    </a>
                </li>
                <li class="mb-3">
                    <a href="products.php" class="flex items-center py-2 px-4 rounded-lg bg-gray-700 text-white transition duration-200">
                        <i class="fas fa-box-open mr-3"></i>Products
                    </a>
                </li>
                <li class="mb-3">
                    <a href="../orders.php" class="flex items-center py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-200">
                        <i class="fas fa-shopping-cart mr-3"></i>Orders
                    </a>
                </li>
                <li class="mb-3">
                    <a href="../users.php" class="flex items-center py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-200">
                        <i class="fas fa-users mr-3"></i>Users
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="flex-1 p-8 overflow-y-auto">
        <h1 class="text-4xl font-extrabold text-gray-900 mb-6">Manage Products</h1>

        <?= $message ?>

        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Add New Product</h2>
            <form method="POST" action="" enctype="multipart/form-data"> <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="product_name" class="block text-gray-700 text-sm font-bold mb-2">Product Name <span class="text-red-500">*</span></label>
                        <input type="text" name="product_name" id="product_name"
                            class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., Wireless Bluetooth Earbuds" required value="<?= htmlspecialchars($_POST['product_name'] ?? '') ?>">
                    </div>
                    <div>
                        <label for="category_id" class="block text-gray-700 text-sm font-bold mb-2">Category <span class="text-red-500">*</span></label>
                        <select name="category_id" id="category_id"
                            class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                            <option value="">Select a Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category['id']) ?>"
                                    <?= (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Price ($) <span class="text-red-500">*</span></label>
                        <input type="number" name="price" id="price" step="0.01" min="0"
                            class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., 49.99" required value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                    </div>
                    <div>
                        <label for="stock_quantity" class="block text-gray-700 text-sm font-bold mb-2">Stock Quantity <span class="text-red-500">*</span></label>
                        <input type="number" name="stock_quantity" id="stock_quantity" min="0"
                            class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., 100" required value="<?= htmlspecialchars($_POST['stock_quantity'] ?? '') ?>">
                    </div>
                    <div>
                        <label for="sku" class="block text-gray-700 text-sm font-bold mb-2">SKU (Stock Keeping Unit)</label>
                        <input type="text" name="sku" id="sku"
                            class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., SKU-HEADPHONES-001" value="<?= htmlspecialchars($_POST['sku'] ?? '') ?>">
                    </div>
                    <div>
                        <label for="brand" class="block text-gray-700 text-sm font-bold mb-2">Brand (Optional)</label>
                        <input type="text" name="brand" id="brand"
                            class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., Acme Electronics" value="<?= htmlspecialchars($_POST['brand'] ?? '') ?>">
                    </div>
                    <div>
                        <label for="weight" class="block text-gray-700 text-sm font-bold mb-2">Weight (kg/lbs, Optional)</label>
                        <input type="number" name="weight" id="weight" step="0.01" min="0"
                            class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., 0.5" value="<?= htmlspecialchars($_POST['weight'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label block text-gray-700 text-sm font-bold mb-2">Item Image</label>
                        <input type="file" name="image" id="image" class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Please select a product image.</p>
                    </div>
                </div>
                <div class="mb-6">
                    <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                    <textarea name="description" id="description" rows="5"
                        class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="A detailed description of the product..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <button type="submit" name="add_product"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out transform hover:scale-105">
                    <i class="fas fa-plus-circle mr-2"></i>Add Product
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Existing Products</h2>
            <?php
            // Fetch all products with their category names
            $productsResult = mysqli_query($db, "SELECT p.id, p.product_name, p.price, p.stock_quantity, p.image_url, p.sku, c.category_name 
                                                 FROM products p LEFT JOIN categories c ON p.category_id = c.id 
                                                 ORDER BY p.product_name ASC");

            if ($productsResult && mysqli_num_rows($productsResult) > 0) {
                echo '<div class="overflow-x-auto">';
                echo '<table class="min-w-full bg-white border border-gray-200 rounded-lg">';
                echo '<thead>';
                echo '<tr class="bg-gray-100 border-b">';
                echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>';
                echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>';
                echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>';
                echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>';
                echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>';
                echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>';
                echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>';
                echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody class="divide-y divide-gray-200">';
                while ($product = mysqli_fetch_assoc($productsResult)) {
                    echo '<tr>';
                    echo '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">' . htmlspecialchars($product['id']) . '</td>';
                    echo '<td class="px-6 py-4 whitespace-nowrap">';
                    if (!empty($product['image_url'])) {
                        echo '<img src="../../dataimg/'. htmlspecialchars($product['image_url']) . '" alt="' . htmlspecialchars($product['product_name']) . '" class="product-thumbnail"  style="max-width: 150px; height: 100px;">';
                    } else {
                        echo '<i class="fas fa-image text-gray-400 text-2xl"></i>';
                    }
                    echo '</td>';
                    echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' . htmlspecialchars($product['product_name']) . '</td>';
                    echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' . htmlspecialchars($product['category_name'] ?? 'N/A') . '</td>';
                    echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">$' . number_format(htmlspecialchars($product['price']), 2) . '</td>';
                    echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' . htmlspecialchars($product['stock_quantity']) . '</td>';
                    echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' . htmlspecialchars($product['sku']) . '</td>';
                    echo '<td class="px-6 py-4 whitespace-nowrap text-sm">';
                    // Edit link (placeholder for future functionality)
                    echo '<a href="edit_product.php?id=' . htmlspecialchars($product['id']) . '" class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-edit"></i> Edit</a>';
                    // Delete link
                    echo '<a href="products.php?action=delete&id=' . htmlspecialchars($product['id']) . '" class="text-red-600 hover:text-red-900" onclick="return confirm(\'Are you sure you want to delete product \\\'' . htmlspecialchars($product['product_name']) . '\\\'? This action cannot be undone.\');"><i class="fas fa-trash-alt"></i> Delete</a>';
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
                echo '</div>';
            } else {
                echo '<p class="text-gray-600">No products found. Add one above!</p>';
            }
            // Close the database connection
            mysqli_close($db);
            ?>
        </div>
    </main>
</div>

<footer class="bg-gray-800 text-white p-4 text-center text-sm shadow-inner">
    <div class="container mx-auto">
        &copy; 2025 Online Store. All rights reserved.
    </div>
</footer>
</body>
</html>