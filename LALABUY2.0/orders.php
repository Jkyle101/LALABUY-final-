<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

// Query to fetch the user's orders
$sql = "SELECT o.order_id, o.total_price, o.order_date, o.status 
        FROM orders o
        WHERE o.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user']);
$stmt->execute();
$result = $stmt->get_result();

// Check for success or error messages in the URL
$delete_success = isset($_GET['delete_success']) ? $_GET['delete_success'] : null;
$delete_error = isset($_GET['delete_error']) ? $_GET['delete_error'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | LALABUY</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 900px;
            margin-top: 30px;
        }

        .order-table {
            box-shadow: rgba(0, 0, 0, 0.1) 0px 4px 10px;
            background-color: white;
            border-radius: 8px;
            padding: 20px;
        }

        .navbar {
            margin-top: 20px;
            background-color: #e3f2fd;
            border-radius: 20px;
            padding: 15px;
        }

        .navbar-brand {
            color: #007bff;
            font-weight: bold;
        }

        .navbar-nav .nav-link {
            color: #007bff;
        }

        .navbar-nav .nav-link.active {
            color: #0056b3;
        }
    </style>
</head>
<body>

<header>
    <nav class="navbar navbar-expand-xl navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">LALABUY</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarLight" aria-controls="navbarLight" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse show" id="navbarLight">
                <ul class="navbar-nav me-auto mb-2 mb-xl-0">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="cart.php">My Cart</a></li>
                    <li class="nav-item"><a class="nav-link active" href="me.php">Me</a></li>
                </ul>
                <a href="logout.php" class="btn btn-outline-danger ms-3">Sign Out</a>
            </div>
        </div>
    </nav>
</header>

<div class="container">
    <h1 class="text-center">My Orders</h1>

    <!-- Success or Error message -->
    <?php if ($delete_success): ?>
        <div class="alert alert-success">Order has been successfully deleted.</div>
    <?php elseif ($delete_error): ?>
        <div class="alert alert-danger">There was an error deleting the order. Please try again.</div>
    <?php endif; ?>

    <div class="order-table">
    <?php if ($result->num_rows > 0) { ?>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Total Price</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $result->fetch_assoc()) { ?>
                    <tr>
                        <td>#<?php echo $order['order_id']; ?></td>
                        <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                        <td><?php echo date("F j, Y", strtotime($order['order_date'])); ?></td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                        <td>
                            <a href="order_tracking.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-info btn-sm">Track Order</a>

                            <?php if ($order['status'] === 'Pending') { ?>
                                <a href="cancel_order.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-danger btn-sm">Cancel Order</a>
                            <?php } elseif ($order['status'] === 'Delivered') { ?>
                                <form method="POST" action="delete_order.php" class="d-inline">
    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
    <input type="hidden" name="redirect_url" value="orders.php">
    <button type="submit" name="delete_order" class="btn btn-success btn-sm">Order Received</button>
</form>

                            <?php } ?>

                            <?php if ($order['status'] === 'Cancelled') { ?>
                                <a href="delete_order.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-danger btn-sm">Trash</a>
                            <?php } ?>

                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p class="text-center">You have not placed any orders yet.</p>
    <?php } ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
