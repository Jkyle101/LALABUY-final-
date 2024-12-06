<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

// Get the order_id from the URL
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : 0;

// Check if the order_id is valid
if ($order_id <= 0) {
    echo "Invalid order ID.";
    exit();
}

// Ensure the order belongs to the logged-in user
$sql = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $_SESSION['user']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Order not found or you are not authorized to cancel this order.";
    exit();
}

// Update the order status to 'Cancelled'
$sql = "UPDATE orders SET status = 'Cancelled' WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
    // Redirect to orders page with a success message
    header("Location: orders.php?cancel_success=true");
} else {
    // Redirect to orders page with an error message
    header("Location: orders.php?cancel_error=true");
}

exit();
?>
