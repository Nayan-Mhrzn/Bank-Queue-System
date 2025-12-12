<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'bank_queue_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

function db_query($sql, $params = [], $types = '') {
    global $conn;
    if (empty($params)) {
        return $conn->query($sql);
    }
    if ($types === '') {
        $types = str_repeat('s', count($params));
    }
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result();
}
?>
