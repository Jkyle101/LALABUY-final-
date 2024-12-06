<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

// Fetch the product ID from the query string
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : 0;

if ($product_id <= 0) {
    echo "Invalid product ID.";
    exit();
}

// Query to fetch the product details
$sql_product = "SELECT * FROM products WHERE product_id = ? AND user_id = ?";
$stmt_product = $conn->prepare($sql_product);
$stmt_product->bind_param("ii", $product_id, $_SESSION["user"]);
$stmt_product->execute();
$result_product = $stmt_product->get_result();

if ($result_product->num_rows == 0) {
    echo "Product not found.";
    exit();
}

$product = $result_product->fetch_assoc();

// Query to fetch orders for this product
$sql_orders = "
    SELECT o.order_id, o.total_price, o.order_date, o.status, o.tracking_number, oi.product_id
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    WHERE oi.product_id = ?";
$stmt_orders = $conn->prepare($sql_orders);
$stmt_orders->bind_param("i", $product_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();
?>

<!DOCTYPE html>
<html lang="en">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <title>Seller Page | LALABUY</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <style>
        .container {
            max-width: 900px;
            margin-top: 30px;
        }
        .product-image {
            max-width: 150px;
            max-height: 150px;
            object-fit: cover;
        }
        .order-table {
            box-shadow: rgba(0, 0, 0, 0.1) 0px 4px 10px;
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        .btn-update {
            margin-top: 10px;
        }
        .tracking-input {
            width: 100%;
        }
    </style>
</head>
<body>
    <header class="navbar navbar-expand-lg navbar-light" style="background-color: #e3f2fd;">
        <div class="container-fluid">
            <a class="navbar-brand" href="me.php" style="color:blue; font-weight:bold;">LALABUY</a>
            <a href="logout.php" class="btn btn-outline-danger ms-3">Sign Out</a>
        </div>
    </header>

    <div class="container">
        <h1 class="text-center mb-4">Product Details</h1>
        
        <!-- Product Details -->
        <div class="row mb-5">
            <div class="col-md-4">
                <img src="uploads/<?php echo $product['image']; ?>" alt="Product Image" class="product-image">
            </div>
            <div class="col-md-8">
                <h3><?php echo $product['product_name']; ?></h3>
                <p><strong>Category:</strong> <?php echo $product['category']; ?></p>
                <p><strong>Description:</strong> <?php echo $product['description']; ?></p>
                <p><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="order-table">
            <h2>Orders for this Product</h2>
            <?php if ($result_orders->num_rows > 0) { ?>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Total Price</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Tracking Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
    <?php while ($order = $result_orders->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $order["order_id"]; ?></td>
            <td>$<?php echo number_format($order["total_price"], 2); ?></td>
            <td><?php echo date("F j, Y", strtotime($order["order_date"])); ?></td>
            <td>
                <span class="badge 
                <?php echo ($order["status"] === 'Shipped') ? 'bg-warning' : (($order["status"] === 'Delivered') ? 'bg-success' : 'bg-secondary'); ?>">
                    <?php echo $order["status"]; ?>
                </span>
            </td>
            <td><?php echo $order["tracking_number"] ?: "N/A"; ?></td>
            <td>
                <!-- Shipping Update Form -->
                <form method="POST" action="update_shipping.php" class="d-inline">
                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>"> <!-- Pass product_id -->
                    <input type="text" placeholder="Enter Tracking Number" name="tracking_number" class="form-control mb-2" value="<?php echo $order["tracking_number"]; ?>" readonly>
                    <select name="status" class="form-control mb-2" required>
                        <option value="Shipped" <?php echo ($order['status'] == 'Shipped') ? 'selected' : ''; ?>>Mark as Shipped</option>
                        <option value="Delivered" <?php echo ($order['status'] == 'Delivered') ? 'selected' : ''; ?>>Mark as Delivered</option>
                    </select>
                    <button type="submit" name="update_order" class="btn btn-primary btn-sm btn-update">Update Status</button>
                    <button type="submit" name="update_tracking" class="btn btn-success btn-sm btn-update" disabled>Update Tracking Number</button>
                </form>
                
                <?php if ($order['status'] == 'Delivered') { ?>
                    <!-- Trash Icon to Delete Order -->
                    <form method="POST" action="delete_order.php" class="d-inline">
                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        <button type="submit" name="delete_order" class="btn btn-danger btn-sm ms-2">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </form>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
</tbody>

                </table>
            <?php } else { ?>
                <p>No orders found for this product.</p>
            <?php } ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to toggle the tracking input field between read-only and editable
        function toggleTrackingInput(orderId) {
            var trackingInput = document.getElementById('tracking_input_' + orderId);
            var unlockButton = document.querySelector('button[onclick="toggleTrackingInput(' + orderId + ')"]');
            var updateTrackingButton = document.getElementById('update_tracking_' + orderId);

            // Toggle the read-only state of the input field
            if (trackingInput.readOnly) {
                trackingInput.readOnly = false;
                updateTrackingButton.disabled = false;  // Enable the update button
                unlockButton.textContent = 'Lock';  // Change button text
            } else {
                trackingInput.readOnly = true;
                updateTrackingButton.disabled = true;  // Disable the update button
                unlockButton.textContent = 'Unlock';  // Reset button text
            }
        }
    </script>
</body>
</html>
