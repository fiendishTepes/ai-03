<?php 
// about.php
include 'includes/header.php'; 
?>

<div class="row">
    <div class="col s12">
        <h2><i class="material-icons">help_outline</i> คู่มือการใช้งานและจัดการโปรแกรม</h2>
        <div class="divider"></div>
        <div class="card-panel z-depth-1">
            <span class="card-title black-text">โปรแกรม: <?php echo APP_NAME; ?></span>
            
            <p>โปรแกรมนี้ถูกสร้างขึ้นในรูปแบบ **Modular PHP** เพื่อให้ง่ายต่อการดูแลรักษา **คุณไม่จำเป็นต้องมีความรู้ด้านการเขียนโค้ดเพื่อใช้งานหรือปรับแต่งการตั้งค่าเบื้องต้น**</p>

            <ul class="collection with-header">
                <li class="collection-header teal white-text"><h5>1. คู่มือการใช้งานเมนูหลัก (สำหรับผู้ใช้ทั่วไป)</h5></li>
                
                <li class="collection-item">
                    <div>
                        <i class="material-icons left">list_alt</i> <strong>จัดการรายรับ-จ่าย (Transactions)</strong>
                        <p>ใช้สำหรับ **บันทึก** รายการรายรับ/รายจ่ายประจำวัน. ในส่วนนี้คุณสามารถ **เพิ่ม** และ **ลบ** รายการล่าสุดได้ทันที</p>
                    </div>
                </li>
                <li class="collection-item">
                    <div>
                        <i class="material-icons left">bar_chart</i> <strong>สรุปยอด (Summary)</strong>
                        <p>ใช้สำหรับดูสรุปภาพรวมทางการเงินแบบ **รายเดือน** โดยจะแสดงยอดรวม กราฟสัดส่วนรายจ่าย และตารางรายการธุรกรรมทั้งหมดในเดือนที่คุณเลือก</p>
                    </div>
                </li>
                <li class="collection-item">
                    <div>
                        <i class="material-icons left">savings</i> <strong>ออมเงิน (Saving)</strong>
                        <p>ใช้สำหรับ **สร้างกระปุกออมสิน** ตั้งเป้าหมายเงินรวมและกำหนดระยะเวลา ระบบจะคำนวณยอดเงินที่ควรเก็บต่อวัน/เดือน/ปีให้คุณโดยอัตโนมัติ</p>
                    </div>
                </li>
                <li class="collection-item">
                    <div>
                        <i class="material-icons left">cloud_download</i> <strong>ส่งออก (Export)</strong>
                        <p>ใช้สำหรับส่งออกข้อมูลรายการธุรกรรมทั้งหมดของคุณเป็นไฟล์ **CSV** ซึ่งสามารถเปิดและจัดการได้ง่ายในโปรแกรม Microsoft Excel หรือ Google Sheets</p>
                    </div>
                </li>
            </ul>

            <ul class="collection with-header">
                <li class="collection-header blue darken-1 white-text"><h5>2. คู่มือการจัดการและปรับปรุงโปรแกรม (สำหรับผู้ดูแลระบบ)</h5></li>
                
                <li class="collection-item">
                    <div>
                        <i class="material-icons left">settings</i> <strong>การตั้งค่าการเชื่อมต่อฐานข้อมูล (DB Config)</strong>
                        <p>การเปลี่ยนแปลงที่อยู่เซิร์ฟเวอร์, ชื่อผู้ใช้, หรือรหัสผ่านฐานข้อมูล ต้องทำในไฟล์ **<code>config/db_config.php</code>** เท่านั้น หากคุณย้ายโฮสต์หรือเปลี่ยนรหัสผ่าน MySQL ต้องแก้ไขไฟล์นี้</p>
                        <blockquote class="fw-bold">
                            ไฟล์ที่เกี่ยวข้อง: <code>config/db_config.php</code>
                        </blockquote>
                    </div>
                </li>
                <li class="collection-item">
                    <div>
                        <i class="material-icons left">info_outline</i> <strong>โครงสร้างหลักของโปรแกรม (Framework Structure)</strong>
                        <p>โปรแกรมถูกแยกออกเป็นส่วนๆ ดังนี้:</p>
                        <ul>
                            <li>**Pages (เช่น <code>dashboard.php</code>, <code>summary.php</code>):** คือส่วนหน้าจอที่ผู้ใช้เห็น (View) และเป็นตัวเรียกใช้ JavaScript</li>
                            <li>**API (<code>api/*.php</code>):** คือส่วนหลังบ้าน (Controller) ทำหน้าที่รับคำสั่งจาก Pages ผ่าน AJAX และจัดการข้อมูลกับฐานข้อมูล</li>
                            <li>**Includes (<code>includes/*.php</code>):** ส่วนที่ใช้ซ้ำ (เช่น Header/Footer/DB Connection)</li>
                        </ul>
                    </div>
                </li>
            </ul>
            
            <ul class="collection with-header">
                <li class="collection-header green darken-1 white-text"><h5>3. วิธีการเพิ่มโมดูลใหม่ (เช่น การจัดการหนี้)</h5></li>
                <li class="collection-item">
                    <p>การเพิ่มฟังก์ชันใหม่ให้ทำตามหลักการ Modular โดยเริ่มจากหลังบ้านไปหน้าบ้าน:</p>
                    <ol>
                        <li>**สร้างตารางใหม่ในฐานข้อมูล:** เขียนโค้ด SQL เพื่อสร้างตารางสำหรับฟังก์ชันใหม่ (เช่น <code>debts</code>)</li>
                        <li>**สร้างไฟล์ API:** สร้างไฟล์ PHP ใหม่ในโฟลเดอร์ **<code>api/</code>** (เช่น <code>api/debts.php</code>) เพื่อเขียน Logic การเพิ่ม, อ่าน, แก้ไข, ลบ ข้อมูลจากตารางใหม่นี้</li>
                        <li>**สร้างหน้าจอ:** สร้างไฟล์ PHP ใหม่ในระดับรูท (เช่น **<code>debts.php</code>**) และใช้ HTML/JavaScript เพื่อออกแบบหน้าจอและเขียนโค้ด AJAX สำหรับติดต่อกับ <code>api/debts.php</code></li>
                        <li>**เพิ่มเมนู:** แก้ไขไฟล์ **<code>includes/header.php</code>** เพื่อเพิ่มลิงก์ไปยัง <code>debts.php</code> ในแถบเมนู (Navbar) และเมนูสำหรับมือถือ (Sidenav)</li>
                    </ol>
                    <blockquote class="fw-bold">
                        การทำแบบนี้ทำให้หากส่วนใดส่วนหนึ่งมีปัญหา (เช่น โมดูลหนี้) ส่วนอื่นๆ ของโปรแกรม (เช่น รายรับ-รายจ่าย) จะยังคงทำงานได้ปกติ
                    </blockquote>
                </li>
            </ul>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>