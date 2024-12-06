<?php
include "database.php";
$id = $_GET["id"];
$sql = "DELETE FROM `products` WHERE product_id = $id";
$result = mysqli_query($conn, $sql);

if ($result) {
  header("Location: me.php?msg=Data deleted successfully");
} else {
  echo "Failed: " . mysqli_error($conn);
}
