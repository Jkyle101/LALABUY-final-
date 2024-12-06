<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

require_once "database.php";

// Initialize the search query variable
$search_query = isset($_GET['search_query']) ? $_GET['search_query'] : '';

// Build the SQL query based on the search input
if (!empty($search_query)) {
    $sql = "SELECT * FROM products WHERE (product_name LIKE ? OR description LIKE ? OR category LIKE ?) AND user_id != ?";
    $stmt = $conn->prepare($sql);
    $search_term = "%" . $search_query . "%"; // Prepare the search term with wildcards
    $stmt->bind_param("sssi", $search_term, $search_term, $search_term, $_SESSION['user']);
} else {
    $sql = "SELECT * FROM products WHERE user_id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user']);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>LALABUY | Home</title>
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

        /* Add zoom effect for product images */
        img {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            transition: transform 0.3s ease; /* Smooth transition for zoom effect */
        }

        img:hover {
            transform: scale(7);
            cursor: pointer; /* Zoom in the image */
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

        .link-dark {
            color: #333;
            text-decoration: none;
        }

        .link-dark:hover {
            color: #007bff;
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
                    <li class="nav-item"><a class="nav-link active" href="addproduct.php">Add Product</a></li>
                    <li class="nav-item"><a class="nav-link active" href="cart.php">Cart</a></li>
                    <li class="nav-item"><a class="nav-link active" href="orders.php">Orders</a></li>
                    <li class="nav-item"><a class="nav-link active" href="me.php">Me</a></li>


                </ul>
                <form class="d-flex" method="GET" action="index.php">
                    <input class="form-control me-2" type="search" name="search_query" placeholder="Search products, description, or category..." aria-label="Search" style="width: 400px;">
                    <button class="btn btn-outline-primary" type="submit">Search</button>
                </form>
                
                <a href="logout.php" class="btn btn-outline-danger ms-3">Sign Out</a>
            </div>
        </div>
    </nav>
</header>

<div class="container" style="margin-top: 50px;">
    <h1>Welcome to Dashboard</h1>
</div>

<div class="container2" style="margin-top: 20px;">
    <?php
    if (isset($_GET["msg"])) {
        $msg = $_GET["msg"];
        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
        ' . $msg . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
    ?>

    <table class="table table-hover text-center">
        <thead>
        <tr>
            <th scope="col">Product Name</th>
            <th scope="col">Price</th>
            <th scope="col">Image</th>
            <th scope="col">Description</th>
            <th scope="col">Category</th>
            <th scope="col"></th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                ?>
                <tr>
                    <td style="display:none;"><?php echo $row["product_id"]; ?></td>
                    <td style="display:none;"><?php echo $row["user_id"]; ?></td>
                    <td><?php echo $row["product_name"]; ?></td>
                    <td><?php echo $row["price"]; ?></td>
                    <td><img src="uploads/<?php echo $row['image']; ?>" alt="Product Image"></td>
                    <td><?php echo $row["description"]; ?></td>
                    <td><?php echo $row["category"]; ?></td>
                    <td>
                        <a href="addtocart.php?id=<?php echo $row['product_id']; ?>" class="link-dark">
                            <i class="fa-solid fa-cart-plus fs-5"></i> Add to Cart
                        </a>
                    </td>
                </tr>
                <?php
            }
        } else {
            echo '<tr><td colspan="5">No Products to Show</td></tr>';
        }
        ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</body>
</html>
