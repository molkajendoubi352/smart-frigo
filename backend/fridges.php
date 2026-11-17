<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
require_once "config.php";

$data = json_decode(file_get_contents("php://input"), true);
$action = $data["action"] ?? $_GET["action"] ?? "";
$user_id = $data["user_id"] ?? 0;

if ($action === "getAll") {
    $sql = "SELECT f.* FROM fridges f
            JOIN user_fridges uf ON f.serial = uf.fridge_serial
            WHERE uf.user_id = $user_id";
    $result = mysqli_query($conn, $sql);
    $fridges = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $fridges[] = $row;
    }
    echo json_encode(["success" => true, "fridges" => $fridges]);
} else if ($action === "add") {
    $name = $data["name"] ?? "";
    $serial = $data["serial"] ?? "";
    $min_temp = $data["min_temp"] ?? 1;
    $max_temp = $data["max_temp"] ?? 8;
    $location = $data["location"] ?? "";

    $alreadyLinked = mysqli_query($conn, "SELECT * FROM user_fridges WHERE user_id=$user_id AND fridge_serial='$serial'");
    if (mysqli_num_rows($alreadyLinked) > 0) {
        echo json_encode(["success" => false, "message" => "You already have this fridge"]);
        exit;
    }

    $exists = mysqli_query($conn, "SELECT serial FROM fridges WHERE serial='$serial'");
    if (mysqli_num_rows($exists) === 0) {
        $sql = "INSERT INTO fridges (serial, name, min_temp, max_temp, location, current_temp, status)
                VALUES ('$serial', '$name', $min_temp, $max_temp, '$location', $min_temp, 'online')";
        if (!mysqli_query($conn, $sql)) {
            echo json_encode(["success" => false, "message" => mysqli_error($conn)]);
            exit;
        }
        $shared = false;
    } else {
        $shared = true;
    }

    mysqli_query($conn, "INSERT INTO user_fridges (user_id, fridge_serial) VALUES ($user_id, '$serial')");
    echo json_encode(["success" => true, "shared" => $shared]);
} else if ($action === "edit") {
    $serial = $data["serial"] ?? "";
    $name = $data["name"] ?? "";
    $min_temp = $data["min_temp"] ?? 1;
    $max_temp = $data["max_temp"] ?? 8;
    $location = $data["location"] ?? "";

    $check = mysqli_query($conn, "SELECT * FROM user_fridges WHERE user_id=$user_id AND fridge_serial='$serial'");
    if (mysqli_num_rows($check) === 0) {
        echo json_encode(["success" => false, "message" => "Access denied"]);
        exit;
    }

    $sql = "UPDATE fridges SET name='$name', min_temp=$min_temp, max_temp=$max_temp, location='$location' WHERE serial='$serial'";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => mysqli_error($conn)]);
    }
} else if ($action === "updateRange") {
    $serial = $data["serial"] ?? "";
    $min_temp = $data["min_temp"] ?? 1;
    $max_temp = $data["max_temp"] ?? 8;

    $sql = "UPDATE fridges SET min_temp=$min_temp, max_temp=$max_temp WHERE serial='$serial'";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => mysqli_error($conn)]);
    }
} else if ($action === "delete") {
    $serial = $data["serial"] ?? "";
    $sql = "DELETE FROM user_fridges WHERE user_id=$user_id AND fridge_serial='$serial'";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => mysqli_error($conn)]);
    }
} else if ($action === "updateTemp") {
    $serial = $data["serial"] ?? "";
    $temp = $data["temp"] ?? 0;
    mysqli_query($conn, "UPDATE fridges SET current_temp=$temp WHERE serial='$serial'");
    mysqli_query($conn, "INSERT INTO temp_history (fridge_serial, temperature) VALUES ('$serial', $temp)");
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Unknown action"]);
}
?>
