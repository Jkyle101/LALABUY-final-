<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["user"];

    // Fetch shipping information from the form
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $address2 = isset($_POST['address2']) ? trim($_POST['address2']) : '';
    $zip_code = isset($_POST['zip_code']) ? trim($_POST['zip_code']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $street = isset($_POST['street']) ? trim($_POST['street']) : '';

    // Validate required fields
    if (empty($address) || empty($zip_code) || empty($city) || empty($street)) {
        header("Location: checkout.php?error=Missing required fields");
        exit();
    }

    // Get cart items for the user
    $sql_cart = "SELECT c.product_id, p.price 
                 FROM cart c 
                 JOIN products p ON c.product_id = p.product_id 
                 WHERE c.user_id = ?";
    $stmt_cart = $conn->prepare($sql_cart);
    $stmt_cart->bind_param("i", $user_id);
    $stmt_cart->execute();
    $cart_items = $stmt_cart->get_result();

    if ($cart_items->num_rows === 0) {
        header("Location: cart.php?error=Your cart is empty.");
        exit();
    }

    // Begin transaction
    $conn->begin_transaction();
    try {
        // Calculate total price
        $total_price = 0;
        while ($item = $cart_items->fetch_assoc()) {
            $total_price += $item['price'];
        }

        // Save order information in the `orders` table
        $shipping_address = "$address, $address2, $street, $city, $zip_code";
        $order_status = 'Pending';
        $sql_order = "INSERT INTO orders (user_id, total_price, status, shipping_address) VALUES (?, ?, ?, ?)";
        $stmt_order = $conn->prepare($sql_order);
        $stmt_order->bind_param("idss", $user_id, $total_price, $order_status, $shipping_address);
        $stmt_order->execute();
        $order_id = $conn->insert_id;

        // Save order items in the `order_items` table
        $sql_order_items = "INSERT INTO order_items (order_id, product_id) VALUES (?, ?)";
        $stmt_order_items = $conn->prepare($sql_order_items);
        $cart_items->data_seek(0); // Reset cart result pointer
        while ($item = $cart_items->fetch_assoc()) {
            $stmt_order_items->bind_param("ii", $order_id, $item['product_id']);
            $stmt_order_items->execute();
        }

        // Clear the user's cart
        $sql_clear_cart = "DELETE FROM cart WHERE user_id = ?";
        $stmt_clear_cart = $conn->prepare($sql_clear_cart);
        $stmt_clear_cart->bind_param("i", $user_id);
        $stmt_clear_cart->execute();

        // Commit the transaction
        $conn->commit();
        header("Location: orders.php?success=Order placed successfully!");
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        header("Location: checkout.php?error=Failed to place the order. Please try again.");
    }
} else {
    header("Location: checkout.php?error=Invalid request.");
    exit();
}
