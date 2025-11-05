<?php
// logout.php
session_start();
 
// ยกเลิกตัวแปรเซสชั่นทั้งหมด
$_SESSION = array();
 
// ลบเซสชั่น
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
 
session_destroy();
 
// ส่งกลับไปหน้า Login
header("location: index.php");
exit;
?>