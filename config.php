<?php

/**
 * Database Class
 * 
 * This class handles all database operations for the marketplace application.
 * It provides methods for CRUD operations, user authentication, and file handling.
 * The class uses mysqli for database connections and includes security measures
 * like input validation and password hashing.
 */
class Database
{
    /** @var mysqli Database connection instance */
    private $conn;

    /**
     * Constructor - Establishes database connection
     * Initializes the database connection with specified credentials
     * Dies with error message if connection fails
     */
    public function __construct()
    {
        $servername = "192.168.100.83";
        $username = "external_user";
        $password = '$Admin123';
        $dbname = "marketplace";

        $this->conn = new mysqli($servername, $username, $password, $dbname);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    /**
     * Destructor - Closes database connection
     * Ensures proper cleanup of database resources
     */
    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    /**
     * Validates and sanitizes input data
     * Prevents SQL injection and XSS attacks
     * 
     * @param mixed $value The input value to validate
     * @return string The sanitized value
     */
    function validate($value)
    {
        $value = trim($value);
        $value = stripslashes($value);
        $value = htmlspecialchars($value);
        $value = mysqli_real_escape_string($this->conn, $value);
        return $value;
    }

    /**
     * Executes a SQL query and handles errors
     * 
     * @param string $sql The SQL query to execute
     * @return mysqli_result The query result
     * @throws Exception on query failure
     */
    public function executeQuery($sql)
    {
        $result = $this->conn->query($sql);
        if ($result === false) {
            die("ERROR: " . $this->conn->error);
        }
        return $result;
    }

    /**
     * Performs SELECT operation on database
     * 
     * @param string $table Table name
     * @param string $columns Columns to select, defaults to "*"
     * @param string $condition WHERE clause conditions
     * @return array Associative array of results
     */
    public function select($table, $columns = "*", $condition = "")
    {
        $sql = "SELECT $columns FROM $table $condition";
        return $this->executeQuery($sql)->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Executes custom SQL query and returns results
     * 
     * @param string $sql Custom SQL query
     * @return array Associative array of results
     */
    public function custom($sql)
    {
        return $this->executeQuery($sql)->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Gets the ID of the last inserted record
     * 
     * @return int Last insert ID
     */
    public function getLastInsertId()
    {
        return $this->conn->insert_id;
    }

    /**
     * Performs INSERT operation on database
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value pairs
     * @return bool True on success, false on failure
     */
    public function insert($table, $data)
    {
        $keys = implode(', ', array_keys($data));
        $values = "'" . implode("', '", array_values($data)) . "'";
        $sql = "INSERT INTO $table ($keys) VALUES ($values)";
        return $this->executeQuery($sql);
    }

    /**
     * Performs UPDATE operation on database
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value pairs
     * @param string $condition WHERE clause conditions
     * @return bool True on success, false on failure
     */
    public function update($table, $data, $condition = "")
    {
        $set = '';
        foreach ($data as $key => $value) {
            $set .= "$key = '$value', ";
        }
        $set = rtrim($set, ', ');
        $sql = "UPDATE $table SET $set $condition";
        return $this->executeQuery($sql);
    }

    /**
     * Performs DELETE operation on database
     * 
     * @param string $table Table name
     * @param string $condition WHERE clause conditions
     * @return bool True on success, false on failure
     */
    public function delete($table, $condition = "")
    {
        $sql = "DELETE FROM $table $condition";
        return $this->executeQuery($sql);
    }

    /**
     * Hashes password using HMAC-SHA256
     * 
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public function hashPassword($password)
    {
        return hash_hmac('sha256', $password, 'iqbolshoh');
    }

    /**
     * Authenticates user credentials
     * 
     * @param string $username Username
     * @param string $password Plain text password
     * @param string $table Table name to check credentials against
     * @return array User data if authenticated, empty array if not
     */
    public function authenticate($username, $password, $table)
    {
        $username = $this->validate($username);
        $condition = "WHERE username = '" . $username . "' AND password = '" . $this->hashPassword($password) . "'";
        return $this->select($table, "*", $condition);
    }

    /**
     * Registers a new user
     * 
     * @param string $name User's name
     * @param string $number Phone number
     * @param string $email Email address
     * @param string $username Username
     * @param string $password Plain text password
     * @param string $role User role
     * @return mixed User ID on success, false on failure
     */
    public function registerUser($name, $number, $email, $username, $password, $role)
    {
        $name = $this->validate($name);
        $number = $this->validate($number);
        $email = $this->validate($email);
        $username = $this->validate($username);

        $password_hash = $this->hashPassword($password);

        $data = array(
            'name' => $name,
            'number' => $number,
            'email' => $email,
            'username' => $username,
            'password' => $password_hash,
            'role' => $role
        );

        $user_id = $this->insert('accounts', $data);

        if ($user_id) {
            return $user_id;
        }
        return false;
    }

    /**
     * Saves multiple product images to database and filesystem
     * 
     * @param array $files Array of uploaded files
     * @param string $path Directory path to save images
     * @param int $productId Product ID to associate images with
     * @return array|string Array of filenames on success, false on failure
     */
    function saveImagesToDatabase($files, $path, $productId)
    {
        if (is_array($files['tmp_name'])) {
            $uploaded_files = array();
            foreach ($files['tmp_name'] as $index => $tmp_name) {
                $file_name = $files['name'][$index];
                $file_info = pathinfo($file_name);
                $file_extension = $file_info['extension'];
                $new_file_name = md5($tmp_name . date("Y-m-d_H-i-s") . rand(1, 9999999) . $productId) . "." . $file_extension;
                if (move_uploaded_file($tmp_name, $path . $new_file_name)) {
                    $uploaded_files[] = $new_file_name;
                    $this->insert('product_images', array('product_id' => $productId, 'image_url' => $new_file_name));
                }
            }
            return $uploaded_files;
        } else {
            $file_name = $files['name'];
            $file_tmp = $files['tmp_name'];

            $file_info = pathinfo($file_name);
            $file_format = $file_info['extension'];

            $new_file_name = md5($file_tmp . date("Y-m-d_H-i-s") . rand(1, 9999999) . $productId) . "." . $file_format;

            if (move_uploaded_file($file_tmp, $path . $new_file_name)) {
                $this->insert('product_images', array('product_id' => $productId, 'image_url' => $new_file_name));
                return $new_file_name;
            }
            return false;
        }
    }

    /**
     * Saves a single image file
     * 
     * @param array $file Uploaded file array
     * @param string $path Directory path to save image
     * @return string|bool New filename on success, false on failure
     */
    function saveImage($file, $path)
    {
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];

        $file_info = pathinfo($file_name);
        $file_format = $file_info['extension'];

        $new_file_name = md5($file_tmp . date("Y-m-d_H-i-s")) . rand(1, 9999999) . "." . $file_format;

        if (move_uploaded_file($file_tmp, $path . $new_file_name)) {
            return $new_file_name;
        }
        return false;
    }

    /**
     * Gets all product categories
     * 
     * @return array Associative array of category ID => name pairs
     */
    public function getCategories()
    {
        $categories = array();
        $result = $this->select('categories', 'id, category_name');
        foreach ($result as $row) {
            $categories[$row['id']] = $row['category_name'];
        }
        return $categories;
    }

    /**
     * Gets product details by ID
     * 
     * @param int $product_id Product ID
     * @return array Product details
     */
    public function getProduct($product_id)
    {
        $result = $this->select('products', '*', 'WHERE id = ' . $product_id);
        return $result[0];
    }

    /**
     * Gets all image IDs for a product
     * 
     * @param int $product_id Product ID
     * @return array Array of image IDs
     */
    public function getProductImageID($product_id)
    {
        $images = $this->select('product_images', 'id', 'WHERE product_id = ' . $product_id);
        $id = array();
        foreach ($images as $image) {
            $id[] = $image['id'];
        }
        return $id;
    }

    /**
     * Gets image URL by image ID
     * 
     * @param int $id Image ID
     * @return string Image URL
     */
    function getProductImage($id)
    {
        global $query;
        $result = $this->select('product_images', 'image_url', 'WHERE id = ' . $id);
        return $result[0]['image_url'];
    }

    /**
     * Gets cart items for a user
     * 
     * @param int $user_id User ID
     * @return array Cart items with product details and totals
     */
    public function getCartItems($user_id)
    {
        $sql = "SELECT 
            p.id,
            p.name,
            p.price_current,
            p.price_old,
            c.number_of_products,
            (p.price_current * c.number_of_products) AS total_price
        FROM 
            cart c
        JOIN
            products p ON c.product_id = p.id
        WHERE 
            c.user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cartItems = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $cartItems;
    }

    /**
     * Gets all image URLs for a product
     * 
     * @param int $product_id Product ID
     * @return array Array of image URLs
     */
    public function getProductImages($product_id)
    {
        $sql = "SELECT image_url FROM product_images WHERE product_id = $product_id";
        $result = $this->executeQuery($sql)->fetch_all(MYSQLI_ASSOC);
        $imageUrls = array();
        foreach ($result as $row) {
            $imageUrls[] = $row['image_url'];
        }
        return $imageUrls;
    }

    /**
     * Gets wishlist items for a user
     * 
     * @param int $user_id User ID
     * @return array Wishlist items with product details
     */
    public function getWishes($user_id)
    {
        $sql = "SELECT 
            p.name,
            p.price_current,
            p.price_old,
            w.product_id
        FROM 
            wishes w
        JOIN
            products p ON w.product_id = p.id
        WHERE 
            w.user_id = $user_id";
        return $this->executeQuery($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function lastInsertId($table, $data)
    {
        $keys = implode(', ', array_keys($data));
        $values = "'" . implode("', '", array_values($data)) . "'";
        $sql = "INSERT INTO $table ($keys) VALUES ($values)";
        $insert_result = $this->executeQuery($sql);

        if ($insert_result) {
            return $this->conn->insert_id;
        } else {
            return false;
        }
    }

    /**
     * Creates a new order from cart items
     * 
     * @param int $user_id User ID
     * @param array $shipping_info Shipping information
     * @param string $payment_method Payment method
     * @return int|bool Order ID on success, false on failure
     */
    public function createOrder($user_id, $shipping_info, $payment_method)
    {
        // Start transaction
        $this->conn->begin_transaction();

        try {
            // Get cart items
            $cart_items = $this->getCartItems($user_id);
            
            // Calculate total amount
            $total_amount = 0;
            foreach ($cart_items as $item) {
                $total_amount += $item['total_price'];
            }

            // Create order
            $order_data = array(
                'user_id' => $user_id,
                'total_amount' => $total_amount,
                'shipping_address' => $this->validate($shipping_info['address']),
                'shipping_city' => $this->validate($shipping_info['city']),
                'shipping_country' => $this->validate($shipping_info['country']),
                'shipping_postal_code' => $this->validate($shipping_info['postal_code']),
                'phone_number' => $this->validate($shipping_info['phone']),
                'payment_method' => $this->validate($payment_method)
            );

            $order_id = $this->lastInsertId('orders', $order_data);

            if (!$order_id) {
                throw new Exception("Failed to create order");
            }

            // Add order items
            foreach ($cart_items as $item) {
                $order_item_data = array(
                    'order_id' => $order_id,
                    'product_id' => $item['id'],
                    'quantity' => $item['number_of_products'],
                    'price_at_time' => $item['price_current']
                );
                
                if (!$this->insert('order_items', $order_item_data)) {
                    throw new Exception("Failed to add order items");
                }
            }

            // Clear cart after successful order
            $this->delete('cart', "WHERE user_id = $user_id");

            // Commit transaction
            $this->conn->commit();
            return $order_id;

        } catch (Exception $e) {
            // Rollback on error
            $this->conn->rollback();
            return false;
        }
    }

    /**
     * Gets order history for a user
     * 
     * @param int $user_id User ID
     * @return array Array of orders with their items
     */
    public function getOrderHistory($user_id)
    {
        $orders = $this->select('orders', '*', "WHERE user_id = $user_id ORDER BY order_date DESC");
        
        foreach ($orders as &$order) {
            $sql = "SELECT 
                oi.*,
                p.name as product_name,
                p.price_current as current_price
            FROM 
                order_items oi
            JOIN 
                products p ON oi.product_id = p.id
            WHERE 
                oi.order_id = {$order['id']}";
            
            $order['items'] = $this->custom($sql);
        }
        
        return $orders;
    }

    /**
     * Gets a specific order's details
     * 
     * @param int $order_id Order ID
     * @param int $user_id User ID (for security)
     * @return array|bool Order details or false if not found/unauthorized
     */
    public function getOrderDetails($order_id, $user_id)
    {
        $order = $this->select('orders', '*', "WHERE id = $order_id AND user_id = $user_id");
        
        if (empty($order)) {
            return false;
        }

        $order = $order[0];
        
        $sql = "SELECT 
            oi.*,
            p.name as product_name,
            p.price_current as current_price
        FROM 
            order_items oi
        JOIN 
            products p ON oi.product_id = p.id
        WHERE 
            oi.order_id = $order_id";
        
        $order['items'] = $this->custom($sql);
        
        return $order;
    }
}
