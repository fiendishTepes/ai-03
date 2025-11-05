<?php 
// transactions.php
include 'includes/header.php'; 
?>

<div class="row">
    <div class="col s12">
        <h2><i class="material-icons">list_alt</i> จัดการรายรับ-รายจ่าย</h2>
        <div class="divider"></div>
    </div>
</div>

<div class="row">
    <div class="col s12 m4">
        <div class="card-panel z-depth-1">
            <span class="card-title black-text">เพิ่มรายการใหม่</span>
            <form id="transaction-form">
                
                <div class="row mb-3">
                    <label>ประเภทรายการ</label>
                    <div class="col s6">
                        <label>
                            <input name="type" type="radio" value="income" checked />
                            <span><i class="material-icons green-text">trending_up</i> รายรับ</span>
                        </label>
                    </div>
                    <div class="col s6">
                        <label>
                            <input name="type" type="radio" value="expense" />
                            <span><i class="material-icons red-text">trending_down</i> รายจ่าย</span>
                        </label>
                    </div>
                </div>

                <div class="row">
                    <div class="input-field col s12">
                        <i class="material-icons prefix">date_range</i>
                        <input id="transaction_date" name="transaction_date" type="text" class="datepicker" required value="<?php echo date('Y-m-d'); ?>">
                        <label for="transaction_date">วันที่ทำรายการ</label>
                    </div>
                </div>
                
                <div class="row">
                    <div class="input-field col s12">
                        <i class="material-icons prefix">attach_money</i>
                        <input id="amount" name="amount" type="number" step="0.01" min="0.01" class="validate" required>
                        <label for="amount">จำนวนเงิน (฿)</label>
                    </div>
                </div>
                
                <div class="row">
                    <div class="input-field col s12">
                        <i class="material-icons prefix">category</i>
                        <select id="category_id" name="category_id" required>
                            <option value="" disabled selected>เลือกหมวดหมู่</option>
                            </select>
                        <label for="category_id">หมวดหมู่</label>
                    </div>
                </div>

                <div class="row">
                    <div class="input-field col s12">
                        <i class="material-icons prefix">description</i>
                        <input id="description" name="description" type="text">
                        <label for="description">รายละเอียด (ไม่จำเป็น)</label>
                    </div>
                </div>

                <div class="row">
                    <div class="col s12 center-align">
                        <button class="btn waves-effect waves-light teal darken-1" type="submit">
                            บันทึกรายการ <i class="material-icons right">save</i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col s12 m8">
        <div class="card-panel z-depth-1">
            <span class="card-title black-text">รายการล่าสุด 100 รายการ</span>
            <div class="table-responsive">
                <table class="striped responsive-table" id="transaction-table">
                    <thead>
                        <tr>
                            <th>วันที่</th>
                            <th>ประเภท</th>
                            <th>จำนวนเงิน (฿)</th>
                            <th>หมวดหมู่</th>
                            <th>รายละเอียด</th>
                            <th>ลบ</th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
            </div>
            <p class="center-align" id="loading-message">กำลังโหลดข้อมูล...</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Initialise Select and Datepicker (Materialize Components)
    $('select').formSelect();
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        i18n: {
             cancel: 'ยกเลิก', clear: 'ล้าง', done: 'ตกลง', 
             months: ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'],
             weekdaysShort: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
        }
    });
    
    // ดึงข้อมูลประเภทรายการและรายการธุรกรรมเริ่มต้น
    fetchTransactionsAndCategories();

    // ฟังการเปลี่ยนแปลงของ Radio Button เพื่ออัปเดตตัวเลือกหมวดหมู่
    $('input[name="type"]').on('change', function() {
        populateCategories($('input[name="type"]:checked').val());
    });

    // Submits Form (กลับไปใช้ AJAX + JSON)
    $('#transaction-form').on('submit', function(e) {
        e.preventDefault();
        
        const type = $('input[name="type"]:checked').val();
        const amount = $('#amount').val();
        const category_id = $('#category_id').val(); // ค่าจะเป็น string ('') ถ้าไม่มีการเลือก
        const description = $('#description').val();
        const transaction_date = $('#transaction_date').val();
        
        // Validation ฝั่ง Frontend
        if (!type || parseFloat(amount) <= 0 || category_id === '' || !transaction_date) {
            Swal.fire('ข้อผิดพลาด', 'กรุณากรอกข้อมูลให้ครบถ้วนและจำนวนเงินต้องมากกว่า 0', 'error');
            return;
        }

        // ใช้ JSON Format
        const formData = {
            type: type,
            amount: parseFloat(amount),
            category_id: category_id, // ส่งค่าเป็น string ไปก่อน PHP จะจัดการแปลงเป็น null หรือ int
            description: description,
            transaction_date: transaction_date
        };

        $.ajax({
            url: 'api/transactions.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    Swal.fire('สำเร็จ!', response.message, 'success');
                    $('#transaction-form')[0].reset();
                    // ตั้งค่า Datepicker ใหม่เป็นวันปัจจุบัน
                    M.Datepicker.getInstance(document.getElementById('transaction_date')).setDate(new Date()); 
                    
                    // ต้องเลือก option ที่มี value='' (disabled selected) หรือรีโหลด select
                    $('#category_id').val(''); 
                    $('select').formSelect(); 

                    fetchTransactionsAndCategories(); // โหลดข้อมูลใหม่
                } else {
                    Swal.fire('ข้อผิดพลาด', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                 Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ' + xhr.responseText, 'error');
            }
        });
    });

    // ลบรายการ (โค้ดเดิม)
    $(document).on('click', '.delete-btn', function() {
        const transactionId = $(this).data('id');
        
        Swal.fire({
            title: 'คุณแน่ใจหรือไม่?',
            text: "คุณต้องการลบรายการนี้ใช่หรือไม่?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/transactions.php?id=' + transactionId,
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('ลบสำเร็จ!', response.message, 'success');
                            fetchTransactionsAndCategories(); // โหลดข้อมูลใหม่
                        } else {
                            Swal.fire('ข้อผิดพลาด', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                    }
                });
            }
        });
    });
    
    let allCategories = [];
    
    // ฟังก์ชันดึงรายการธุรกรรมและประเภท
    function fetchTransactionsAndCategories() {
        $('#loading-message').show();
        $.ajax({
            url: 'api/transactions.php?limit=100', // ดึง 100 รายการล่าสุด
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#loading-message').hide();
                if (response.success) {
                    allCategories = response.categories;
                    populateCategories($('input[name="type"]:checked').val()); // เติมหมวดหมู่เริ่มต้น
                    renderTransactions(response.transactions); // แสดงรายการ
                } else {
                    Swal.fire('ข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลได้: ' + response.message, 'error');
                }
            },
            error: function() {
                $('#loading-message').text('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์เพื่อโหลดข้อมูลได้');
                Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
            }
        });
    }

    // ฟังก์ชันเติมตัวเลือกหมวดหมู่
    function populateCategories(type) {
        const select = $('#category_id');
        select.empty();
        select.append('<option value="" disabled selected>เลือกหมวดหมู่</option>');
        
        allCategories.forEach(function(cat) {
            if (cat.type === type) {
                select.append(`<option value="${cat.category_id}">${cat.category_name}</option>`);
            }
        });
        
        // ต้องเรียก formSelect ใหม่หลังจากเปลี่ยนตัวเลือก
        select.formSelect();
    }
    
    // ฟังก์ชันแสดงรายการในตาราง
    function renderTransactions(transactions) {
        const tbody = $('#transaction-table tbody');
        tbody.empty();

        if (transactions.length === 0) {
            tbody.append('<tr><td colspan="6" class="center-align">ไม่พบรายการรายรับ-รายจ่าย</td></tr>');
            return;
        }

        transactions.forEach(function(item) {
            const typeClass = item.type === 'income' ? 'green-text' : 'red-text';
            const typeText = item.type === 'income' ? 'รายรับ' : 'รายจ่าย';
            const deleteBtn = `<a href="#!" class="delete-btn red-text" data-id="${item.transaction_id}"><i class="material-icons">delete_forever</i></a>`;
            
            // แปลงวันที่ให้อยู่ในรูปแบบไทย (DD/MM/YYYY)
            const dateParts = item.transaction_date.split('-');
            const formattedDate = dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0];

            const row = `
                <tr>
                    <td>${formattedDate}</td>
                    <td class="${typeClass}">${typeText}</td>
                    <td>${parseFloat(item.amount).toLocaleString('th-TH', { minimumFractionDigits: 2 })}</td>
                    <td>${item.category_name || '-'}</td>
                    <td>${item.description || '-'}</td>
                    <td>${deleteBtn}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }
});
</script>