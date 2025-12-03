<?php
require_once 'database.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_POST['action'];

if ($action === 'get_products') {
    $stmt = $pdo->prepare("SELECT products.*, u.username AS added_by_name 
                        FROM products
                        JOIN users u ON products.added_by = u.user_id
                        ORDER BY products.date_added DESC");
    $stmt->execute();
    $products = $stmt->fetchAll();
    echo json_encode(['success' => true, 'products' => $products]);
    exit;
}

if ($action === 'process_transaction') {
    try {
        $pdo->beginTransaction();

    $order = $input['order'];
    $totalAmount = $order['totalAmount'];

    $stmt = $pdo->prepare("INSERT INTO transactions (total_amount) VALUES (?)");
    $stmt->execute([$totalAmount]);
    $transactionId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO transaction_items (transaction_id, product_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
        
        foreach ($order['items'] as $item) {
            $stmt->execute([
                $transactionId, 
                $item['productId'],
                $item['quantity'],
                $item['subtotal']
            ]);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'transactionId' => $transactionId]);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false,'message' => 'Transaction failed: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'register') {
    $username = $input['username'] ;
    $first_name = $input['first_name'];
    $last_name = $input['last_name'];
    $password = $input['password'] ;
    $confirm_password = $input['confirm_password'] ;
    $superadmin_code = $input['superadmin_code'];
    $role = $input['role'];

    if (empty($username) || empty($first_name) || empty($last_name) || empty($password) || empty($superadmin_code)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Username already exists.'
        ]);
        exit;
    }

    if (strlen($password) < 8) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 8 characters long.'
        ]);
        exit;
    }

    if ($password !== $confirm_password) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Passwords do not match.'
        ]);
        exit;
    }

    if ($superadmin_code !== 'superadmin123') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid verification code.'
        ]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, first_name, last_name, password, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt->execute([$username, $first_name, $last_name, $hashed_password, 'superadmin']);
        echo json_encode([
            'success' => true,
            'message' => 'Super Administrator account created successfully.'
        ]);
        exit;
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

if ($action === 'login') {
    $username = $input['username'];
    $password = $input['password'];

    if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false,'message' => 'Username and password are required.']);
    exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        exit;
    }

    if ($user['status'] === 'suspended') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Your account has been suspended. Please contact the super administrator.']);
        exit;
    }

    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['status'] = $user['status'];

    echo json_encode(['success' => true, 'message' => 'Login successful.']);
    exit;
}

if ($action === 'add_product') {
    $userId = $_SESSION['user_id'];
    $product_name = $input['product_name'] ?? $_POST['product_name'] ;
    $price = $input['price'] ?? $_POST['price'];
    $category = $input['category'] ?? $_POST['category'];
    

    if (empty($product_name) || $price === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product name and price are required.']);
    exit;
    }

    if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product image is required.']);
        exit;
    }

    $file = $_FILES['product_image'];
    $imgDir = __DIR__ . '/../images/menu';

    $img_type = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    if (!in_array($file['type'], $img_type)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid image type.']);
        exit;
    }
    
    if (!file_exists($imgDir)) {
        mkdir($imgDir, 0755, true);
    }

    $productName = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($product_name));
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $productName . '.' . $ext;
    $targetPath = $imgDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
        exit;
    }

    $relativePath = 'images/menu/' . $filename;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO products (product_name, price, product_image, category, added_by, date_added) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$product_name, $price, $relativePath, $category, $userId]);
        echo json_encode(['success' => true, 'message' => 'Product added successfully.']);
        exit;
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Failed to add product: ' . $e->getMessage()]);
        exit;
    }
}

if ($action === 'get_staff') {
    $stmt = $pdo->prepare("SELECT * FROM users 
                                WHERE role = 'admin' 
                                ORDER BY date_added DESC");
    $stmt->execute();
    $staff = $stmt->fetchAll();

    echo json_encode(['success' => true, 'staff' => $staff]);
    exit;
}

if ($action === 'add_staff') {
    $username = $input['username'] ;
    $first_name = $input['first_name'];
    $last_name = $input['last_name'];
    $password = $input['password'] ;
    $confirm_password = $input['confirm_password'] ;
    $role = $input['role'];

    if (empty($username) || empty($first_name) || empty($last_name) || empty($password) || empty($confirm_password)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required.'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Username already exists.'
        ]);
        exit;
    }

    if (strlen($password) < 8) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 8 characters long.'
        ]);
        exit;
    }

    if ($password !== $confirm_password) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Passwords do not match.'
        ]);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, first_name, last_name, password, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt->execute([$username, $first_name, $last_name, $hashed_password, 'admin']);
        echo json_encode([
            'success' => true,
            'message' => 'Administrator account created successfully.'
        ]);
        exit;
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

if ($action === 'update_staff_status') {
    $userId = $input['user_id'];
    $status = $input['status'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ? AND role = 'admin'");
        $stmt->execute([$status, $userId]);
        
        echo json_encode(['success' => true, 'message' => 'Staff status updated successfully']);
        exit;
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

if ($action === 'get_transaction') {
    $start_date = $input['start_date'];
    $end_date = $input['end_date'];

    $query = "SELECT * FROM transactions WHERE 1 = 1";
    $params = [];

    if (!empty($start_date) && !empty($end_date)) {
        $query .= " AND DATE(date_added) BETWEEN ? AND ?";
        $params = [$start_date, $end_date];
    }

    $query .= " ORDER BY date_added DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll();

    echo json_encode(['success' => true, 'transactions' => $transactions]);
    exit;
}

if ($action === 'get_transaction_details') {
    $transactionId = intval($input['transaction_id']);

    $stmt = $pdo->prepare("SELECT transaction_items.*, products.product_name 
                            FROM transaction_items
                            JOIN products ON transaction_items.product_id = products.product_id
                            WHERE transaction_items.transaction_id = ?");
    $stmt->execute([$transactionId]);
    $items = $stmt->fetchAll();

    echo json_encode(['success' => true, 'items' => $items]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid action']);
exit;
?>