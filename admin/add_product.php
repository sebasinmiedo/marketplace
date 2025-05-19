<?php
include 'check.php';

if (isset($_POST['submit'])) {
    //tomar en cuenta la direccion
    $uploadDir = 'C:/xampp2/htdocs/imarket/src/images/products/';

    // Validar y mover la imagen
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newFileName = md5(uniqid(rand(), true)) . '.' . $ext;
        $destination = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {

            // Insertar producto
            $data = [
                "name" => $_POST['name'],
                "price_old" => $_POST['price_old'],
                "price_current" => $_POST['price_current'],
                "description" => $_POST['description'],
                "rating" => $_POST['rating'],
                "quantity" => $_POST['quantity'],
                "added_to_site" => $_POST['added_to_site'],
                "category_id" => $_POST['category_id']
            ];

        $result = $query->insert('products', $data);

            if ($result) {
                $productId = $query->getLastInsertId(); // Asegúrate de tener esta función en tu clase

                // Insertar imagen
                $query->insert('product_images', [
                    "product_id" => $productId,
                    "image_url" => $newFileName
                ]);

                echo "<script>alert('Product added successfully'); window.top.location.reload();</script>";
                exit;
            } else {
                echo "<script>alert('Error adding product');</script>";
            }
        } else {
            echo "<script>alert('Error uploading image');</script>";
        }
    } else {
        echo "<script>alert('Image upload failed');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <link rel="stylesheet" href="../src/css/bootstrap.min.css"> <!-- Asegúrate de que existe -->
</head>
<body>
    <div class="container p-4">
        <h3>Add New Product</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Product Image</label>
                <input type="file" name="image" class="form-control-file" accept=".jpg,.jpeg,.png" required>
            </div>

            <!-- Campo para seleccionar categoría -->
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" class="form-control" required>
                    <?php
                    $categories = $query->select('categories', 'id, category_name');
                    foreach ($categories as $cat) {
                        echo '<option value="' . $cat['id'] . '">' . htmlspecialchars($cat['category_name']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Product Name</label>
                <input name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Old Price</label>
                <input type="text" name="price_old" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Current Price</label>
                <input type="text" name="price_current" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label>Rating</label>
                <input type="number" step="0.1" name="rating" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="quantity" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Added to Site</label>
                <input type="date" name="added_to_site" class="form-control" required>
            </div>
            <button name="submit" class="btn btn-success">Save Product</button>
        </form>
    </div>
</body>
</html>
