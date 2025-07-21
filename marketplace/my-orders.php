<?php
include 'check.php';

$user_id = $_SESSION['id'];
$orders = $query->select('orders', '*', "WHERE user_id = $user_id ORDER BY order_date DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Ogani Template">
    <meta name="keywords" content="Ogani, unica, creative, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Mis Órdenes</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;900&display=swap" rel="stylesheet">

    <!-- Css Styles -->
    <link rel="stylesheet" href="src/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="src/css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="src/css/elegant-icons.css" type="text/css">
    <link rel="stylesheet" href="src/css/nice-select.css" type="text/css">
    <link rel="stylesheet" href="src/css/jquery-ui.min.css" type="text/css">
    <link rel="stylesheet" href="src/css/owl.carousel.min.css" type="text/css">
    <link rel="stylesheet" href="src/css/slicknav.min.css" type="text/css">
    <link rel="stylesheet" href="src/css/style.css" type="text/css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .order-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .order-header {
            background-color: #f5f5f5;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }

        .order-id {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .order-date {
            color: #666;
            font-size: 14px;
        }

        .order-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-shipped {
            background-color: #d4edda;
            color: #155724;
        }

        .status-delivered {
            background-color: #7fad39;
            color: white;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .order-body {
            padding: 20px;
        }

        .order-items {
            margin-bottom: 20px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .item-name {
            flex: 2;
            font-weight: 500;
        }

        .item-details {
            flex: 1;
            text-align: right;
        }

        .shipping-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }

        .shipping-info h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .total-amount {
            text-align: right;
            font-size: 18px;
            font-weight: 600;
            color: #7fad39;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #eee;
        }

        .empty-orders {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }

        .empty-orders i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 15px;
        }

        .empty-orders .primary-btn {
            margin-top: 20px;
            display: inline-block;
        }

        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                text-align: center;
            }

            .order-status {
                margin-top: 10px;
            }

            .item-row {
                flex-direction: column;
                text-align: center;
            }

            .item-details {
                margin-top: 10px;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <!-- Header Section Begin -->
    <?php include 'includes/header.php'; ?>
    <!-- Header Section End -->

    <!-- Breadcrumb Section Begin -->
    <section class="breadcrumb-section set-bg" data-setbg="src/images/breadcrumb.jpg">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">
                        <h2>Mis Órdenes</h2>
                        <div class="breadcrumb__option">
                            <a href="./">Inicio</a>
                            <span>Mis Órdenes</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Orders Section Begin -->
    <section class="shoping-cart spad">
        <div class="container">
            <?php if (empty($orders)): ?>
                <div class="empty-orders">
                    <i class="fa fa-shopping-bag"></i>
                    <h3>No hay órdenes</h3>
                    <p>¡Aún no has realizado ninguna compra! ¡Comienza a comprar para ver tus órdenes aquí!</p>
                    <a href="shop.php" class="primary-btn">COMENZAR A COMPRAR</a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): 
                    $order_date = strtotime($order['order_date']);
                    $current_time = time();
                    $hours_diff = ($current_time - $order_date) / 3600;
                    $can_cancel = $order['status'] === 'pending' && $hours_diff <= 24;
                ?>
                    <div class="order-card">
                        <div class="order-header d-flex justify-content-between align-items-center">
                            <div class="order-id">Orden #<?php echo $order['id']; ?></div>
                            <div class="order-date"><?php echo date('d/m/Y H:i', $order_date); ?></div>
                            <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </div>
                        </div>
                        <div class="order-body">
                            <div class="shipping-info">
                                <h4><i class="fa fa-shipping-fast"></i> Información de Envío</h4>
                                <p><?php echo htmlspecialchars($order['shipping_address']); ?></p>
                                <p><?php echo htmlspecialchars($order['shipping_city']); ?>, <?php echo htmlspecialchars($order['shipping_country']); ?> <?php echo htmlspecialchars($order['shipping_postal_code']); ?></p>
                                <p><i class="fa fa-phone"></i> <?php echo htmlspecialchars($order['phone_number']); ?></p>
                                <p><i class="fa fa-credit-card"></i> Método de Pago: <?php echo ucfirst($order['payment_method']); ?></p>
                            </div>

                            <div class="order-items">
                                <?php 
                                $items = $query->select(
                                    'order_items oi',
                                    'oi.*, p.name as product_name',
                                    "JOIN products p ON p.id = oi.product_id WHERE oi.order_id = {$order['id']}"
                                );
                                foreach ($items as $item): 
                                ?>
                                    <div class="item-row">
                                        <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        <div class="item-details">
                                            <span>Cantidad: <?php echo $item['quantity']; ?></span>
                                            <span class="price">$<?php echo number_format($item['price_at_time'], 2); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="total-amount">
                                Total: $<?php echo number_format($order['total_amount'], 2); ?>
                            </div>

                            <div class="text-end mt-3">
                                <?php if ($can_cancel): ?>
                                    <button type="button" class="btn btn-info" onclick="contactSupport(<?php echo $order['id']; ?>)">
                                        <i class="fa fa-headset"></i> Contactar Soporte
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    <!-- Orders Section End -->

    <!-- Footer Section Begin -->
    <?php include 'includes/footer.php'; ?>
    <!-- Footer Section End -->

    <!-- Js Plugins -->
    <script src="src/js/jquery-3.3.1.min.js"></script>
    <script src="src/js/bootstrap.min.js"></script>
    <script src="src/js/jquery.nice-select.min.js"></script>
    <script src="src/js/jquery-ui.min.js"></script>
    <script src="src/js/jquery.slicknav.js"></script>
    <script src="src/js/mixitup.min.js"></script>
    <script src="src/js/owl.carousel.min.js"></script>
    <script src="src/js/main.js"></script>

    <script>
    function contactSupport(orderId) {
        Swal.fire({
            title: 'Contactar Soporte',
            html: `
                <p>Para cancelar tu orden, por favor contacta a nuestro servicio al cliente:</p>
                <p><strong>Email:</strong> soporte@marketplace.com</p>
                <p><strong>Teléfono:</strong> (123) 456-7890</p>
                <p>Menciona el número de orden: <strong>#${orderId}</strong></p>
            `,
            icon: 'info',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#3085d6'
        });
    }

    function viewOrderDetails(orderId) {
        $.ajax({
            url: 'get_order_details.php',
            type: 'GET',
            data: {
                order_id: orderId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let itemsHtml = '';
                    response.items.forEach(function(item) {
                        itemsHtml += `
                            <div class="order-item">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>${item.name}</h6>
                                        <p class="mb-0">Cantidad: ${item.quantity}</p>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <p class="mb-0">Precio: $${item.price}</p>
                                        <p class="mb-0">Subtotal: $${(item.price * item.quantity).toFixed(2)}</p>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        `;
                    });

                    $('#orderDetailsModal .modal-body').html(`
                        <div class="order-total text-end mb-3">
                            <h5>Total: $${response.total_amount}</h5>
                        </div>
                        ${itemsHtml}
                    `);
                    $('#orderDetailsModal').modal('show');
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message || 'No se pudieron cargar los detalles de la orden',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al cargar los detalles de la orden',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    }

    $(document).ready(function() {
        // Initialize the background image for breadcrumb
        var breadcrumbBg = $('.breadcrumb-section');
        if (breadcrumbBg.length) {
            var bgImg = breadcrumbBg.attr('data-setbg');
            breadcrumbBg.css('background-image', 'url(' + bgImg + ')');
        }

        // Asegurarse de que jQuery y SweetAlert2 estén cargados
        if (typeof $ === 'undefined') {
            console.error('jQuery no está cargado');
        }
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 no está cargado');
        }
    });
    </script>
</body>

</html>
