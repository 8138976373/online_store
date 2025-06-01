<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Online Store Dashboard</title>
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
    include('../dbconnect.php'); // Your database connection file

    // Protect this page: Redirect if not logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    $loggedInUsername = $_SESSION['username'] ?? 'Guest';
    $message = ''; // To store success or error messages
 include('../includes/header.php');
    // --- Handle Updating Order Status (Example) ---
    // You'd typically have a form or AJAX call for this on an "Edit Order" page
    // For simplicity, we'll just demonstrate fetching and displaying for now.
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
                        <a href="/online_store/admin/orders.php" class="flex items-center py-2 px-4 rounded-lg bg-gray-700 text-white transition duration-200">
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
            <h1 class="text-4xl font-extrabold text-gray-900 mb-6">Manage Orders</h1>

            <?= $message ?>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">All Orders</h2>
                <?php
                // Fetch all orders, joining with the 'users' table to show the customer's username
                $ordersQuery = "SELECT 
                                    o.id AS order_id, 
                                    o.order_date, 
                                    o.total_amount, 
                                    o.status, 
                                    o.payment_status,
                                    u.username AS customer_username 
                                FROM 
                                    orders o 
                                LEFT JOIN 
                                    users u ON o.user_id = u.id 
                                ORDER BY 
                                    o.order_date DESC";

                $ordersResult = mysqli_query($db, $ordersQuery);

                if ($ordersResult && mysqli_num_rows($ordersResult) > 0) {
                    echo '<div class="overflow-x-auto">';
                    echo '<table class="min-w-full bg-white border border-gray-200 rounded-lg">';
                    echo '<thead>';
                    echo '<tr class="bg-gray-100 border-b">';
                    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>';
                    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>';
                    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>';
                    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>';
                    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>';
                    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Status</th>';
                    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody class="divide-y divide-gray-200">';
                    while ($order = mysqli_fetch_assoc($ordersResult)) {
                        // Determine status badge color
                        $statusColor = '';
                        switch ($order['status']) {
                            case 'pending': $statusColor = 'bg-yellow-200 text-yellow-800'; break;
                            case 'processing': $statusColor = 'bg-blue-200 text-blue-800'; break;
                            case 'shipped': $statusColor = 'bg-indigo-200 text-indigo-800'; break;
                            case 'delivered': $statusColor = 'bg-green-200 text-green-800'; break;
                            case 'cancelled':
                            case 'refunded': $statusColor = 'bg-red-200 text-red-800'; break;
                            default: $statusColor = 'bg-gray-200 text-gray-800'; break;
                        }

                        // Determine payment status badge color
                        $paymentStatusColor = '';
                        switch ($order['payment_status']) {
                            case 'paid': $paymentStatusColor = 'bg-green-200 text-green-800'; break;
                            case 'pending': $paymentStatusColor = 'bg-yellow-200 text-yellow-800'; break;
                            case 'unpaid': $paymentStatusColor = 'bg-red-200 text-red-800'; break;
                            case 'refunded': $paymentStatusColor = 'bg-purple-200 text-purple-800'; break;
                            default: $paymentStatusColor = 'bg-gray-200 text-gray-800'; break;
                        }

                        echo '<tr>';
                        echo '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">' . htmlspecialchars($order['order_id']) . '</td>';
                        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' . htmlspecialchars($order['customer_username'] ?? 'N/A') . '</td>';
                        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' . date('M d, Y H:i', strtotime(htmlspecialchars($order['order_date']))) . '</td>';
                        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">$' . number_format(htmlspecialchars($order['total_amount']), 2) . '</td>';
                        echo '<td class="px-6 py-4 whitespace-nowrap text-sm">';
                        echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' . $statusColor . '">';
                        echo ucfirst(htmlspecialchars($order['status']));
                        echo '</span>';
                        echo '</td>';
                        echo '<td class="px-6 py-4 whitespace-nowrap text-sm">';
                        echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' . $paymentStatusColor . '">';
                        echo ucfirst(htmlspecialchars($order['payment_status']));
                        echo '</span>';
                        echo '</td>';
                        echo '<td class="px-6 py-4 whitespace-nowrap text-sm">';
                        // View Details link (placeholder for future functionality)
                        echo '<a href="view_order.php?id=' . htmlspecialchars($order['order_id']) . '" class="text-blue-600 hover:text-blue-900 mr-3"><i class="fas fa-eye"></i> View</a>';
                        // Edit link (placeholder for future functionality)
                        echo '<a href="edit_order.php?id=' . htmlspecialchars($order['order_id']) . '" class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-edit"></i> Edit</a>';
                        // Delete link (use with caution, typically orders are archived, not deleted)
                        // echo '<a href="orders.php?action=delete&id=' . htmlspecialchars($order['order_id']) . '" class="text-red-600 hover:text-red-900" onclick="return confirm(\'Are you sure you want to delete this order? This action cannot be undone and may affect reporting.\');"><i class="fas fa-trash-alt"></i> Delete</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                } else {
                    echo '<p class="text-gray-600">No orders found.</p>';
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