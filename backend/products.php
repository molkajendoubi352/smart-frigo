<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
require_once "config.php";

$data = json_decode(file_get_contents("php://input"), true);
$action = $data["action"] ?? $_GET["action"] ?? "";
$user_id = $data["user_id"] ?? 0;

if ($action === "getAll") {
    $sql = "SELECT p.* FROM products p
            JOIN user_fridges uf ON p.fridge_serial = uf.fridge_serial
            WHERE uf.user_id = $user_id";
    $result = mysqli_query($conn, $sql);
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    echo json_encode(["success" => true, "products" => $products]);
} else if ($action === "add") {
    $name = mysqli_real_escape_string($conn, $data["name"] ?? "");
    $category = $data["category"] ?? "";
    $fridge_serial = $data["fridge_serial"] ?? "";
    $quantity = $data["quantity"] ?? "";
    $expiry = $data["expiry"] ?? "";
    $emoji = $data["emoji"] ?? "🍴";

    $sql = "INSERT INTO products (name, category, fridge_serial, quantity, expiry_date, emoji)
            VALUES ('$name', '$category', '$fridge_serial', '$quantity', '$expiry', '$emoji')";

    if (mysqli_query($conn, $sql)) {
        $fridgeQuery = mysqli_query($conn, "SELECT name FROM fridges WHERE serial='$fridge_serial'");
        $fridge = mysqli_fetch_assoc($fridgeQuery);
        $fridge_name = mysqli_real_escape_string($conn, $fridge['name'] ?? $fridge_serial);
        mysqli_query($conn, "INSERT INTO history (fridge_serial, type, note) VALUES ('$fridge_serial', 'product_added', '$name added to $fridge_name')");

        $days = (strtotime($expiry) - mktime(0,0,0, date('m'), date('d'), date('Y'))) / 86400;
        if ($days !== false && $days <= 3) {
            if ($days < 0) {
                $alert_type = "critical";
                $alert_title = "$name has expired!";
            } else {
                $alert_type = "warning";
                $alert_title = "$name expires in " . round($days) . " day(s)";
            }
            mysqli_query($conn, "INSERT INTO alerts (type, title, description, fridge_serial) VALUES ('$alert_type', '$alert_title', 'Check your fridge', '$fridge_serial')");
        }

        echo json_encode(["success" => true, "id" => mysqli_insert_id($conn)]);
    } else {
        echo json_encode(["success" => false, "message" => mysqli_error($conn)]);
    }
} else if ($action === "edit") {
    $id = $data["id"] ?? 0;
    $name = $data["name"] ?? "";
    $category = $data["category"] ?? "";
    $fridge_serial = $data["fridge_serial"] ?? "";
    $quantity = $data["quantity"] ?? "";
    $expiry = $data["expiry"] ?? "";
    $emoji = $data["emoji"] ?? "🍴";

    $sql = "UPDATE products SET name='$name', category='$category', fridge_serial='$fridge_serial',
            quantity='$quantity', expiry_date='$expiry', emoji='$emoji' WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => mysqli_error($conn)]);
    }
} else if ($action === "delete") {
    $id = $data["id"] ?? 0;

    $productQuery = mysqli_query($conn, "SELECT name, fridge_serial FROM products WHERE id=$id");
    $product = mysqli_fetch_assoc($productQuery);
    $fridgeQuery = mysqli_query($conn, "SELECT name FROM fridges WHERE serial='{$product['fridge_serial']}'");
    $fridge = mysqli_fetch_assoc($fridgeQuery);
    $fridge_name = $fridge['name'] ?? $product['fridge_serial'];
    mysqli_query($conn, "INSERT INTO history (fridge_serial, type, note) VALUES ('{$product['fridge_serial']}', 'product_removed', '{$product['name']} removed from $fridge_name')");
    $sql = "DELETE FROM products WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => mysqli_error($conn)]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Unknown action"]);
}
?>
