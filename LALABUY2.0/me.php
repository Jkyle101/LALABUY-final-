<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

// Fetch the logged-in user's ID from session
$user_id = $_SESSION["user"];

// Query to fetch the user's products
$sql_products = "SELECT * FROM products WHERE user_id = ?";
$stmt_products = $conn->prepare($sql_products);
$stmt_products->bind_param("i", $user_id);
$stmt_products->execute();
$result_products = $stmt_products->get_result();

// Query to fetch orders for the user's products
$sql_orders = "
    SELECT o.order_id, o.total_price, o.order_date, o.status, p.product_name, o.tracking_number 
    FROM orders o 
    JOIN order_items oi ON o.order_id = oi.order_id 
    JOIN products p ON oi.product_id = p.product_id 
    WHERE p.user_id = ?";
$stmt_orders = $conn->prepare($sql_orders);
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_order"])) {
    $order_id = $_POST["order_id"];
    $tracking_number = $_POST["tracking_number"];
    $status = $_POST["status"];

    $sql_update_order = "UPDATE orders SET tracking_number = ?, status = ? WHERE order_id = ?";
    $stmt_update_order = $conn->prepare($sql_update_order);
    $stmt_update_order->bind_param("ssi", $tracking_number, $status, $order_id);
    $stmt_update_order->execute();

    header("Location: me.php?msg=Order updated successfully!");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <title>LALABUY | Me</title>
    <style>
        .clickable-row {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header class="top" style="max-height:100vh;">
        <nav class="navbar navbar-expand-xl navbar-light" style="background-color: #e3f2fd; margin-top:20px; border-radius:20px; padding:15px">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php?" style="color:blue; font-weight:bold;">LALABUY</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarLight" aria-controls="navbarLight" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse show" id="navbarLight">
                    <ul class="navbar-nav me-auto mb-2 mb-xl-0">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="addproduct.php?">Add Product</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="#">My Cart</a>
                        </li>
                    </ul>
                    <a href="logout.php" class="btn btn-outline-danger ms-3">Sign Out</a>
                </div>
            </div>
        </nav>
    </header>
    
    <div class="container mt-5">
        <h1>Your Products</h1>
        <table class="table table-hover text-center">
            <thead>
                <tr>
                    <th scope="col">Product ID</th>
                    <th scope="col">Product Name</th>
                    <th scope="col">Price</th>
                    <th scope="col">Category</th>
                    <th scope="col">Description</th>
                    <th scope="col">Image</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $result_products->fetch_assoc()) { ?>
                    <tr class="clickable-row" data-href="sellerpage.php?product_id=<?php echo $product['product_id']; ?>">
                        <td><?php echo $product["product_id"]; ?></td>
                        <td><?php echo $product["product_name"]; ?></td>
                        <td><?php echo $product["price"]; ?></td>
                        <td><?php echo $product["category"]; ?></td>
                        <td><?php echo $product["description"]; ?></td>
                        <td>
                            <img src="uploads/<?php echo $product['image']; ?>" alt="Product Image" style="max-width: 100px; max-height: 100px;">
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        
    </div>

    <script>
        // Add click event to make rows clickable
        document.querySelectorAll('.clickable-row').forEach(function(row) {
            row.addEventListener('click', function() {
                window.location = row.getAttribute('data-href');
            });
        });
    </script>
</body>
</html>
