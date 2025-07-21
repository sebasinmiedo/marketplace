<?php
include 'check.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $productId = $_POST['id'];
        $data = [
            'name' => $_POST['name'],
            'price_old' => $_POST['price_old'],
            'price_current' => $_POST['price_current'],
            'description' => $_POST['description'],
            'rating' => $_POST['rating'],
            'quantity' => $_POST['quantity'],
            'category_id' => $_POST['category_id']
        ];

        $condition = "WHERE id = $productId";

        $result = $query->Update('products', $data, $condition);

        if ($result) {
            echo "<script>alert('Producto actualizado correctamente'); window.location.href = 'products.php';</script>";
        } else {
            echo "<script>alert('Error al actualizar producto'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('ID de producto no especificado'); window.history.back();</script>";
    }
}
?>