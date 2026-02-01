<?php
include 'condb.php';
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // ดึงข้อมูลทั้งหมด
            $stmt = $conn->query("SELECT * FROM type");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'POST':
            // เพิ่มข้อมูล
            $data = json_decode(file_get_contents("php://input"), true);
            $stmt = $conn->prepare("INSERT INTO type (type_name) VALUES (?)");
            $stmt->execute([$data['type_name']]);
            echo json_encode(["success" => true, "message" => "เพิ่มข้อมูลสำเร็จ"]);
            break;

        case 'PUT':
            // แก้ไขข้อมูล
            $data = json_decode(file_get_contents("php://input"), true);
            $stmt = $conn->prepare("UPDATE type SET type_name = ? WHERE type_id = ?");
            $stmt->execute([$data['type_name'], $data['type_id']]);
            echo json_encode(["success" => true, "message" => "แก้ไขข้อมูลสำเร็จ"]);
            break;

        case 'DELETE':
            // ลบข้อมูล
            $data = json_decode(file_get_contents("php://input"), true);
            $stmt = $conn->prepare("DELETE FROM type WHERE type_id = ?");
            $stmt->execute([$data['type_id']]);
            echo json_encode(["success" => true, "message" => "ลบข้อมูลสำเร็จ"]);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>