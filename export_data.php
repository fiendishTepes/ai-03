<?php
// api/export_data.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect หรือแสดงข้อผิดพลาดถ้าไม่ได้ Login หรือไม่ได้ใช้ POST
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // ดึงข้อมูลทั้งหมด
    $sql = "SELECT 
                t.transaction_id AS 'รหัสรายการ', 
                t.transaction_date AS 'วันที่', 
                CASE t.type WHEN 'income' THEN 'รายรับ' ELSE 'รายจ่าย' END AS 'ประเภท', 
                t.amount AS 'จำนวนเงิน', 
                c.category_name AS 'หมวดหมู่',
                t.description AS 'รายละเอียด', 
                t.created_at AS 'บันทึกเมื่อ'
            FROM transactions t
            LEFT JOIN categories c ON t.category_id = c.category_id
            WHERE t.user_id = :user_id 
            ORDER BY t.transaction_date DESC, t.transaction_id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($data)) {
        echo "<script>alert('ไม่พบข้อมูลรายการธุรกรรมที่สามารถส่งออกได้'); window.location.href='../export.php';</script>";
        exit;
    }

    // ตั้งค่า Header สำหรับไฟล์ CSV
    $filename = "transactions_export_" . date('Ymd_His') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // สร้างไฟล์ CSV
    $output = fopen('php://output', 'w');
    
    // ตั้งค่าสำหรับภาษาไทย
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); 

    // เขียนหัวตาราง
    fputcsv($output, array_keys($data[0]));

    // เขียนข้อมูล
    foreach ($data as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    // ใช้ SweetAlert ในหน้าเว็บจริง
    echo "<script>alert('เกิดข้อผิดพลาดในการส่งออก: " . $e->getMessage() . "'); window.location.href='../export.php';</script>";
    exit;
}
?>