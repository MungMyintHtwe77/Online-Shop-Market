<?php
require_once 'config/db.php';
require_once 'functions.php';

// Admin Login စစ်ဆေးခြင်း
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}

// CRUD Operations
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'add':
        handleAddProduct();
        break;
    case 'edit':
        handleEditProduct();
        break;
    case 'delete':
        handleDeleteProduct();
        break;
    default:
        displayProductsList();
}

// Functions for each action
function displayProductsList() {
    global $conn;
    $products = getAllProducts();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Manage Products</title>
        <link rel="stylesheet" href="admin_style.css">
    </head>
    <body>
        <div class="admin-container">
            <h1>Manage Phone Repair Services</h1>
            <a href="products.php?action=add" class="btn-add">Add New Service</a>
            
            <table class="product-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Service Name</th>
                        <th>Price (THB)</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= $product['id'] ?></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= number_format($product['price'], 2) ?></td>
                        <td>
                            <?php if ($product['image']): ?>
                            <img src="../images/<?= $product['image'] ?>" width="50">
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="products.php?action=edit&id=<?= $product['id'] ?>" class="btn-edit">Edit</a>
                            <a href="products.php?action=delete&id=<?= $product['id'] ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </body>
    </html>
    <?php
}

function handleAddProduct() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = sanitizeInput($_POST['name']);
        $price = (float)$_POST['price'];
        $description = sanitizeInput($_POST['description']);
        
        // Image Upload Handling
        $imageName = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageName = uploadProductImage($_FILES['image']);
        }
        
        $conn = getDBConnection();
        $sql = "INSERT INTO products (name, price, description, image) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sdss", $name, $price, $description, $imageName);
        
        if (mysqli_stmt_execute($stmt)) {
            header('Location: products.php?message=Product added successfully');
            exit();
        } else {
            $error = "Error adding product: " . mysqli_error($conn);
        }
    }
    
    displayProductForm();
}

function handleEditProduct() {
    $id = (int)$_GET['id'];
    $product = getProductById($id);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = sanitizeInput($_POST['name']);
        $price = (float)$_POST['price'];
        $description = sanitizeInput($_POST['description']);
        
        // Keep existing image if not changed
        $imageName = $product['image'];
        
        // Handle new image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Delete old image if exists
            if ($imageName && file_exists("../images/$imageName")) {
                unlink("../images/$imageName");
            }
            $imageName = uploadProductImage($_FILES['image']);
        }
        
        $conn = getDBConnection();
        $sql = "UPDATE products SET name=?, price=?, description=?, image=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sdssi", $name, $price, $description, $imageName, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            header('Location: products.php?message=Product updated successfully');
            exit();
        } else {
            $error = "Error updating product: " . mysqli_error($conn);
        }
    }
    
    displayProductForm($product);
}

function handleDeleteProduct() {
    $id = (int)$_GET['id'];
    $product = getProductById($id);
    
    // Delete product image if exists
    if ($product['image'] && file_exists("../images/".$product['image'])) {
        unlink("../images/".$product['image']);
    }
    
    $conn = getDBConnection();
    $sql = "DELETE FROM products WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        header('Location: products.php?message=Product deleted successfully');
        exit();
    } else {
        header('Location: products.php?error=Error deleting product');
        exit();
    }
}

function displayProductForm($product = null) {
    $isEdit = ($product !== null);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title><?= $isEdit ? 'Edit' : 'Add' ?> Product</title>
        <link rel="stylesheet" href="admin_style.css">
    </head>
    <body>
        <div class="admin-container">
            <h1><?= $isEdit ? 'Edit' : 'Add' ?> Phone Repair Service</h1>
            <a href="products.php" class="btn-back">Back to List</a>
            
            <form method="POST" enctype="multipart/form-data" class="product-form">
                <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Service Name:</label>
                    <input type="text" id="name" name="name" 
                           value="<?= $isEdit ? htmlspecialchars($product['name']) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="price">Price (THB):</label>
                    <input type="number" id="price" name="price" step="0.01" min="0"
                           value="<?= $isEdit ? $product['price'] : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description"><?= $isEdit ? htmlspecialchars($product['description']) : '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Service Image:</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <?php if ($isEdit && $product['image']): ?>
                    <div class="current-image">
                        <p>Current Image:</p>
                        <img src="../images/<?= $product['image'] ?>" width="100">
                    </div>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn-submit"><?= $isEdit ? 'Update' : 'Add' ?> Service</button>
            </form>
        </div>
    </body>
    </html>
    <?php
}

function uploadProductImage($file) {
    $targetDir = "../images/";
    $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $newFileName = uniqid() . '.' . $imageFileType;
    $targetFile = $targetDir . $newFileName;
    
    // Check if image file is a actual image
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        throw new Exception("File is not an image.");
    }
    
    // Check file size (max 2MB)
    if ($file['size'] > 2000000) {
        throw new Exception("Sorry, your file is too large (max 2MB).");
    }
    
    // Allow certain file formats
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowedTypes)) {
        throw new Exception("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return $newFileName;
    } else {
        throw new Exception("Sorry, there was an error uploading your file.");
    }
}
?>
