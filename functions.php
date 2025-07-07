<?php
// Database connection function
function getDBConnection() {
    static $conn;
    if (!isset($conn)) {
        require_once 'config/db.php'; // db.php ဖိုင်ကိုခေါ်သုံးခြင်း
    }
    return $conn;
}

// Product များကိုရယူခြင်း
function getAllProducts() {
    $conn = getDBConnection();
    $sql = "SELECT * FROM products";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// တစ်ခုတည်းသော Product ကိုရယူခြင်း
function getProductById($id) {
    $conn = getDBConnection();
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Order ထည့်သွင်းခြင်း
function createOrder($data) {
    $conn = getDBConnection();
    $sql = "INSERT INTO orders (customer_name, phone, product_id, payment_method) 
            VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssis", 
        $data['name'],
        $data['phone'],
        $data['product_id'],
        $data['payment_method']
    );
    return mysqli_stmt_execute($stmt);
}

// Telegram သို့ Notification ပို့ခြင်း
function sendTelegramNotification($message) {
    $telegram_token = 'YOUR_BOT_TOKEN';
    $telegram_chat_id = '@muyaung';
    $url = "https://api.telegram.org/bot$telegram_token/sendMessage?chat_id=$telegram_chat_id&text=".urlencode($message);
    return file_get_contents($url);
}

// KBZ Pay Payment Link ဖန်တီးခြင်း
function generateKBZPayLink($amount, $reference) {
    // KBZ Pay API integration အတွက် နမူနာ code
    return "kbzpay://payment?amount=$amount&reference=$reference";
}

// Form Input များကို သန့်စင်ခြင်း
function sanitizeInput($input) {
    $conn = getDBConnection();
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($input)));
}
?>
