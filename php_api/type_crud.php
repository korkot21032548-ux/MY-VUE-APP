<?php
include 'condb.php';
header("Content-Type: application/json; charset=UTF-8");

try {
    $method = $_SERVER['REQUEST_METHOD'];

    // ✅ 1. ดึงข้อมูลจาก type
    if ($method === "GET") {
        // เปลี่ยนจาก type เป็น type
        $stmt = $conn->prepare("SELECT * FROM type ORDER BY type_id DESC");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result); 
    }

    // ✅ 2. เพิ่มข้อมูลลง type
    elseif ($method === "POST") {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) { $data = $_POST; }

        if (empty($data["type_name"])) {
            echo json_encode(["success" => false, "message" => "กรุณากรอกชื่อประเภทสินค้า"]);
            exit;
        }

        // เปลี่ยนเป็น type
        $stmt = $conn->prepare("INSERT INTO type (type_name) VALUES (:type_name)");
        $stmt->bindParam(":type_name", $data["type_name"]);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "เพิ่มข้อมูลเรียบร้อย"]);
        } else {
            echo json_encode(["success" => false, "message" => "ไม่สามารถเพิ่มข้อมูลได้"]);
        }
    }

    // ✅ 3. แก้ไขข้อมูลใน type
    elseif ($method === "PUT") {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data["type_id"]) || empty($data["type_name"])) {
            echo json_encode(["success" => false, "message" => "ข้อมูลไม่ครบถ้วน"]);
            exit;
        }

        // เปลี่ยนเป็น type
        $stmt = $conn->prepare("UPDATE type SET type_name = :type_name WHERE type_id = :id");
        $stmt->bindParam(":type_name", $data["type_name"]);
        $stmt->bindParam(":id", $data["type_id"], PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "แก้ไขข้อมูลเรียบร้อย"]);
        } else {
            echo json_encode(["success" => false, "message" => "ไม่สามารถแก้ไขข้อมูลได้"]);
        }
    }

    // ✅ 4. ลบข้อมูลจาก type
    elseif ($method === "DELETE") {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data["type_id"])) {
            echo json_encode(["success" => false, "message" => "ไม่พบรหัสที่ต้องการลบ"]);
            exit;
        }

        // เปลี่ยนเป็น type
        $stmt = $conn->prepare("DELETE FROM type WHERE type_id = :id");
        $stmt->bindParam(":id", $data["type_id"], PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "ลบข้อมูลเรียบร้อย"]);
        } else {
            echo json_encode(["success" => false, "message" => "ไม่สามารถลบข้อมูลได้"]);
        }
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>