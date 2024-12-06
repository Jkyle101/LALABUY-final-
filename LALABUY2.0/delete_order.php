<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: orders.php?error=Unauthorized%20action");
    exit();
}

// Validate order_id and redirect_url
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$redirect_url = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : 'orders.php';

if ($order_id <= 0) {
    header("Location: $redirect_url?error=Missing%20order_id");
    exit();
}

// Ensure the order belongs to the logged-in user
$sql = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $_SESSION['user']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: $redirect_url?error=Unauthorized%20action");
    exit();
}

// Delete the order
$sql = "DELETE FROM orders WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
    header("Location: $redirect_url?delete_success=true");
} else {
    header("Location: $redirect_url?delete_error=true");
}

exit();
?>
