<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

// Get the order_id from the URL
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : 0;

$sql = "SELECT o.order_id, o.total_price, o.order_date, o.status, oi.product_id, p.product_name, p.image, oi.quantity 
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN products p ON oi.product_id = p.product_id
        WHERE o.order_id = ?";


$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>Order not found.</p>";
    exit();
}

$order_details = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking | LALABUY</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Custom styles -->
    <link rel="stylesheet" href="assets/style.css">
</head>
<body style="background: linear-gradient(135deg, #6f7dff, #e0e0e0); font-family: 'Arial', sans-serif;">

<header class="top" style="max-height:100vh;">
    <nav class="navbar navbar-expand-xl navbar-light" style="background-color: #e3f2fd; margin-top:20px; border-radius:20px; padding:15px;">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php" style="color:blue; font-weight:bold;">LALABUY</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarLight" aria-controls="navbarLight" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse show" id="navbarLight">
                <ul class="navbar-nav me-auto mb-2 mb-xl-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="cart.php">My Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="me.php">Me</a>
                    </li>
                </ul>
                <a href="logout.php" class="btn btn-outline-danger ms-3">Sign Out</a>
            </div>
        </div>
    </nav>
</header>

<div class="container" style="margin-top: 50px; width:700px; padding:30px; border-radius:15px; background-color:#fff; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
    <h2 class="text-center">Order Tracking</h2>

    <div class="order-status">
    <h3 class="text-center">Order ID: #<?php echo $order_details['order_id']; ?></h3>
    <h4 class="text-center">Total Price: <?php echo number_format($order_details['total_price'], 2); ?></h4>
    <p class="text-center">Order Date: <?php echo date("F j, Y, g:i a", strtotime($order_details['order_date'])); ?></p>

    <!-- Order Address -->
    <h4 class="text-center mt-4">Shipping Address</h4>
    <p class="text-center">
        <?php echo htmlspecialchars($order_details['street']); ?><br>
        <?php echo htmlspecialchars($order_details['address']); ?><br>
        <?php echo !empty($order_details['address2']) ? htmlspecialchars($order_details['address2']) . '<br>' : ''; ?>
        <?php echo htmlspecialchars($order_details['city']); ?><br>
        <?php echo htmlspecialchars($order_details['zip_code']); ?>
    </p>

    <!-- Order Status -->
    <div class="progress" style="height: 30px;">
        <div class="progress-bar" role="progressbar" style="width: <?php echo getOrderProgress($order_details['status']); ?>%" aria-valuenow="<?php echo getOrderProgress($order_details['status']); ?>" aria-valuemin="0" aria-valuemax="100">
            <?php echo getOrderStatusText($order_details['status']); ?>
        </div>
    </div>

    <!-- Product Details -->
    <table class="table table-striped mt-4">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Image</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result->data_seek(0); // Reset pointer to the start of result
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                echo "<td><img src='uploads/" . htmlspecialchars($row['image']) . "' alt='Product Image' class='img-fluid' style='max-width: 100px;'></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

<style>
    body { display: grid; place-content: center; font-family: 'Arial', sans-serif; }
    .container { text-align: center; }
    table { text-align: center; width: 100%; }
    th { background-color: #f8f9fa; color: #495057; }
    td { padding: 10px; vertical-align: middle; }
    img { max-width: 100px; max-height: 100px; object-fit: cover; }
    .progress-bar {
        background-color: #28a745;
        font-weight: bold;
        color: white;
    }
    .order-status { background: #f8f9fa; padding: 20px; border-radius: 15px; box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.1); }
</style>

<?php
// Helper function to calculate order progress percentage
function getOrderProgress($status) {
    switch ($status) {
        case 'Shipped':
            return 100;
        case 'Processing':
            return 50;
        case 'Delivered':
            return 100;
        default:
            return 0;
    }
}

// Helper function to display order status text
function getOrderStatusText($status) {
    switch ($status) {
        case 'Shipped':
            return 'Shipped';
        case 'Processing':
            return 'Processing';
        case 'Delivered':
            return 'Delivered';
        default:
            return 'Pending';
    }
}
?>
</body>
</html>
