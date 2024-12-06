<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

// Check if 'id' is set in the URL and if it's a valid product ID
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $user_id = $_SESSION["user"]; // The logged-in user ID
    
    // Check if the product is already in the cart
    $sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // If the product is already in the cart, update the quantity
        $sql = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
    } else {
        // If the product is not in the cart, insert a new record
        $sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
    }

    // Redirect back to the homepage or product listing page with a success message
    header("Location: index.php?msg=Product added to cart successfully.");
    exit();
} else {
    // If no product ID is provided, redirect to the homepage with an error message
    header("Location: index.php?msg=Invalid product.");
    exit();
}
?>
