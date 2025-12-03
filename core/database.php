<?php
session_start();

$host = 'localhost';
$dbname = 'oms_alpine_1';
$username = 'root';
$password = '';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  http_response_code(400);
  echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
  exit;
}

?>