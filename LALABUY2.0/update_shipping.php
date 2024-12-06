<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

// Check if the required data is provided in the POST request
if (isset($_POST['order_id']) && isset($_POST['product_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $product_id = $_POST['product_id'];
    $status = $_POST['status'];

    // Optional: If you want to update the tracking number as well
    $tracking_number = isset($_POST['tracking_number']) ? $_POST['tracking_number'] : '';

    // Query to check if the product exists for the given product_id
    $sql_check_product = "SELECT * FROM products WHERE product_id = ? AND user_id = ?";
    $stmt_check_product = $conn->prepare($sql_check_product);
    $stmt_check_product->bind_param("ii", $product_id, $_SESSION["user"]);
    $stmt_check_product->execute();
    $result_check_product = $stmt_check_product->get_result();

    if ($result_check_product->num_rows == 0) {
        // If the product is not found, show an error message
        echo "Invalid product ID.";
        exit();
    }

    // Query to update the order status and tracking number
    $sql_update_order = "UPDATE orders o
                         JOIN order_items oi ON o.order_id = oi.order_id
                         SET o.status = ?, o.tracking_number = ?
                         WHERE o.order_id = ? AND oi.product_id = ?";
    $stmt_update_order = $conn->prepare($sql_update_order);
    $stmt_update_order->bind_param("ssii", $status, $tracking_number, $order_id, $product_id);
    $stmt_update_order->execute();

    if ($stmt_update_order->affected_rows > 0) {
        // Redirect to the seller page after successful update
        header("Location: sellerpage.php?product_id=" . $product_id);
        exit();
    } else {
        echo "Failed to update the order.";
    }
} else {
    echo "Invalid request.";
}
?>
