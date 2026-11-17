<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
require_once "config.php";

$data = json_decode(file_get_contents("php://input"), true);
$action = $data["action"] ?? "";

if ($action === "login") {
    $email = $data["email"] ?? "";
    $password = $data["password"] ?? "";

    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        echo json_encode(["success" => true, "user" => $user]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid email or password"]);
    }
} else if ($action === "register") {
    $name = $data["name"] ?? "";
    $email = $data["email"] ?? "";
    $password = $data["password"] ?? "";
    $initials = strtoupper(substr($name, 0, 1));

    $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        echo json_encode(["success" => false, "message" => "Email already registered"]);
        exit;
    }

    $sql = "INSERT INTO users (name, email, password, initials) VALUES ('$name', '$email', '$password', '$initials')";
    if (mysqli_query($conn, $sql)) {
        $id = mysqli_insert_id($conn);
        $user = ["id" => $id, "name" => $name, "email" => $email, "initials" => $initials];
        echo json_encode(["success" => true, "user" => $user]);
    } else {
        echo json_encode(["success" => false, "message" => "Registration failed"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Unknown action"]);
}
?>
