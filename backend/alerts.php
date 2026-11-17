<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
require_once "config.php";

$data = json_decode(file_get_contents("php://input"), true);
$action = $data["action"] ?? $_GET["action"] ?? "";
$user_id = $data["user_id"] ?? 0;

if ($action === "getAll") {
    $result = mysqli_query($conn, "SELECT a.* FROM alerts a
        JOIN user_fridges uf ON a.fridge_serial = uf.fridge_serial
        WHERE uf.user_id = $user_id
        ORDER BY a.created_at DESC");
    $alerts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $alerts[] = $row;
    }
    echo json_encode(["success" => true, "alerts" => $alerts]);
} else if ($action === "dismiss") {
    $id = $data["id"] ?? 0;
    $sql = "UPDATE alerts SET dismissed=1 WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => mysqli_error($conn)]);
    }
} else if ($action === "dismissAll") {
    $sql = "UPDATE alerts SET dismissed=1 WHERE fridge_serial IN
            (SELECT fridge_serial FROM user_fridges WHERE user_id=$user_id)";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => mysqli_error($conn)]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Unknown action"]);
}
?>