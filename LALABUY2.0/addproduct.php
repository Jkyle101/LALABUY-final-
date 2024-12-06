<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";  
$password = "";      
$dbname = "lalabuy";  

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the logged-in user's ID from session
$user_id = $_SESSION["user"]; 

// Check if the user exists
$user_check = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$user_check->bind_param("i", $user_id); 
$user_check->execute();
$result = $user_check->get_result();

// If no user is found with that user_id, prevent product insertion
if ($result->num_rows == 0) {
    die("User not found. Please log in again.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    // Database connection
    $servername = "localhost";
    $username = "root";  
    $password = "";      
    $dbname = "lalabuy";  

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get form data
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $user_id = $_SESSION["user"]; // Get the logged-in user's ID from session

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Get the image file info
        $image = $_FILES['image'];
        $image_name = $image['name'];
        $image_tmp_name = $image['tmp_name'];
        $image_size = $image['size'];
        $image_error = $image['error'];

        // Validate image file type (only allow jpg, jpeg, png)
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($image['type'], $allowed_types)) {
            echo'<script>alert("Invalid file type. Only JPG, JPEG, and PNG are allowed.")</script>';
            die;
        }

        // Create a unique name for the image
        $image_new_name = uniqid('', true) . '.' . pathinfo($image_name, PATHINFO_EXTENSION);
        $image_upload_dir = 'uploads/';
        
        // Move the uploaded image to the upload directory
        if (move_uploaded_file($image_tmp_name, $image_upload_dir . $image_new_name)) {
            // Prepare SQL query to insert data into the database
            $stmt = $conn->prepare("INSERT INTO products (product_name, price, image, category, description, user_id) VALUES (?,?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi", $product_name, $price, $image_new_name, $category, $description, $user_id);

            // Execute query and check success
            if ($stmt->execute()) {
                echo "<script>alert('Product added successfully.');</script>";
            } else {
                echo "Error: " . $stmt->error;
            }

            // Close statement
            $stmt->close();
        } else {
            echo "<div class='alert alert-danger'>Error uploading image.</div>";
        }
    } else {
        echo "Image is required.";
    }

    // Close the connection
    $conn->close(); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LALABUY | Add Product</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Custom styles -->
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
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

<div class="container" style="margin-top: 50px; width:700px;">
    <h2>Add New Product</h2>
    <form action="addproduct.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <input type="text" class="form-control" name="product_name" placeholder="Product Name" required>
        </div>
        <div class="form-group">
            <input type="number" class="form-control" name="price" placeholder="Price" required>
        </div>
        <div class="form-group">
            <label for="image" style="display:block;">Image:</label>
            <input type="file" class="form-control" name="image" accept=".jpg, .jpeg, .png" required>
        </div>
        <div class="form-group">
            <select class="form-select" name="category" required>
                <option value="" selected disabled>Category</option>
                <option value="Vehicles">Vehicles</option>
                <option value="Apparel">Apparel</option>
                <option value="Electronics">Electronics</option>
                <option value="Garden & Outdoor">Garden & Outdoor</option>
                <option value="Hobbies">Hobbies</option>
                <option value="Toys & Games">Toys & Games</option>
                <option value="Sporting Goods">Sporting Goods</option>
                <option value="Pet Supplies">Pet Supplies</option>
                <option value="Musical Instruments">Musical Instruments</option>
                <option value="Home Supplies">Home Supplies</option>
            </select>
        </div>
        <div class="form-group">
            <textarea class="form-control" name="description" placeholder="Product description" rows="5" required></textarea>
        </div>
        <div class="form-btn">
            <input type="submit" class="btn btn-primary" value="Add Product" name="submit">
        </div>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

<style>
    body { display: grid; place-content: center; font-family: Arial, sans-serif; }
        .container { text-align: center; }
        .container2 { max-width: 1500px; padding: 15px; box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px; margin-top: 20px; }
        table { text-align: center; width: 100%; }
        th { background-color: #f8f9fa; color: #495057; }
        td { padding: 10px; vertical-align: middle; }
        img { max-width: 100px; max-height: 100px; object-fit: cover; }
        .navbar { margin-top: 20px; background-color: #e3f2fd; border-radius: 20px; padding: 15px; }
        .navbar-brand { color: #007bff; font-weight: bold; }
        .navbar-nav .nav-link { color: #007bff; }
        .navbar-nav .nav-link.active { color: #0056b3; }
</style>
</body>
</html>
