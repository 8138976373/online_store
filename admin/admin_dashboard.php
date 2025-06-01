<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Store Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom styles for the Inter font (optional) */
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">
    <?php
    session_start();
    // Optional: Protect this page
    // If user is not logged in, redirect them to the login page
    if (!isset($_SESSION['user_id'])) {
        header('Location: /online_store/login.php'); // Assuming your login page is login.php
        exit();
    }
    include('../includes/header.php');

    // You can also display the logged-in username
    $loggedInUsername = $_SESSION['username'] ?? 'Guest';
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
                        <a href="/online_store/admin/category/categories.php" class="flex items-center py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-200">
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
            <h1 class="text-4xl font-extrabold text-gray-900 mb-6">Dashboard Overview</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-700">Total Categories</h2>
                        <i class="fas fa-tags text-blue-500 text-3xl"></i>
                    </div>
                    <p class="text-5xl font-bold text-gray-900">
                        <?php
                            // PHP to fetch total categories
                            include('../dbconnect.php'); // Ensure this connects properly
                            $result = mysqli_query($db, "SELECT COUNT(*) AS total_categories FROM categories");
                            $row = mysqli_fetch_assoc($result);
                            echo htmlspecialchars($row['total_categories'] ?? 0);
                            mysqli_close($db); // Close connection after use
                        ?>
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-700">Total Products</h2>
                        <i class="fas fa-box-open text-green-500 text-3xl"></i>
                    </div>
                    <p class="text-5xl font-bold text-gray-900">
                        <?php
                            // PHP to fetch total products
                            include('../dbconnect.php'); // Re-include if db connection is not persistent
                            $result = mysqli_query($db, "SELECT COUNT(*) AS total_products FROM products");
                            $row = mysqli_fetch_assoc($result);
                            echo htmlspecialchars($row['total_products'] ?? 0);
                            mysqli_close($db);
                        ?>
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-700">Pending Orders</h2>
                        <i class="fas fa-hourglass-half text-yellow-500 text-3xl"></i>
                    </div>
                    <p class="text-5xl font-bold text-gray-900">
                        <?php
                            // PHP to fetch pending orders
                            // Example: assuming an 'orders' table with a 'status' column
                            include('../dbconnect.php');
                            $result = mysqli_query($db, "SELECT COUNT(*) AS pending_orders FROM orders WHERE status = 'pending'");
                            $row = mysqli_fetch_assoc($result);
                            echo htmlspecialchars($row['pending_orders'] ?? 0);
                            mysqli_close($db);
                        ?>
                    </p>
                </div>
                </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Recent Activity</h2>
                <ul class="list-disc list-inside text-gray-700">
                    <li><span class="font-bold">2025-06-01 10:30 AM:</span> New product "Wireless Headphones" added.</li>
                    <li><span class="font-bold">2025-06-01 09:15 AM:</span> Category "Electronics" updated.</li>
                    <li><span class="font-bold">2025-05-31 03:45 PM:</span> Order #12345 placed by Customer A.</li>
                    </ul>
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