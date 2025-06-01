<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Online Store Dashboard</title>
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

    // Handle adding a new category
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
        $categoryName = trim($_POST['category_name']);
        if (!empty($categoryName)) {
            // Use prepared statement to prevent SQL injection
            $stmt = $db->prepare("INSERT INTO categories (category_name) VALUES (?)");
            if ($stmt) {
                $stmt->bind_param("s", $categoryName);
                if ($stmt->execute()) {
                    $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Category **' . htmlspecialchars($categoryName) . '** added successfully!</div>';
                } else {
                    $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Error adding category: ' . htmlspecialchars($stmt->error) . '</div>';
                }
                $stmt->close();
            } else {
                $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Database error preparing statement.</div>';
            }
        } else {
            $message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">Category name cannot be empty.</div>';
        }
    }

    // Handle deleting a category
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $categoryId = (int)$_GET['id']; // Cast to integer for security

        // Use prepared statement for deletion
        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $categoryId); // 'i' for integer
            if ($stmt->execute()) {
                $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Category deleted successfully!</div>';
            } else {
                $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Error deleting category: ' . htmlspecialchars($stmt->error) . '</div>';
            }
            $stmt->close();
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Database error preparing statement for deletion.</div>';
        }
        // Redirect to clear the GET parameters after deletion
        header('Location: /online_store/admin/category/categories.php');
        exit();
    }
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
            <h1 class="text-4xl font-extrabold text-gray-900 mb-6">Manage Categories</h1>

            <?= $message ?>

            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Add New Category</h2>
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="category_name" class="block text-gray-700 text-sm font-bold mb-2">Category Name</label>
                        <input type="text" name="category_name" id="category_name"
                            class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., Electronics, Clothing, Books" required>
                    </div>
                    <button type="submit" name="add_category"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out transform hover:scale-105">
                        <i class="fas fa-plus-circle mr-2"></i>Add Category
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Existing Categories</h2>
                <?php
                // Fetch all categories
                $categoriesResult = mysqli_query($db, "SELECT id, category_name FROM categories ORDER BY category_name ASC");
                if (mysqli_num_rows($categoriesResult) > 0) {
                    echo '<div class="overflow-x-auto">';
                    echo '<table class="min-w-full bg-white border border-gray-200 rounded-lg">';
                    echo '<thead>';
                    echo '<tr class="bg-gray-100 border-b">';
                    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>';
                    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category Name</th>';
                    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody class="divide-y divide-gray-200">';
                    while ($category = mysqli_fetch_assoc($categoriesResult)) {
                        echo '<tr>';
                        echo '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">' . htmlspecialchars($category['id']) . '</td>';
                        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' . htmlspecialchars($category['category_name']) . '</td>';
                        echo '<td class="px-6 py-4 whitespace-nowrap text-sm">';
                        // Edit link (placeholder for future functionality)
                        echo '<a href="edit_category.php?id=' . htmlspecialchars($category['id']) . '" class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-edit"></i> Edit</a>';
                        // Delete link
                        echo '<a href="/online_store/admin/category/categories.php?action=delete&id=' . htmlspecialchars($category['id']) . '" class="text-red-600 hover:text-red-900" onclick="return confirm(\'Are you sure you want to delete this category? This action cannot be undone.\');"><i class="fas fa-trash-alt"></i> Delete</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                } else {
                    echo '<p class="text-gray-600">No categories found. Add one above!</p>';
                }
                // Close the database connection after fetching data
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