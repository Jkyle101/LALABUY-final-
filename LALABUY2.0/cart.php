<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

$user_id = $_SESSION["user"];

// Query to fetch cart items
$sql = "SELECT p.product_name, p.price, p.image, p.description, c.product_id 
        FROM cart c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.user_id = ?";

// Prepare the query
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error); // Debugging error
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LALABUY | My Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { display: grid; place-content: center; font-family: Arial, sans-serif; }
        .container { text-align: center; }
        .container2 { max-width: 1500px; padding: 15px; box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px; margin-top: 20px; }
        table { text-align: center; width: 100%; }
        th { background-color: #f8f9fa; color: #495057; }
        td { padding: 10px; vertical-align: middle; }
        img { max-width: 100px; max-height: 100px; object-fit: cover; transition: transform 0.3s ease; }
        img:hover { transform: scale(7); cursor: pointer; }
        .navbar { margin-top: 20px; background-color: #e3f2fd; border-radius: 20px; padding: 15px; }
        .navbar-brand { color: #007bff; font-weight: bold; }
        .navbar-nav .nav-link { color: #007bff; }
        .navbar-nav .nav-link.active { color: #0056b3; }
        .chat-button { margin-top: 5px; }
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
                        <li class="nav-item"><a class="nav-link active" href="addproduct.php">Add Product</a></li>
                        <li class="nav-item"><a class="nav-link active" href="me.php">Me</a></li>
                    </ul>
                    <a href="logout.php" class="btn btn-outline-danger ms-3">Sign Out</a>
                </div>
            </div>
        </nav>
    </header>

    <div class="container">
        <h1>My Cart</h1>
    </div>

    <div class="container2">
        <?php
        if (isset($_GET["msg"])) {
            echo '<div class="alert alert-info">' . htmlspecialchars($_GET["msg"]) . '</div>';
        }
        
        if ($result && $result->num_rows > 0) {
            echo '<table class="table table-hover text-center">';
            echo '<thead><tr>
                    <th scope="col">Product Name</th>
                    <th scope="col">Price</th>
                    <th scope="col">Image</th>
                    <th scope="col">Description</th>
                    <th scope="col">Action</th>
                  </tr></thead><tbody>';
                  
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row["product_name"]) . '</td>';
                echo '<td>' . htmlspecialchars($row["price"]) . '</td>';
                echo '<td><img src="uploads/' . htmlspecialchars($row["image"]) . '" alt="Product Image"></td>';
                echo '<td>' . htmlspecialchars($row["description"]) . '</td>';
                echo '<td>
        <a href="removefromcart.php?id=' . htmlspecialchars($row["product_id"]) . '" class="btn btn-danger btn-sm">Remove</a>
        <a href="checkout.php?user_id=' . $user_id . '" class="btn btn-primary btn-sm chat-button">Check Out</a>
      </td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
        } else {
            echo '<p>Your cart is empty.</p>';
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
