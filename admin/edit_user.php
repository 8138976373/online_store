<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Online Store Dashboard</title>
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

    // Protect this page: Redirect if not logged in or if not an admin (optional, but recommended for user management)
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
    // Optional: Check if the logged-in user is an admin
    // if ($_SESSION['user_role'] !== 'admin') { // Assuming you store user role in session
    //     header('Location: /online_store/admin/admin_dashboard.php'); // Redirect non-admins
    //     exit();
    // }
     include('../includes/header.php');

    $loggedInUsername = $_SESSION['username'] ?? 'Guest';
    $message = ''; // To store success or error messages
    $user_data = null; // To hold the user's current data

    // 1. Fetch User Data to Pre-fill the Form
    if (isset($_GET['id'])) {
        $userId = (int)$_GET['id'];

        // Use prepared statement to fetch user details
        $stmt = $db->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user_data = $result->fetch_assoc();
            } else {
                $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">User not found.</div>';
                $userId = null; // Invalidate userId if user not found
            }
            $stmt->close();
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Database error preparing statement for fetching user.</div>';
            $userId = null;
        }
    } else {
        $message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">No user ID provided.</div>';
    }


    // 2. Handle Form Submission (Update User)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user']) && $userId !== null) {
        $userIdToUpdate = (int)$_POST['user_id'];
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // Basic validation
        if (empty($username) || empty($email) || empty($role)) {
            $message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">Username, Email, and Role cannot be empty.</div>';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">Invalid email format.</div>';
        }
        // Password validation if provided
        elseif (!empty($newPassword)) {
            if ($newPassword !== $confirmPassword) {
                $message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">New password and confirm password do not match.</div>';
            } elseif (strlen($newPassword) < 8) { // Example: minimum password length
                $message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">New password must be at least 8 characters long.</div>';
            }
        }

        if (empty($message)) { // Only proceed if no validation errors
            $update_password_sql = '';
            $hashedPassword = null;
            if (!empty($newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $update_password_sql = ', password = ?';
            }

            // Construct the SQL query dynamically based on whether password is being updated
            $sql = "UPDATE users SET username = ?, email = ?, role = ? {$update_password_sql} WHERE id = ?";
            $stmt = $db->prepare($sql);

            if ($stmt) {
                if (!empty($newPassword)) {
                    // ssssi: username (s), email (s), role (s), password (s), id (i)
                    $stmt->bind_param("ssssi", $username, $email, $role, $hashedPassword, $userIdToUpdate);
                } else {
                    // sssi: username (s), email (s), role (s), id (i)
                    $stmt->bind_param("sssi", $username, $email, $role, $userIdToUpdate);
                }

                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">User **' . htmlspecialchars($username) . '** updated successfully!</div>';
                        // Re-fetch user data to update the form with new values
                        // This prevents user seeing old data in form after update
                        $stmt_re_fetch = $db->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
                        if ($stmt_re_fetch) {
                            $stmt_re_fetch->bind_param("i", $userIdToUpdate);
                            $stmt_re_fetch->execute();
                            $user_data = $stmt_re_fetch->get_result()->fetch_assoc();
                            $stmt_re_fetch->close();
                        }
                    } else {
                        $message = '<div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">No changes made to user **' . htmlspecialchars($username) . '** (data was identical).</div>';
                    }
                } else {
                    $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Error updating user: ' . htmlspecialchars($stmt->error) . '</div>';
                }
                $stmt->close();
            } else {
                $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Database error preparing update statement.</div>';
            }
        }
    }
    // Close the database connection
    mysqli_close($db);
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
                        <a href="/online_store/admin/users.php" class="flex items-center py-2 px-4 rounded-lg bg-gray-700 text-white transition duration-200">
                            <i class="fas fa-users mr-3"></i>Users
                        </a>
                    </li>
                    </ul>
            </nav>
        </aside>

        <main class="flex-1 p-8 overflow-y-auto">
            <h1 class="text-4xl font-extrabold text-gray-900 mb-6">Edit User: <?= htmlspecialchars($user_data['username'] ?? 'N/A') ?></h1>

            <?= $message ?>

            <?php if ($user_data): ?>
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">User Details</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_data['id']) ?>">

                        <div class="mb-4">
                            <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                            <input type="text" name="username" id="username"
                                class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                                value="<?= htmlspecialchars($user_data['username']) ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                            <input type="email" name="email" id="email"
                                class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                                value="<?= htmlspecialchars($user_data['email']) ?>" required>
                        </div>

                        <div class="mb-6">
                            <label for="role" class="block text-gray-700 text-sm font-bold mb-2">Role</label>
                            <select name="role" id="role"
                                class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                                <option value="customer" <?= ($user_data['role'] === 'customer') ? 'selected' : '' ?>>Customer</option>
                                <option value="admin" <?= ($user_data['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                            </select>
                            <?php if ((int)$user_data['id'] === (int)$_SESSION['user_id']): ?>
                                <p class="text-red-500 text-xs italic mt-1">Caution: You are editing your own role. Changing this may affect your access.</p>
                            <?php endif; ?>
                        </div>

                        <hr class="my-6 border-gray-300">

                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Change Password (Optional)</h3>
                        <p class="text-gray-600 text-sm mb-4">Only fill these fields if you want to change the user's password.</p>

                        <div class="mb-4">
                            <label for="new_password" class="block text-gray-700 text-sm font-bold mb-2">New Password</label>
                            <input type="password" name="new_password" id="new_password"
                                class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Enter new password">
                        </div>

                        <div class="mb-6">
                            <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirm_password"
                                class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Confirm new password">
                        </div>

                        <button type="submit" name="update_user"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out transform hover:scale-105">
                            <i class="fas fa-save mr-2"></i>Update User
                        </button>
                        <a href="users.php" class="ml-4 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded-lg transition duration-300 ease-in-out">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Users List
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