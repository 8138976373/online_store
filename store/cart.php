<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart - Online Store</title>
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

    $loggedInUsername = $_SESSION['username'] ?? null;
    $message = ''; // To store success or error messages

    // Initialize cart if not already set
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // --- Handle Add to Cart (e.g., from product page) ---
    // This is a simplified way. A real store might use AJAX or dedicated 'add_to_cart.php'
    if (isset($_POST['add_to_cart']) && isset($_POST['product_id']) && isset($_POST['quantity'])) {
        $productId = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];

        if ($productId > 0 && $quantity > 0) {
            // Check product stock before adding
            $stmt = $db->prepare("SELECT stock_quantity FROM products WHERE id = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            $productStock = $result->fetch_assoc()['stock_quantity'] ?? 0;
            $stmt->close();

            $currentCartQuantity = $_SESSION['cart'][$productId]['quantity'] ?? 0;
            $newQuantity = $currentCartQuantity + $quantity;

            if ($newQuantity <= $productStock) {
                $_SESSION['cart'][$productId] = [
                    'quantity' => $newQuantity
                    // We only store quantity in session; product details are fetched live.
                ];
                $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Item added to cart!</div>';
            } else {
                $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Cannot add ' . $quantity . ' items. Only ' . ($productStock - $currentCartQuantity) . ' left in stock.</div>';
            }
        }
    }

    // --- Handle Update Cart Quantity ---
    if (isset($_POST['update_cart_quantity']) && isset($_POST['product_id']) && isset($_POST['new_quantity'])) {
        $productId = (int)$_POST['product_id'];
        $newQuantity = (int)$_POST['new_quantity'];

        if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
            // Check product stock before updating
            $stmt = $db->prepare("SELECT stock_quantity FROM products WHERE id = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            $productStock = $result->fetch_assoc()['stock_quantity'] ?? 0;
            $stmt->close();

            if ($newQuantity > 0 && $newQuantity <= $productStock) {
                $_SESSION['cart'][$productId]['quantity'] = $newQuantity;
                $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Cart updated!</div>';
            } elseif ($newQuantity == 0) {
                 unset($_SESSION['cart'][$productId]); // Remove if quantity is 0
                 $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Item removed from cart.</div>';
            } else {
                $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Invalid quantity or not enough stock available (Max: ' . $productStock . ').</div>';
            }
        }
        // Redirect to clear POST data and prevent re-submission on refresh
        header('Location: cart.php');
        exit();
    }

    // --- Handle Remove Item from Cart ---
    if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['product_id'])) {
        $productId = (int)$_GET['product_id'];
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Item removed from cart.</div>';
        }
        // Redirect to clear GET data
        header('Location: cart.php');
        exit();
    }

    // --- Fetch Product Details for Display and Calculate Totals ---
    $cartItemsData = [];
    $cartTotal = 0;
    if (!empty($_SESSION['cart'])) {
        $productIds = implode(',', array_keys($_SESSION['cart']));
        
        // Fetch product details from DB for all items in cart using a single query
        $stmt = $db->prepare("SELECT id, product_name, price, stock_quantity, image_url FROM products WHERE id IN ($productIds)");
        
        // No bind_param for IN clause with dynamic array unless you build placeholder string
        // For simplicity with IN clause, direct query is used after sanitizing productIds for integer list.
        // NOTE: For very large carts, this could still be an issue. Parameterized approach is more robust.
        // $sql = "SELECT id, product_name, price, stock_quantity, image_url FROM products WHERE id IN (" . str_repeat('?,', count($_SESSION['cart'])) . ")";
        // $sql = rtrim($sql, ','); // Remove trailing comma
        // $stmt = $db->prepare($sql);
        // if ($stmt) { $stmt->bind_param(str_repeat('i', count($_SESSION['cart'])), ...array_keys($_SESSION['cart'])); ... }
        // For now, simpler but still safer with `implode` after `array_keys` ensures only valid IDs are in string.

        // Fallback to simpler query if prepared statement for IN fails or is too complex for example
        $result = mysqli_query($db, "SELECT id, product_name, price, stock_quantity, image_url FROM products WHERE id IN ($productIds)");
        
        if ($result) {
            while ($product = mysqli_fetch_assoc($result)) {
                $productId = $product['id'];
                if (isset($_SESSION['cart'][$productId])) {
                    $quantityInCart = $_SESSION['cart'][$productId]['quantity'];
                    
                    // Cap quantity at available stock if cart quantity exceeds it (e.g., if stock reduced since adding)
                    if ($quantityInCart > $product['stock_quantity']) {
                        $quantityInCart = $product['stock_quantity'];
                        $_SESSION['cart'][$productId]['quantity'] = $quantityInCart; // Update session
                        $message = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">Some items in your cart have had their quantity adjusted due to low stock.</div>';
                    }
                    if ($quantityInCart > 0) { // Only include if quantity is still positive
                        $itemSubtotal = $product['price'] * $quantityInCart;
                        $cartItemsData[] = [
                            'product_id' => $productId,
                            'product_name' => $product['product_name'],
                            'price' => $product['price'],
                            'quantity' => $quantityInCart,
                            'image_url' => $product['image_url'],
                            'stock_quantity' => $product['stock_quantity'], // Pass stock for min/max
                            'subtotal' => $itemSubtotal
                        ];
                        $cartTotal += $itemSubtotal;
                    } else {
                         unset($_SESSION['cart'][$productId]); // Remove if quantity became 0
                    }
                }
            }
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Error fetching cart product details: ' . htmlspecialchars(mysqli_error($db)) . '</div>';
        }
    }

    mysqli_close($db);
    ?>

    <header class="bg-white text-gray-800 shadow-md p-4 sticky top-0 z-50">
        <div class="container mx-auto flex items-center justify-between">
            <a class="text-3xl font-extrabold text-blue-700 flex items-center" href="index.php">
                <i class="fas fa-store mr-2"></i>My Online Store
            </a>
            <nav class="flex items-center space-x-6">
                <a href="index.php" class="text-gray-700 hover:text-blue-700 font-semibold text-lg transition duration-200">Home</a>
                
                <div class="relative group">
                    <button class="text-gray-700 hover:text-blue-700 font-semibold text-lg transition duration-200 focus:outline-none">
                        Categories <i class="fas fa-caret-down ml-1"></i>
                    </button>
                    <div class="absolute hidden group-hover:block bg-white shadow-lg rounded-md mt-2 py-2 w-48 z-10">
                        <a href="index.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">All Products</a>
                        <?php 
                        // You'd need to re-fetch categories here or pass them from index.php if coming from there
                        // For simplicity, let's assume a basic re-fetch if this were a standalone page
                        // For a real app, consider a central categories fetching function
                        if (!empty($categories)) {
                            foreach ($categories as $cat_nav): ?>
                                <a href="index.php?category_id=<?= htmlspecialchars($cat_nav['id']) ?>" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                    <?= htmlspecialchars($cat_nav['category_name']) ?>
                                </a>
                            <?php endforeach;
                        }
                        ?>
                    </div>
                </div>

                <a href="cart.php" class="text-blue-700 font-semibold text-lg transition duration-200 relative">
                    <i class="fas fa-shopping-cart text-xl"></i>
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full px-2 py-1">
                        <?= count($_SESSION['cart']) ?>
                    </span>
                </a>
                
                <?php if ($loggedInUsername): ?>
                    <div class="relative group">
                        <button class="text-blue-700 font-semibold text-lg transition duration-200 focus:outline-none">
                            <i class="fas fa-user-circle mr-1"></i> <?= htmlspecialchars($loggedInUsername) ?> <i class="fas fa-caret-down ml-1"></i>
                        </button>
                        <div class="absolute hidden group-hover:block bg-white shadow-lg rounded-md mt-2 py-2 w-48 right-0 z-10">
                            <a href="my_account.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">My Account</a>
                            <a href="order_history.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Order History</a>
                            <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-300">
                        Login / Register
                    </a>
                <?php endif; ?>
                
                <a href="dashboard.php" class="text-gray-700 hover:text-blue-700 font-semibold text-lg transition duration-200">
                    <i class="fas fa-cogs mr-1"></i> Admin
                </a>
            </nav>
        </div>
    </header>

    <main class="flex-1 container mx-auto p-8">
        <h1 class="text-5xl font-extrabold text-gray-900 mb-8 text-center">Your Shopping Cart</h1>

        <?= $message ?>

        <?php if (!empty($cartItemsData)): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Items in your Cart</h2>
                    <?php foreach ($cartItemsData as $item): ?>
                        <div class="flex items-center border-b border-gray-200 py-4 last:border-b-0">
                            <div class="flex-shrink-0 w-24 h-24 mr-4">
                                <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="w-full h-full object-cover rounded-md">
                            </div>
                            <div class="flex-grow">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <a href="product_detail.php?id=<?= htmlspecialchars($item['product_id']) ?>" class="hover:text-blue-700">
                                        <?= htmlspecialchars($item['product_name']) ?>
                                    </a>
                                </h3>
                                <p class="text-gray-700 text-sm">Price: $<?= number_format(htmlspecialchars($item['price']), 2) ?></p>
                                <p class="text-gray-700 text-sm">Subtotal: $<?= number_format(htmlspecialchars($item['subtotal']), 2) ?></p>
                                <p class="text-gray-500 text-xs">Available: <?= htmlspecialchars($item['stock_quantity']) ?> in stock</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <form action="cart.php" method="POST" class="flex items-center">
                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['product_id']) ?>">
                                    <input type="number" name="new_quantity" value="<?= htmlspecialchars($item['quantity']) ?>"
                                        min="1" max="<?= htmlspecialchars($item['stock_quantity']) ?>"
                                        class="w-20 px-3 py-2 border border-gray-300 rounded-md text-center focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700"
                                        onchange="this.form.submit()"> <button type="submit" name="update_cart_quantity" class="ml-2 bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-md transition duration-200" title="Update Quantity">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </form>
                                <a href="cart.php?action=remove&product_id=<?= htmlspecialchars($item['product_id']) ?>"
                                   class="text-red-600 hover:text-red-800 ml-4 p-2" title="Remove Item"
                                   onclick="return confirm('Are you sure you want to remove this item from your cart?');">
                                    <i class="fas fa-trash-alt text-lg"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6 h-fit">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Cart Summary</h2>
                    <div class="flex justify-between items-center text-lg mb-2">
                        <span class="text-gray-700">Subtotal:</span>
                        <span class="font-bold text-gray-900">$<?= number_format($cartTotal, 2) ?></span>
                    </div>
                    <div class="flex justify-between items-center text-lg mb-4 border-b pb-4">
                        <span class="text-gray-700">Shipping:</span>
                        <span class="font-bold text-gray-900">Calculated at Checkout</span>
                    </div>
                    <div class="flex justify-between items-center text-2xl font-bold mb-6">
                        <span>Total:</span>
                        <span class="text-blue-700">$<?= number_format($cartTotal, 2) ?></span>
                    </div>
                    
                    <a href="checkout.php" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-center transition duration-300 ease-in-out">
                        <i class="fas fa-credit-card mr-2"></i> Proceed to Checkout
                    </a>
                    <a href="index.php" class="block w-full bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded-lg text-center mt-3 transition duration-300 ease-in-out">
                        <i class="fas fa-shopping-bag mr-2"></i> Continue Shopping
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <p class="text-gray-700 text-xl mb-4">Your cart is empty.</p>
                <a href="/online_store/index.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg inline-block transition duration-300 ease-in-out">
                    <i class="fas fa-shopping-bag mr-2"></i> Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-gray-800 text-white p-6 text-center text-sm mt-8">
        <div class="container mx-auto">
            <p>&copy; 2025 My Online Store. All rights reserved.</p>
            <p class="mt-2">
                <a href="#" class="hover:text-blue-400 mx-2">Privacy Policy</a> | 
                <a href="#" class="hover:text-blue-400 mx-2">Terms of Service</a> | 
                <a href="#" class="hover:text-blue-400 mx-2">Contact Us</a>
            </p>
        </div>
    </footer>
</body>

</html>