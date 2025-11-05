<?php 
// export.php
include 'includes/header.php'; 
?>

<div class="row">
    <div class="col s12">
        <h2><i class="material-icons">cloud_download</i> ส่งออกข้อมูล</h2>
        <div class="divider"></div>
        <p class="flow-text">ส่งออกรายการรายรับ-รายจ่ายทั้งหมดเป็นไฟล์ CSV/Excel (ไฟล์ CSV สามารถเปิดใน Excel ได้)</p>
    </div>
</div>

<div class="row">
    <div class="col s12 m6">
        <div class="card-panel z-depth-1">
            <span class="card-title black-text">ส่งออกข้อมูลธุรกรรม</span>
            <form action="api/export_data.php" method="POST">
                <p>คลิกที่ปุ่มด้านล่างเพื่อดาวน์โหลดรายการธุรกรรมทั้งหมดของคุณ</p>
                <div class="col s12 center-align">
                    <button class="btn waves-effect waves-light green darken-1" type="submit" name="export">
                        ส่งออกเป็น CSV/Excel <i class="material-icons right">file_download</i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col s12 m6">
        <div class="card-panel z-depth-1">
            <span class="card-title black-text">นำเข้าข้อมูล (สร้างไว้ค่อยทำ)</span>
             <p>ระบบนำเข้าข้อมูลจะเปิดใช้งานเร็วๆ นี้</p>
             <button class="btn waves-effect waves-light grey darken-1" onclick="featureComingSoon('นำเข้าข้อมูล (Import)')" disabled>
                นำเข้าจาก CSV/Excel <i class="material-icons right">file_upload</i>
            </button>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>