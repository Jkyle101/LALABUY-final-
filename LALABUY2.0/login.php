<?php
session_start();
    if (isset($_SESSION["user"])) {
    header("Location: index.php");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="./assets/style.css">
    <title>Login Form</title>
</head>
<body>
    <div class="container"  style="width:500px">
    <a href="index.php?"><img src="./includes/lalabuy.png" alt="LALABUY LOGO"></a>
        <?php
        if (isset($_POST["login"])) {
            $username = $_POST["username"];
            $password = $_POST["password"];
            
            // Make sure username and password are not empty
            if (empty($username) || empty($password)) {
                echo "<div class='alert alert-danger'>All fields are required</div>";
            } else {
                // Include your database connection file
                require_once "database.php"; 
        
                // Prepare the SQL statement to prevent SQL injection
                $sql = "SELECT * FROM users WHERE username = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $username); // 's' for string type
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc(); // Fetch the user data
        
                // Check if the user exists
                if ($user) {
                    // Verify the password
                    if (password_verify($password, $user["password"])) {
                        // Set session variables
                        $_SESSION["user"] = $user["user_id"];  // Store the user_id from the users table
        
                        // Redirect to index.php
                        header("Location: index.php");
                        exit();
                    } else {
                        echo "<div class='alert alert-danger'>Password Incorrect</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>User does not exist</div>";
                }
            }
        }
        ?>
      <form action="login.php" method="post">
        <div class="form-group" style="width:400px">
            <input type="username" placeholder="Enter Username:" name="username" class="form-control">
        </div>
        <div class="form-group" style="width:400px">
            <input type="password" placeholder="Enter Password:" name="password" class="form-control">
        </div>
        <div class="form-btn">
            <input type="submit" value="Login" name="login" class="btn btn-primary">
        </div>
      </form>
     <div><p>Not registered yet? <a href="register.php">Register Here</a></p></div>
    </div>
</body>
</html>