<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

if (isset($_GET["id"])) {
    $product_id = $_GET["id"];
    $user_id = $_SESSION["user"];
    
    // SQL to remove the product from the cart
    $sql = "DELETE FROM cart WHERE product_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $product_id, $user_id);
    
    if ($stmt->execute()) {
        // Redirect to cart page with a success message
        header("Location: cart.php?msg=Product removed from cart successfully");
    } else {
        // Redirect to cart page with an error message
        header("Location: cart.php?msg=Failed to remove product from cart");
    }
} else {
    // Redirect to cart page if no product ID is provided
    header("Location: cart.php?msg=No product selected");
}
exit();
?>
