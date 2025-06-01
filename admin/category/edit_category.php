<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category - Online Store Dashboard</title>
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
    $category_data = null; // To hold the category's current data
    $categoryId = null; // Initialize categoryId

    // 1. Fetch Category Data to Pre-fill the Form
    if (isset($_GET['id'])) {
        $categoryId = (int)$_GET['id'];

        // Use prepared statement to fetch category details
        $stmt = $db->prepare("SELECT id, category_name FROM categories WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $categoryId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $category_data = $result->fetch_assoc();
            } else {
                $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Category not found.</div>';
                $categoryId = null; // Invalidate categoryId if not found
            }
            $stmt->close();
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Database error preparing statement for fetching category.</div>';
            $categoryId = null;
        }
    } else {
        $message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">No category ID provided.</div>';
    }


    // 2. Handle Form Submission (Update Category)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category']) && $categoryId !== null) {
        $categoryIdToUpdate = (int)$_POST['category_id'];
        $categoryName = trim($_POST['category_name']);

        if (empty($categoryName)) {
            $message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">Category name cannot be empty.</div>';
        } else {
            // Use prepared statement for update
            $stmt = $db->prepare("UPDATE categories SET category_name = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("si", $categoryName, $categoryIdToUpdate); // 's' for string, 'i' for integer
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Category **' . htmlspecialchars($categoryName) . '** updated successfully!</div>';
                        // Re-fetch category data to update the form with new value
                        // This prevents user seeing old data in form after update
                        $stmt_re_fetch = $db->prepare("SELECT id, category_name FROM categories WHERE id = ?");
                        if ($stmt_re_fetch) {
                            $stmt_re_fetch->bind_param("i", $categoryIdToUpdate);
                            $stmt_re_fetch->execute();
                            $category_data = $stmt_re_fetch->get_result()->fetch_assoc();
                            $stmt_re_fetch->close();
                        }
                    } else {
                        $message = '<div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">No changes made to category **' . htmlspecialchars($categoryName) . '** (name was identical).</div>';
                    }
                } else {
                    $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Error updating category: ' . htmlspecialchars($stmt->error) . '</div>';
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
                        <a href="/online_store/admin/admin_dashboard.php" class="flex items-center py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-200">
                            <i class="fas fa-tachometer-alt mr-3"></i>Dashboard Home
                        </a>
                    </li>
                    <li class="mb-3">
                        <a href="/online_store/admin/category/categories.php" class="flex items-center py-2 px-4 rounded-lg bg-gray-700 text-white transition duration-200">
                            <i class="fas fa-tags mr-3"></i>Categories
                        </a>
                    </li>
                    <li class="mb-3">
                        <a href="/online_store/admin/products/products.php" class="flex items-center py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-200">
                            <i class="fas fa-box-open mr-3"></i>Products
                        </a>
                    </li>
                    <li class="mb-3">
                        <a href="/online_store/admin/orders.php" class="flex items-center py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-200">
                            <i class="fas fa-shopping-cart mr-3"></i>Orders
                        </a>
                    </li>
                    <li class="mb-3">
                        <a href="/online_store/admin/users.php" class="flex items-center py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-200">
                            <i class="fas fa-users mr-3"></i>Users
                        </a>
                    </li>
                    </ul>
            </nav>
        </aside>

        <main class="flex-1 p-8 overflow-y-auto">
            <h1 class="text-4xl font-extrabold text-gray-900 mb-6">Edit Category: <?= htmlspecialchars($category_data['category_name'] ?? 'N/A') ?></h1>

            <?= $message ?>

            <?php if ($category_data): ?>
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Category Details</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="category_id" value="<?= htmlspecialchars($category_data['id']) ?>">

                        <div class="mb-4">
                            <label for="category_name" class="block text-gray-700 text-sm font-bold mb-2">Category Name</label>
                            <input type="text" name="category_name" id="category_name"
                                class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                                value="<?= htmlspecialchars($category_data['category_name']) ?>" required>
                        </div>

                        <button type="submit" name="update_category"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out transform hover:scale-105">
                            <i class="fas fa-save mr-2"></i>Update Category
                        </button>
                        <a href="/online_store/admin/category/categories.php" class="ml-4 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded-lg transition duration-300 ease-in-out">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Categories List
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