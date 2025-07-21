<?php
// Asegúrate de tener acceso a la base de datos
include 'check.php'; // O tu archivo de conexión

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['productId'])) {
        $productId = intval($_POST['productId']);

        // Ejecutar la eliminación con el método delete()
        $resultado = $query->delete("products", "WHERE id = $productId");

        if ($resultado) {
            // Redirigir o dar mensaje de éxito
            echo "<script>alert('Producto actualizado correctamente'); window.location.href = 'products.php';</script>";
        } else {
            header("Location: products.php?error=No se pudo eliminar el producto");
            exit;
        }
    } else {
        echo "ID de producto no recibido.";
    }
} else {
    echo "Acceso inválido.";
}
?>
