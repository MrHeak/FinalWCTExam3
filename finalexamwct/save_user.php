<?php
header('Content-Type: application/json');

// Step 1: Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Only POST requests are allowed."]);
    exit();
}

// Step 2: Read JSON input
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

// Step 3: Validate required fields
if (!$data || !isset($data['name'], $data['email'], $data['registered_on'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or incomplete JSON payload."]);
    exit();
}

// Step 4: Validate email format
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid email format."]);
    exit();
}

// Step 5: Connect to DB and insert
$host = '127.0.0.1';
$port = 3307;
$dbname = 'myapp';
$username = 'root';
$password = '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Step 6: Prepare and insert
    $stmt = $pdo->prepare("INSERT INTO users (name, email, registered_on) VALUES (:name, :email, :registered_on)");
    $stmt->execute([
        ':name' => $data['name'],
        ':email' => $email,
        ':registered_on' => $data['registered_on']
    ]);

    http_response_code(201);
    echo json_encode(["message" => "User saved successfully."]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
