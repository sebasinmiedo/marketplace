<?php
include 'check.php';

$user_id = $_SESSION['id'];
$orders = $query->getOrderHistory($user_id);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Ogani Template">
    <meta name="keywords" content="Ogani, unica, creative, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>My Orders</title>

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
                        <h2>My Orders</h2>
                        <div class="breadcrumb__option">
                            <a href="./">Home</a>
                            <span>My Orders</span>
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
                    <h3>No Orders Yet</h3>
                    <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
                    <a href="index.php" class="primary-btn">START SHOPPING</a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header d-flex justify-content-between align-items-center">
                            <div class="order-id">Order #<?= $order['id'] ?></div>
                            <div class="order-date"><?= date('F j, Y g:i A', strtotime($order['order_date'])) ?></div>
                            <div class="order-status status-<?= strtolower($order['status']) ?>">
                                <?= ucfirst($order['status']) ?>
                            </div>
                        </div>
                        <div class="order-body">
                            <div class="order-items">
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="item-row">
                                        <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                        <div class="item-details">
                                            <span>Qty: <?= $item['quantity'] ?></span>
                                            <span class="price">$<?= number_format($item['price_at_time'], 2) ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="shipping-info">
                                <h4><i class="fa fa-shipping-fast"></i> Shipping Information</h4>
                                <p><?= htmlspecialchars($order['shipping_address']) ?></p>
                                <p><?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_country']) ?> <?= htmlspecialchars($order['shipping_postal_code']) ?></p>
                                <p><i class="fa fa-phone"></i> <?= htmlspecialchars($order['phone_number']) ?></p>
                                <p><i class="fa fa-credit-card"></i> Payment Method: <?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></p>
                                <p><i class="fa fa-check-circle"></i> Payment Status: <?= ucfirst($order['payment_status']) ?></p>
                            </div>

                            <div class="total-amount">
                                Total Amount: $<?= number_format($order['total_amount'], 2) ?>
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
        $(document).ready(function() {
            // Initialize the background image for breadcrumb
            var breadcrumbBg = $('.breadcrumb-section');
            if (breadcrumbBg.length) {
                var bgImg = breadcrumbBg.attr('data-setbg');
                breadcrumbBg.css('background-image', 'url(' + bgImg + ')');
            }
        });
    </script>
</body>

</html>
