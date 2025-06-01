<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); 
    exit();
}

$loggedInUsername = $_SESSION['username'] ?? 'Guest'; // Get logged-in username from session
$message = ''; // Initialize message variable for displaying alerts (can be set in individual pages)

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Online Store Dashboard'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="bg-blue-700 text-white p-4 shadow-md z-10">
        <div class="container mx-auto flex items-center justify-between">
            <a class="text-2xl font-bold flex items-center" href="dashboard.php">
                <i class="fas fa-store mr-3"></i> Online Store Dashboard
            </a>
            <div class="flex items-center space-x-4">
                <span class="text-lg">Welcome, **<?= htmlspecialchars($loggedInUsername) ?>**!</span>
                 <a href="/online_store/index.php" class="text-white hover:text-blue-200 font-semibold py-2 px-3 rounded-lg transition duration-300 ease-in-out flex items-center">
                    <i class="fas fa-shopping-bag mr-2"></i> Visit Store
                </a>
                <a href="../logout.php" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-300 ease-in-out">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </div>
    </header>
