<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
require_once "config.php";

$data = json_decode(file_get_contents("php://input"), true);
$action = $data["action"] ?? $_GET["action"] ?? "";
$user_id = $data["user_id"] ?? 0;

if ($action === "getAll") {
    $fridge_id = $data["fridge_id"] ?? null;
    $hours = $data["hours"] ?? 24;

    if ($fridge_id) {
        $sql = "SELECT h.* FROM history h
            JOIN user_fridges uf ON h.fridge_serial = uf.fridge_serial
            WHERE uf.user_id = $user_id
            AND h.fridge_serial = '$fridge_id'
            AND h.recorded_at >= NOW() - INTERVAL $hours HOUR
            ORDER BY h.recorded_at ASC";
    } else {
        $sql = "SELECT h.* FROM history h
            JOIN user_fridges uf ON h.fridge_serial = uf.fridge_serial
            WHERE uf.user_id = $user_id
            AND h.recorded_at >= NOW() - INTERVAL $hours HOUR
            ORDER BY h.recorded_at ASC";
    }

    $result = mysqli_query($conn, $sql);
    $history = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $history[] = $row;
    }
    echo json_encode(["success" => true, "history" => $history]);
} else if ($action === "clear") {
    $sql = "DELETE FROM history WHERE fridge_serial IN
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