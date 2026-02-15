<?php
// เพิ่ม Header เพื่อรองรับการเรียกจาก Vue (CORS) และกำหนดเป็น JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'condb.php';

$action = $_POST['action'] ?? null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
        switch($action) {
            case 'add':
                $emp_id = $_POST['emp_id'];
                $full_name = $_POST['full_name'];
                $department = $_POST['department'];
                $salary = $_POST['salary'];
                $active = $_POST['active'];

                $filename = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $targetDir = "uploads/";
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }
                    $filename = time() . '_' . basename($_FILES['image']['name']);
                    $targetFile = $targetDir . $filename;
                    move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
                }

                
                $sql = "INSERT INTO employee (emp_id, full_name, department, salary, active, image)
                        VALUES (:emp_id, :full_name, :department, :salary, :active, :image)";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':emp_id' => $emp_id,
                    ':full_name' => $full_name,
                    ':department' => $department,
                    ':salary' => $salary,
                    ':active' => $active,
                    ':image' => $filename
                ]);

                echo json_encode(["success" => true, "message" => "เพิ่มข้อมูลพนักงานสำเร็จ"]);
                break;

            case 'update':
                $emp_id = $_POST['emp_id'];
                $full_name = $_POST['full_name'];
                $department = $_POST['department'];
                $salary = $_POST['salary'];
                $active = $_POST['active'];

                $imageSQL = "";
                $params = [
                    ':full_name' => $full_name,
                    ':department' => $department,
                    ':salary' => $salary,
                    ':active' => $active,
                    ':emp_id' => $emp_id
                ];

                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $targetDir = "uploads/";
                    $filename = time() . '_' . basename($_FILES['image']['name']);
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $filename)) {
                        $imageSQL = ", image = :image";
                        $params[':image'] = $filename;
                    }
                }

                $sql = "UPDATE employee SET 
                            full_name = :full_name,
                            department = :department,
                            salary = :salary,
                            active = :active
                            $imageSQL
                        WHERE emp_id = :emp_id";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                echo json_encode(["success" => true, "message" => "แก้ไขข้อมูลพนักงานสำเร็จ"]);
                break;

            case 'delete':
                $emp_id = $_POST['emp_id'];
                $stmt = $conn->prepare("DELETE FROM employee WHERE emp_id = :emp_id");
                $stmt->execute([':emp_id' => $emp_id]);
                echo json_encode(["success" => true, "message" => "ลบข้อมูลพนักงานสำเร็จ"]);
                break;

            default:
                echo json_encode(["success" => false, "error" => "Action ไม่ถูกต้อง"]);
                break;
        }
    } else {
        // GET: ดึงข้อมูลพนักงานทั้งหมด
        $stmt = $conn->prepare("SELECT * FROM employee ORDER BY emp_id DESC");
        $stmt->execute();
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // จุดที่ผิด: เดิมคุณเขียน $employee (ไม่มี s) ต้องเป็น $employees
        echo json_encode(["success" => true, "data" => $employees]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database Error: " . $e->getMessage()]);
}
?>