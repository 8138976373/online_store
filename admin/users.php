<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Online Store Dashboard</title>
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

    // --- Handle Deleting a User ---
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $userId = (int)$_GET['id']; // Cast to integer for security

        // Optional: Prevent deleting the currently logged-in user
        if ($userId === (int)$_SESSION['user_id']) {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">You cannot delete your own account from this interface.</div>';
        } else {
            // Use prepared statement for deletion
            // Consider foreign key constraints (e.g., orders placed by this user)
            // ON DELETE CASCADE on 'orders.user_id' will delete orders if user is deleted.
            // If you used ON DELETE RESTRICT, deletion will fail if orders exist.
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $userId); // 'i' for integer
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">User deleted successfully!</div>';
                    } else {
                        $message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">User not found or already deleted.</div>';
                    }
                } else {
                    $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Error deleting user: ' . htmlspecialchars($stmt->error) . '</div>';
                }
                $stmt->close();
            } else {
                $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Database error preparing statement for user deletion.</div>';
            }
        }
        // Redirect to clear the GET parameters after deletion
        header('Location: users.php');
        exit();
    }
     include('../includes/header.php');
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
                        <a href="orders.php" class="flex items-center py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-200">
                            <i class="fas fa-shopping-cart mr-3"></i>Orders
                        </a>
                    </li>
                    <li class="mb-3">
                        <a href="users.php" class="flex items-center py-2 px-4 rounded-lg bg-gray-700 text-white transition duration-200">
                            <i class="fas fa-users mr-3"></i>Users
                        </a>
                    </li>
                    </ul>
            </nav>
        </aside>

        <main class="flex-1 p-8 overflow-y-auto">
            <h1 class="text-4xl font-extrabold text-gray-900 mb-6">Manage Users</h1>

            <?= $message ?>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Registered Users</h2>
                <?php
                // Fetch all users
                // Assuming 'users' table has id, username, email (optional), role (optional), created_at
                $usersResult = mysqli_query($db, "SELECT id, username, email, role, created_at FROM users ORDER BY username ASC");

                if ($usersResult && mysqli_num_rows($usersResult) > 0) {
                    echo '<div class="overflow-x-auto">';
                    echo '<table class="min-w-full bg-white border border-gray-200 rounded-lg">';
                    echo '<thead>';
                    echo '<tr class="bg-gray-100 border-b">';
                    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>';
                    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>';
                    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>';
                    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>';
                    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered On</th>';
                    echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody class="divide-y divide-gray-200">';
                    while ($user = mysqli_fetch_assoc($usersResult)) {
                        echo '<tr>';
                        echo '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">' . htmlspecialchars($user['id']) . '</td>';
                        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' . htmlspecialchars($user['username']) . '</td>';
                        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' . htmlspecialchars($user['email'] ?? 'N/A') . '</td>';
                        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' . htmlspecialchars(ucfirst($user['role'] ?? 'customer')) . '</td>'; // Default to 'customer' if role not set
                        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">' . date('M d, Y', strtotime(htmlspecialchars($user['created_at']))) . '</td>';
                        echo '<td class="px-6 py-4 whitespace-nowrap text-sm">';
                        // Edit link (placeholder for future functionality)
                        echo '<a href="edit_user.php?id=' . htmlspecialchars($user['id']) . '" class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-edit"></i> Edit</a>';

                        // Delete link: Add extra check to prevent deleting self
                        if ((int)$user['id'] !== (int)$_SESSION['user_id']) {
                            echo '<a href="users.php?action=delete&id=' . htmlspecialchars($user['id']) . '" class="text-red-600 hover:text-red-900" onclick="return confirm(\'Are you sure you want to delete user \\\'' . htmlspecialchars($user['username']) . '\\\'? This action cannot be undone and will affect associated orders.\');"><i class="fas fa-trash-alt"></i> Delete</a>';
                        } else {
                            echo '<span class="text-gray-400 cursor-not-allowed ml-3" title="Cannot delete your own account"><i class="fas fa-trash-alt"></i> Delete</span>';
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                } else {
                    echo '<p class="text-gray-600">No users found.</p>';
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