<?php
// index.php (หน้า Login/Register)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/config/db_config.php';

// ถ้า Login แล้ว ให้ส่งไปหน้า Dashboard
if (isset($_SESSION['user_id'])) {
    header("location: dashboard.php");
    exit;
}

$login_error = '';
$register_error = '';
$register_success = '';

// --- ฟังก์ชันสำหรับ Login ---
function handleLogin($pdo, &$login_error) {
    $username = trim($_POST["login_username"]); 
    $password = trim($_POST["login_password"]);

    if (empty($username) || empty($password)) {
        $login_error = "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน";
        return;
    } 

    $sql = "SELECT user_id, username, password FROM users WHERE username = :username";
    
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() == 1) {
                if ($row = $stmt->fetch()) {
                    $hashed_password = $row['password'];
                    
                    if (password_verify($password, $hashed_password)) {
                        // Login สำเร็จ
                        $_SESSION["loggedin"] = true;
                        $_SESSION["user_id"] = $row['user_id'];
                        $_SESSION["username"] = $row['username'];
                        
                        header("location: dashboard.php");
                        exit;
                    } else {
                        $login_error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
                    }
                }
            } else {
                $login_error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
            }
        } else {
            $login_error = "มีบางอย่างผิดพลาด กรุณาลองใหม่ภายหลัง";
        }
        unset($stmt);
    }
}

// --- ฟังก์ชันสำหรับ Register ---
function handleRegister($pdo, &$register_error, &$register_success) {
    $username = trim($_POST["reg_username"]);
    $password = trim($_POST["reg_password"]);
    $confirm_password = trim($_POST["reg_confirm_password"]);
    
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $register_error = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
        return;
    }
    
    if (strlen($password) < 6) {
        $register_error = "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
        return;
    }
    
    if ($password !== $confirm_password) {
        $register_error = "รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน";
        return;
    }
    
    // ตรวจสอบชื่อผู้ใช้ซ้ำ
    $sql = "SELECT user_id FROM users WHERE username = :username";
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        if ($stmt->execute() && $stmt->rowCount() > 0) {
            $register_error = "ชื่อผู้ใช้ **{$username}** มีคนใช้แล้ว";
            return;
        }
    }
    
    // บันทึกผู้ใช้ใหม่
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
    
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $register_success = "ลงทะเบียนสำเร็จแล้ว! คุณสามารถเข้าสู่ระบบได้เลย";
            $_POST = [];
        } else {
            $register_error = "มีบางอย่างผิดพลาดในการลงทะเบียน กรุณาลองใหม่ภายหลัง";
        }
        unset($stmt);
    }
}

// --- จัดการ POST Request ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action_login'])) {
        handleLogin($pdo, $login_error);
    } elseif (isset($_POST['action_register'])) {
        handleRegister($pdo, $register_error, $register_success);
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - เข้าสู่ระบบ/ลงทะเบียน</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            background-color: #f5f5f5;
        }
        main {
            flex: 1 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            width: 90%;
            max-width: 400px;
            margin: 20px;
        }
        /* *** ซ่อนแถบ Tabs header และ indicator (Login สลับกล่องเดียว) *** */
        .tabs {
            height: 0; 
            border-bottom: none !important;
            opacity: 0; 
            overflow: hidden; 
        }
        .tabs .tab a {
            display: none; 
        }
        .alert-container {
            margin-top: 10px;
        }
        .switch-link {
            text-align: center;
            margin-top: 20px;
            display: block;
            color: #26a69a;
            font-weight: 500;
        }
        .auth-title {
             font-size: 1.8rem;
             font-weight: 500;
             margin-bottom: 25px;
             color: #26a69a;
        }
        /* ปรับปรุง Input Field และ Button ให้เต็มความกว้าง */
        .input-field {
            margin-left: 0;
            margin-right: 0;
            width: 100% !important; 
        }
        .input-field input {
            width: calc(100% - 45px) !important; 
        }
        .input-field .prefix {
             top: 0.9rem; 
        }
        .btn {
            width: 100%; 
            font-size: 1.1rem;
            height: 45px;
            line-height: 45px;
            margin-top: 15px; 
        }
        .btn i {
            font-size: 1.3rem;
        }
        /* ปรับสีเมื่อ focus */
        .input-field input:focus + label,
        .input-field input:valid + label {
            color: #26a69a !important;
        }
        .input-field input:focus {
            border-bottom: 1px solid #26a69a !important;
            box-shadow: 0 1px 0 0 #26a69a !important;
        }
    </style>
</head>
<body>
    <main>
        <div class="card auth-card z-depth-3">
            <div class="card-content">
                
                <div class="row">
                    <div class="col s12">
                        <ul class="tabs">
                            <li class="tab col s6"><a class="active" href="#login-tab" id="tab-login-link">เข้าสู่ระบบ</a></li>
                            <li class="tab col s6"><a href="#register-tab" id="tab-register-link">ลงทะเบียน</a></li>
                        </ul>
                    </div>
                    
                    <div id="login-tab" class="col s12">
                        <p class="auth-title center-align">เข้าสู่ระบบ</p>
                        <div class="alert-container">
                        <?php if (!empty($login_error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $login_error; ?>
                            </div>
                        <?php endif; ?>
                        </div>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="hidden" name="action_login" value="1">
                            <div class="row">
                                <div class="input-field col s12">
                                    <i class="material-icons prefix">account_circle</i>
                                    <input id="login_username" type="text" name="login_username" class="validate" required value="<?php echo htmlspecialchars($_POST['login_username'] ?? ''); ?>">
                                    <label for="login_username">ชื่อผู้ใช้ / อีเมล</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-field col s12">
                                    <i class="material-icons prefix">lock</i>
                                    <input id="login_password" type="password" name="login_password" class="validate" required>
                                    <label for="login_password">รหัสผ่าน</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col s12 center-align">
                                    <button class="btn waves-effect waves-light teal darken-1" type="submit" name="action">
                                        เข้าสู่ระบบ <i class="material-icons right">send</i>
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <a href="#!" class="switch-link" onclick="switchForm('register')">
                            ยังไม่มีบัญชี? ลงทะเบียนใหม่ที่นี่
                        </a>
                    </div>
                    
                    <div id="register-tab" class="col s12">
                         <p class="auth-title center-align blue-text text-darken-1">ลงทะเบียนบัญชีใหม่</p>
                         <div class="alert-container">
                        <?php if (!empty($register_error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $register_error; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($register_success)): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $register_success; ?>
                            </div>
                        <?php endif; ?>
                        </div>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <input type="hidden" name="action_register" value="1">
                            <div class="row">
                                <div class="input-field col s12">
                                    <i class="material-icons prefix">account_box</i>
                                    <input id="reg_username" type="text" name="reg_username" class="validate" required value="<?php echo htmlspecialchars($_POST['reg_username'] ?? ''); ?>">
                                    <label for="reg_username">ชื่อผู้ใช้ / อีเมล</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-field col s12">
                                    <i class="material-icons prefix">vpn_key</i>
                                    <input id="reg_password" type="password" name="reg_password" class="validate" required>
                                    <label for="reg_password">รหัสผ่าน (อย่างน้อย 6 ตัวอักษร)</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-field col s12">
                                    <i class="material-icons prefix">lock_open</i>
                                    <input id="reg_confirm_password" type="password" name="reg_confirm_password" class="validate" required>
                                    <label for="reg_confirm_password">ยืนยันรหัสผ่าน</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col s12 center-align">
                                    <button class="btn waves-effect waves-light blue darken-1" type="submit">
                                        ลงทะเบียน <i class="material-icons right">person_add</i>
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <a href="#!" class="switch-link blue-text" onclick="switchForm('login')">
                            เป็นสมาชิกอยู่แล้ว? เข้าสู่ระบบ
                        </a>
                    </div>
                </div>
                
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        let tabsInstance;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialise Tabs (Materialize Component)
            var el = document.querySelectorAll('.tabs');
            tabsInstance = M.Tabs.init(el);
            
            // ฟังก์ชันที่ใช้ในการสลับ
            window.switchForm = function(target) {
                if (tabsInstance[0]) {
                     if (target === 'register') {
                        tabsInstance[0].select('register-tab');
                    } else if (target === 'login') {
                        tabsInstance[0].select('login-tab');
                    }
                }
            };
            
            // ตรวจสอบสถานะการลงทะเบียนหลังการ Submit
            <?php if (!empty($register_error) || !empty($register_success)): ?>
                // หากมี Error หรือ Success ให้แสดงกล่อง Register
                if (tabsInstance[0]) {
                    tabsInstance[0].select('register-tab');
                }
                
                <?php if (!empty($register_success)): ?>
                    // หากสำเร็จ ให้แสดง SweetAlert แล้วสลับไปหน้า Login
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: '<?php echo $register_success; ?>',
                        confirmButtonText: 'เข้าสู่ระบบ'
                    }).then((result) => {
                        if (tabsInstance[0]) {
                             tabsInstance[0].select('login-tab');
                        }
                    });
                <?php endif; ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>