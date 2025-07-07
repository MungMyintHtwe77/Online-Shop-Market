<?php
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);

    // Get product details
    $product_query = "SELECT * FROM products WHERE id = $product_id";
    $product_result = mysqli_query($conn, $product_query);
    $product = mysqli_fetch_assoc($product_result);

    // Insert order
    $sql = "INSERT INTO orders (customer_name, phone, product_id, payment_method) 
            VALUES ('$name', '$phone', $product_id, '$payment_method')";

    if (mysqli_query($conn, $sql)) {
        // Send notification (you can implement Telegram bot here)
        $message = "New Order!\nName: $name\nPhone: $phone\nService: {$product['name']}\nPrice: {$product['price']} THB\nPayment: $payment_method";
        
        // Redirect with success message
        header("Location: ../index.php?order=success");
    } else {
        header("Location: ../index.php?order=error");
    }
}
?>
