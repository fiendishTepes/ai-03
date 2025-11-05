<?php 
// dashboard.php
// includes/db.php ถูกเรียกก่อนหน้านี้เพื่อตั้งค่า Timezone แล้ว
include 'includes/header.php'; 

// กำหนดปีปัจจุบัน
$current_year = date('Y');

// ดึงข้อมูลสรุปยอดรายวันและยอดรวม (ประจำปี)
try {
    $user_id = $_SESSION['user_id'];
    $today = date('Y-m-d'); // ใช้ Timezone ที่ถูกต้องแล้วจาก includes/db.php

    // 1. ยอดรวมรายรับ-รายจ่ายวันนี้ (ใช้สำหรับการ์ด 'สรุปยอดประจำวันที่')
    $sql_daily = "SELECT 
                    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS total_income_today,
                    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS total_expense_today
                  FROM transactions 
                  WHERE user_id = :user_id AND transaction_date = :today";
    $stmt_daily = $pdo->prepare($sql_daily);
    $stmt_daily->bindParam(':user_id', $user_id);
    $stmt_daily->bindParam(':today', $today);
    $stmt_daily->execute();
    $daily_summary = $stmt_daily->fetch();
    $net_daily_balance = $daily_summary['total_income_today'] - $daily_summary['total_expense_today'];
    
    // 2. ยอดรวมรายรับ-รายจ่ายทั้งหมด (ประจำปีปัจจุบัน)
    $sql_total = "SELECT 
                    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS total_income,
                    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS total_expense
                  FROM transactions 
                  WHERE user_id = :user_id AND YEAR(transaction_date) = YEAR(CURDATE())"; 
    $stmt_total = $pdo->prepare($sql_total);
    $stmt_total->bindParam(':user_id', $user_id);
    $stmt_total->execute();
    $total_summary = $stmt_total->fetch();

    $net_balance = $total_summary['total_income'] - $total_summary['total_expense'];

} catch (PDOException $e) {
    echo "<p class='alert alert-danger'>Error: " . $e->getMessage() . "</p>";
    $daily_summary = ['total_income_today' => 0, 'total_expense_today' => 0];
    $total_summary = ['total_income' => 0, 'total_expense' => 0];
    $net_balance = 0;
    $net_daily_balance = 0; 
}
?>

<div class="row">
    <div class="col s12">
        <h2><i class="material-icons">dashboard</i> Dashboard ประจำปี <?php echo $current_year; ?></h2>
        <div class="divider"></div>
    </div>
</div>

<div class="row">
    <div class="col s12 m4">
        <div class="card-panel teal lighten-1 white-text center-align hoverable">
            <i class="material-icons large">arrow_upward</i>
            <h5>รายรับรวม</h5>
            <p class="flow-text">฿ <?php echo number_format($total_summary['total_income'], 2); ?></p>
        </div>
    </div>
    <div class="col s12 m4">
        <div class="card-panel red lighten-1 white-text center-align hoverable">
            <i class="material-icons large">arrow_downward</i>
            <h5>รายจ่ายรวม</h5>
            <p class="flow-text">฿ <?php echo number_format($total_summary['total_expense'], 2); ?></p>
        </div>
    </div>
    <div class="col s12 m4">
        <div class="card-panel blue darken-1 white-text center-align hoverable">
            <i class="material-icons large">account_balance</i>
            <h5>คงเหลือสุทธิ</h5>
            <p class="flow-text">฿ <?php echo number_format($net_balance, 2); ?></p>
        </div>
    </div>
</div>

<div class="row" id="special-expense-cards">
    <div class="col s12 m6">
         <div class="card-panel light-blue lighten-5 center-align z-depth-1">
             <i class="material-icons blue-text">opacity</i>
             <p class="mb-0 black-text" style="font-weight: 500;">ค่าน้ำรวม (ปีนี้):</p>
             <h5 class="blue-text" id="expense_ค่าน้ำ">฿ 0.00</h5>
         </div>
    </div>
    <div class="col s12 m6">
         <div class="card-panel amber lighten-5 center-align z-depth-1">
             <i class="material-icons amber-text text-darken-4">flash_on</i>
             <p class="mb-0 black-text" style="font-weight: 500;">ค่าไฟรวม (ปีนี้):</p>
             <h5 class="amber-text text-darken-4" id="expense_ค่าไฟ">฿ 0.00</h5>
         </div>
    </div>
</div>

<div class="row">
    <div class="col s12">
        <div class="card-panel grey lighten-4 z-depth-1">
            <span class="card-title">สรุปยอดประจำวันที่: **<?php echo date('d/m/Y'); ?>**</span>
            <div class="row">
                <div class="col s12 m4">
                    <p>รายรับวันนี้: <strong class="teal-text">฿ <?php echo number_format($daily_summary['total_income_today'], 2); ?></strong></p>
                </div>
                <div class="col s12 m4">
                    <p>รายจ่ายวันนี้: <strong class="red-text">฿ <?php echo number_format($daily_summary['total_expense_today'], 2); ?></strong></p>
                </div>
                <?php 
                    $daily_balance_color = ($net_daily_balance >= 0) ? 'blue-text text-darken-1' : 'red-text text-darken-1';
                ?>
                <div class="col s12 m4">
                    <p>คงเหลือวันนี้: <strong class="<?php echo $daily_balance_color; ?>">฿ <?php echo number_format($net_daily_balance, 2); ?></strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col s12">
        <div class="card z-depth-1">
            <div class="card-content">
                <span class="card-title">กราฟรายรับ-รายจ่าย 7 วันล่าสุด</span>
                <canvas id="dailyChart" width="400" height="150"></canvas>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // ฟังก์ชันดึงข้อมูลสำหรับกราฟ 7 วันล่าสุด และข้อมูลพิเศษ
    $.ajax({
        url: 'api/dashboard_data.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                renderDailyChart(data.labels, data.incomeData, data.expenseData);
                updateSpecialExpenseCards(data.specialExpenses); 
            } else {
                console.error("Error fetching dashboard data:", data.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
        }
    });

    // ฟังก์ชันอัปเดต Card ค่าน้ำ/ค่าไฟ
    function updateSpecialExpenseCards(specialExpenses) {
        // อัปเดตค่าน้ำ
        $('#expense_ค่าน้ำ').text(`฿ ${specialExpenses['ค่าน้ำ'].toLocaleString('th-TH', { minimumFractionDigits: 2 })}`);
        // อัปเดตค่าไฟ
        $('#expense_ค่าไฟ').text(`฿ ${specialExpenses['ค่าไฟ'].toLocaleString('th-TH', { minimumFractionDigits: 2 })}`);
    }

    // ฟังก์ชันสร้างกราฟ (โค้ดเดิม)
    function renderDailyChart(labels, incomeData, expenseData) {
        const ctx = document.getElementById('dailyChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'รายรับ (฿)',
                        data: incomeData,
                        backgroundColor: 'rgba(38, 166, 154, 0.7)', 
                        borderColor: 'rgba(38, 166, 154, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'รายจ่าย (฿)',
                        data: expenseData,
                        backgroundColor: 'rgba(239, 83, 80, 0.7)', 
                        borderColor: 'rgba(239, 83, 80, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'รายรับ-รายจ่าย 7 วันล่าสุด'
                    }
                }
            }
        });
    }
});
</script>