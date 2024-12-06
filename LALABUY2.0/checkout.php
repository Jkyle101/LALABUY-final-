<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

$user_id = $_SESSION["user"];

// Query to fetch the user's cart items
$sql = "SELECT p.product_name, p.price, p.image, c.product_id 
        FROM cart c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>Your cart is empty. Please add products before proceeding to checkout.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <title>LALABUY | Checkout</title>
    <style>
        body {
            display: grid;
            place-content: center;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            text-align: center;
        }

        .container2 {
            max-width: 1500px;
            padding: 15px;
            box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
            background-color: white;
        }

        table {
            text-align: center;
            width: 100%;
        }

        th {
            background-color: #f8f9fa;
            color: #495057;
        }

        td {
            padding: 10px;
            vertical-align: middle;
        }

        img {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        img:hover {
            transform: scale(7);
            cursor: pointer;
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

        .btn-outline-primary {
            color: #007bff;
            border-color: #007bff;
        }

        .btn-outline-primary:hover {
            background-color: #007bff;
            color: white;
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
                    <li class="nav-item"><a class="nav-link active" href="cart.php">My Cart</a></li>
                    <li class="nav-item"><a class="nav-link active" href="me.php">Me</a></li>
                </ul>
                <a href="logout.php" class="btn btn-outline-danger ms-3">Sign Out</a>
            </div>
        </div>
    </nav>
</header>

<div class="container" style="margin-top: 50px;">
    <h1>Checkout</h1>
</div>

<div class="container2" style="margin-top: 20px;">
    <table class="table table-hover text-center">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Price</th>
                <th>Image</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total = 0;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row["product_name"]) . "</td>";
                echo "<td>$" . htmlspecialchars($row["price"]) . "</td>";
                echo "<td><img src='uploads/" . htmlspecialchars($row["image"]) . "' alt='Product Image'></td>";
                echo "</tr>";
                $total += $row["price"];
            }
            ?>
        </tbody>
    </table>

    <h3>Total: $<?php echo number_format($total, 2); ?></h3>

    <form action="process_checkout.php" method="POST" class="mt-4">
        <h4>Shipping Information</h4>
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" name="address" id="address" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="address2" class="form-label">Address 2 (Optional)</label>
            <input type="text" name="address2" id="address2" class="form-control">
        </div>
        <div class="mb-3">
            <label for="zip_code" class="form-label">Zip Code</label>
            <input type="text" name="zip_code" id="zip_code" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="city" class="form-label">City</label>
            <input type="text" name="city" id="city" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="street" class="form-label">Street</label>
            <input type="text" name="street" id="street" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-outline-primary">Confirm Checkout</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
