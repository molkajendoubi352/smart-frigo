<?php
header('Content-Type: application/json');
require 'db.php';

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($name === '' || $email === '' || $password === '') {
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

$name = mysqli_real_escape_string($conn, $name);
$email = mysqli_real_escape_string($conn, $email);
$password = mysqli_real_escape_string($conn, $password);

$initials = '';
$parts = explode(' ', strtoupper($name));
foreach ($parts as $p) {
    $initials .= $p[0];
}
$initials = substr($initials, 0, 2);

$sql = "INSERT INTO users (name, email, password, initials)
        VALUES ('$name', '$email', '$password', '$initials')";

if (mysqli_query($conn, $sql)) {
    $newId = mysqli_insert_id($conn);
    $user = [
        'id' => $newId,
        'name' => $name,
        'email' => $email,
        'initials' => $initials
    ];
    echo json_encode($user);
} else {
    echo json_encode(['error' => 'Registration failed']);
}
?>