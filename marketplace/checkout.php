<?php
include 'check.php';

$user_id = $_SESSION['id'];

$user = $query->executeQuery("SELECT * FROM accounts WHERE id = $user_id")->fetch_assoc();
$cart = $query->executeQuery("SELECT * FROM cart WHERE user_id = $user_id");

$price_old_Sum = 0;
$price_current_Sum = 0;

// Process the purchase if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'cancel') {
            header('Content-Type: application/json');
            
            try {
                // Si la orden ya fue creada, la cancelamos
                if (isset($_SESSION['current_order_id'])) {
                    $order_id = $_SESSION['current_order_id'];
                    $order = $query->select('orders', '*', "WHERE id = $order_id AND user_id = {$_SESSION['id']}")[0] ?? null;
                    
                    if (!$order) {
                        throw new Exception('Orden no encontrada');
                    }
                    
                    if ($order['status'] !== 'pending') {
                        throw new Exception('Solo se pueden cancelar órdenes pendientes');
                    }

                    $query->conn->begin_transaction();
                    
                    try {
                        // Actualizar el estado de la orden
                        $update_result = $query->executeQuery("UPDATE orders SET status = 'cancelled' WHERE id = $order_id AND user_id = {$_SESSION['id']}");
                        
                        if ($query->conn->affected_rows === 0) {
                            throw new Exception('No se pudo actualizar la orden');
                        }
                        
                        // Devolver el stock a los productos
                        $order_items = $query->select('order_items', '*', "WHERE order_id = $order_id");
                        foreach ($order_items as $item) {
                            $product = $query->select('products', '*', "WHERE id = {$item['product_id']}")[0];
                            if (!$product) {
                                throw new Exception('Producto no encontrado');
                            }
                            $new_quantity = $product['quantity'] + $item['quantity'];
                            $update_stock = $query->executeQuery("UPDATE products SET quantity = $new_quantity WHERE id = {$item['product_id']}");
                            if ($query->conn->affected_rows === 0) {
                                throw new Exception('No se pudo actualizar el stock del producto');
                            }
                        }
                        
                        $query->conn->commit();
                        unset($_SESSION['current_order_id']);
                        
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Orden cancelada exitosamente',
                            'redirect' => 'my-orders.php'
                        ]);
                    } catch (Exception $e) {
                        $query->conn->rollback();
                        throw new Exception('Error al procesar la cancelación: ' . $e->getMessage());
                    }
                } else {
                    // Si la orden no fue creada, solo limpiamos la sesión
                    unset($_SESSION['current_order_id']);
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Proceso de compra cancelado',
                        'redirect' => 'shoping-cart.php'
                    ]);
                }
            } catch (Exception $e) {
                error_log('Error en cancelación de orden: ' . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            exit;
        }
    }

    // Obtener información de envío
    $shipping_info = [
        'address' => $_POST['address'] ?? '',
        'city' => $_POST['city'] ?? '',
        'country' => $_POST['country'] ?? '',
        'postal_code' => $_POST['postal_code'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'payment_method' => $_POST['payment_method'] ?? ''
    ];

    // Obtener items del carrito
    $cart_items = $query->getCartItems($_SESSION['id']);

    try {
        // Crear la orden
        $order_id = $query->createOrder($_SESSION['id'], $cart_items, $shipping_info, $shipping_info['payment_method']);
        $_SESSION['current_order_id'] = $order_id;

        // Limpiar el carrito
        $query->executeQuery("DELETE FROM cart WHERE user_id = {$_SESSION['id']}");

        echo json_encode([
            'success' => true,
            'message' => '¡Orden creada exitosamente!',
            'order_id' => $order_id,
            'redirect' => 'my-orders.php'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="src/css/slicknav.min.css" type="text/css">
    <link rel="stylesheet" href="src/css/style.css" type="text/css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .container {
            width: 90%;
            overflow-x: auto;
            margin: 40px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
            font-size: 2.5em;
            font-weight: bold;
        }

        h3 {
            color: #333;
            font-size: 1.5em;
            margin-bottom: 15px;
            text-decoration: underline;
        }

        .user-information,
        .cart-summary {
            margin-bottom: 40px;
        }

        .user-information ul {
            list-style-type: none;
            padding: 0;
            font-size: 1.1em;
            color: #555;
        }

        .user-information li {
            margin-bottom: 12px;
        }

        .user-information li strong {
            color: #7fad39;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        th,
        td {
            padding: 15px 20px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 1.1em;
        }

        th {
            background-color: #f1f1f1;
            color: #7fad39;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #e0f7fa;
            transition: background-color 0.3s ease;
        }

        .total {
            font-size: 1.4em;
            font-weight: bold;
            color: #333;
            margin-top: 25px;
            text-align: right;
        }

        .total p {
            margin: 15px 0;
        }

        .total span {
            color: rgb(255, 51, 0);
        }

        .price del {
            color: rgb(255, 0, 0);
            font-size: 14px;
        }

        .price {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price span {
            color: #7fad39;
            font-weight: bold;
        }

        .cart-summary {
            border-top: 2px solid #f1f1f1;
            padding-top: 20px;
        }

        del {
            font-weight: bold;
        }

        .shipping-form {
            margin-top: 40px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }

        .shipping-form h3 {
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
        }

        .purchase-button {
            background-color: #7fad39;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 4px;
            font-size: 1.2em;
            cursor: pointer;
            margin-top: 20px;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        .purchase-button:hover {
            background-color: #689f2c;
        }

        @media (max-width: 768px) {
            .container {
                width: 95%;
                padding: 20px;
            }

            h2 {
                font-size: 2em;
            }

            h3 {
                font-size: 1.2em;
            }

            table th,
            table td {
                font-size: 1em;
                padding: 10px;
            }

            .total p {
                font-size: 1.2em;
            }

            .user-information ul {
                font-size: 1em;
            }

            .price span {
                font-size: 1em;
            }

            .price del {
                font-size: 12px;
            }

            .shipping-form {
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            .container {
                width: 100%;
                padding: 10px;
            }

            h2 {
                font-size: 1.8em;
            }

            h3 {
                font-size: 1.1em;
            }

            .user-information ul {
                font-size: 0.9em;
            }

            table th,
            table td {
                font-size: 0.9em;
                padding: 8px;
            }

            .price span {
                font-size: 0.9em;
            }

            .total p {
                font-size: 1em;
            }

            .cart-summary {
                padding-top: 15px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Checkout Summary</h2>

        <div class="user-information">
            <h3>User Information</h3>
            <ul>
                <li><strong>Name:</strong> <?= htmlspecialchars($user['name']); ?></li>
                <li><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></li>
                <li><strong>Phone Number:</strong> <?= htmlspecialchars($user['number']); ?></li>
            </ul>
        </div>

        <div class="cart-summary">
            <h3>Cart Items</h3>
            <table>
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($cart as $index => $item) {
                        $product = $query->executeQuery("SELECT * FROM products WHERE id = {$item['product_id']}")->fetch_assoc();
                        $price_old_Sum += $product['price_old'] * $item['number_of_products'];
                        $price_current_Sum += $product['price_current'] * $item['number_of_products'];
                    ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td class="price">
                                <del>$<?= number_format($product['price_old'], 2) ?></del>
                                <span>$<?= number_format($product['price_current'], 2) ?></span>
                            </td>
                            <td><?= $item['number_of_products'] ?></td>
                            <td>$<?= number_format($product['price_current'] * $item['number_of_products'], 2) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <div class="total">
                <p>Total Price: <del>$<?= number_format($price_old_Sum, 2) ?></del> <span>$<?= number_format($price_current_Sum, 2) ?></span></p>
            </div>
        </div>

        <form method="POST" class="shipping-form" id="checkout-form">
            <h3>Shipping Information</h3>
            
            <div class="form-group">
                <label for="address">Shipping Address</label>
                <input type="text" id="address" name="address" required>
            </div>

            <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city" required>
            </div>

            <div class="form-group">
                <label for="country">Country</label>
                <input type="text" id="country" name="country" required>
            </div>

            <div class="form-group">
                <label for="postal_code">Postal Code</label>
                <input type="text" id="postal_code" name="postal_code" required>
            </div>

            <div class="form-group">
                <label for="phone">Contact Phone</label>
                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['number']) ?>" required>
            </div>

            <div class="form-group">
                <label for="payment_method">Payment Method</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="credit_card">Credit Card</option>
                    <option value="debit_card">Debit Card</option>
                    <option value="paypal">PayPal</option>
                    <option value="cash">Cash on Delivery</option>
                </select>
            </div>

            <div class="form-group">
                <button type="button" class="btn btn-danger" onclick="cancelOrder()">Cancelar Orden</button>
                <button type="submit" class="btn btn-primary">Confirmar Compra</button>
            </div>
        </form>
    </div>
    <!-- jQuery (obligatorio para que funcione el AJAX con $) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function() {
        $('#checkout-form').on('submit', function(e) {
            e.preventDefault();
            
            // Mostrar indicador de carga
            Swal.fire({
                title: 'Procesando...',
                text: 'Por favor espere mientras procesamos su orden',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: 'checkout.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.message || 'Ocurrió un error al procesar su orden',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Ocurrió un error al procesar su solicitud. Por favor, intente nuevamente.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        });
    });

    function cancelOrder() {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas cancelar este proceso de compra?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No, continuar'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Cancelado',
                    text: 'El proceso de compra ha sido cancelado',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(function() {
                    // Redireccionar a la página principal
                    window.location.href = 'shoping-cart.php';
                });
            }
        });
    }
    </script>

</body>

</html>