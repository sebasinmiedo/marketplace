<?php
include 'check.php';

if (!isset($_GET['type']) || !isset($_GET['format'])) {
    die('Parámetros requeridos no especificados');
}

$type = $_GET['type'];
$format = $_GET['format'];

// Función para generar el nombre del archivo
function generateFilename($type) {
    return 'reporte_' . $type . '_' . date('Y-m-d_H-i-s');
}

// Función para formatear los datos según el tipo de reporte
function getReportData($type, $query) {
    switch ($type) {
        case 'sales':
            return $query->custom("
                SELECT 
                    o.id as order_id,
                    o.order_date,
                    u.username as customer,
                    o.total_amount,
                    o.status,
                    GROUP_CONCAT(p.name SEPARATOR ', ') as products
                FROM orders o
                JOIN accounts u ON o.user_id = u.id
                JOIN order_items oi ON o.id = oi.order_id
                JOIN products p ON oi.product_id = p.id
                WHERE o.status != 'cancelled'
                GROUP BY o.id
                ORDER BY o.order_date DESC
            ");
        
        case 'products':
            return $query->custom("
                SELECT 
                    p.name,
                    p.price_current,
                    p.price_old,
                    p.quantity,
                    p.rating,
                    c.category_name,
                    COUNT(oi.id) as times_ordered,
                    SUM(oi.quantity) as total_quantity_sold
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id AND o.status != 'cancelled'
                GROUP BY p.id
                ORDER BY total_quantity_sold DESC
            ");
        
        case 'users':
            return $query->custom("
                SELECT 
                    u.username,
                    u.email,
                    u.registration_date,
                    COUNT(DISTINCT o.id) as total_orders,
                    SUM(o.total_amount) as total_spent,
                    MAX(o.order_date) as last_order_date
                FROM accounts u
                LEFT JOIN orders o ON u.id = o.user_id AND o.status != 'cancelled'
                WHERE u.role = 'user'
                GROUP BY u.id
                ORDER BY total_spent DESC
            ");
        
        default:
            die('Tipo de reporte no válido');
    }
}

// Obtener los datos del reporte
$data = getReportData($type, $query);

if (empty($data)) {
    die('No hay datos para exportar');
}

// Obtener los encabezados de las columnas
$headers = array_keys($data[0]);

// Configurar los headers para CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . generateFilename($type) . '.csv"');

// Crear el archivo CSV
$output = fopen('php://output', 'w');

// Agregar BOM para caracteres especiales
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Escribir encabezados
fputcsv($output, $headers);

// Escribir datos
foreach ($data as $row) {
    fputcsv($output, $row);
}

fclose($output); 