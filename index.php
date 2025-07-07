<?php include 'config/db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>My Phone Repair Shop</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <header>
        <h1>My Phone Repair Shop</h1>
    </header>

    <main>
        <section class="products">
            <h2>Our Services</h2>
            <div class="product-list">
                <?php
                $sql = "SELECT * FROM products";
                $result = mysqli_query($conn, $sql);
                
                while($row = mysqli_fetch_assoc($result)) {
                    echo '<div class="product">';
                    echo '<img src="images/'.$row['image'].'" alt="'.$row['name'].'">';
                    echo '<h3>'.$row['name'].'</h3>';
                    echo '<p>Price: '.$row['price'].' THB</p>';
                    echo '<button onclick="orderProduct('.$row['id'].')">Order Now</button>';
                    echo '</div>';
                }
                ?>
            </div>
        </section>
    </main>

    <script src="script.js"></script>
</body>
</html>
