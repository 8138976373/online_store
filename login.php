<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Store Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom styles for the Inter font */
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="bg-blue-600 text-white p-4 shadow-md">
        <nav class="container mx-auto flex items-center justify-between">
            <a class="text-2xl font-bold flex items-center" href="/online_store/admin/admin_dashboard.php">
                <i class="fas fa-store me-2 mr-2"></i>
                Online Store
            </a>
            <button class="lg:hidden text-white focus:outline-none">
                <span class="sr-only">Open main menu</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </nav>
    </header>

    <main class="flex-grow flex items-center justify-center p-4">
        <div class="bg-white shadow-lg rounded-xl p-8 max-w-md w-full">
            <h3 class="text-3xl font-semibold text-center mb-6 text-gray-800">Customer Login</h3>

            <?php
            // Start the session at the very beginning of the PHP script
            // session_start();

            
            

            // Check if the form has been submitted
           

            // Display error messages if any
            if (isset($_SESSION['login_error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($_SESSION['login_error']) ?></span>
                </div>
                <?php unset($_SESSION['login_error']); // Clear the error after displaying ?>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                    <input type="text" name="username" id="username"
                        class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter Username" required>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" name="password" id="password"
                        class="shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 mb-3 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter Password" required>
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline w-full transition duration-300 ease-in-out transform hover:scale-105">
                        Login
                    </button>
                </div>
            </form>

            <!-- <div class="mt-6 text-center">
                <a href="index.php" class="text-blue-600 hover:underline text-sm mr-4">Back to Home</a>
                <a href="register.php" class="text-blue-600 hover:underline text-sm">Register Now</a>
            </div> -->
        </div>
    </main>

    <footer class="bg-gray-800 text-white p-4 text-center text-sm">
        <div class="container mx-auto">
            &copy; 2025 Online Store. All rights reserved.
        </div>
    </footer>
</body>

</html>
<?php
session_start();
include('dbconnect.php');
if (isset($_POST['username']) && isset($_POST['password'])) {
    $Username = $_POST['username'];
    $Password = $_POST['password'];
    /*........... query for login.............*/
    $sql = "SELECT * FROM users WHERE username = '$Username' and password = '$Password'";
    $data = mysqli_query($db, $sql);   /*include two variable database($db) and query($sql) and finally store $data variable */
    $result = mysqli_num_rows($data);/*data are feech then check how many data are feetch*/
    if ($result == 1) {
        $row = mysqli_fetch_assoc($data); // Fetch row as associative array
        $_SESSION['user_id'] = $row['id'];

        $_SESSION['username'] = $Username;
        header('location:/online_store/admin/admin_dashboard.php'); /*redirect page whene you want*/
    } else {
        echo "failed";
    }
}
?>