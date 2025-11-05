<?php 
// debt.php
include 'includes/header.php'; 
?>

<div class="row">
    <div class="col s12">
        <h2><i class="material-icons">credit_card</i> จัดการหนี้</h2>
        <div class="divider"></div>
    </div>
</div>

<div class="row">
    <div class="col s12 m4">
        <div class="card-panel z-depth-1">
            <span class="card-title black-text">บันทึกหนี้ใหม่</span>
            <form id="debt-form" enctype="multipart/form-data">
                
                <div class="input-field">
                    <i class="material-icons prefix">assignment</i>
                    <input id="item_name" name="item_name" type="text" class="validate" required>
                    <label for="item_name">ชื่อรายการหนี้</label>
                </div>

                <div class="input-field">
                    <i class="material-icons prefix">person_pin</i>
                    <input id="owner_name" name="owner_name" type="text" class="validate" required>
                    <label for="owner_name">ชื่อเจ้าของหนี้</label>
                </div>

                <div class="input-field">
                    <i class="material-icons prefix">attach_money</i>
                    <input id="principal_amount" name="principal_amount" type="number" step="0.01" min="1" class="validate" required>
                    <label for="principal_amount">วงเงินหนี้ (บาท)</label>
                </div>
                
                <div class="input-field">
                    <i class="material-icons prefix">date_range</i>
                    <input id="total_months" name="total_months" type="number" min="1" class="validate" required>
                    <label for="total_months">กำหนดผ่อนกี่เดือน</label>
                </div>
                
                <div class="divider"></div>
                <h6><i class="material-icons tiny">calculate</i> วิธีคำนวณยอดจ่าย</h6>
                
                <div class="row mb-3">
                    <div class="col s12">
                        <label>
                            <input name="interest_type" type="radio" value="interest_rate" checked />
                            <span>ดอกเบี้ยต่อปี (%)</span>
                        </label>
                    </div>
                    <div class="col s12">
                        <label>
                            <input name="interest_type" type="radio" value="user_payment" />
                            <span>กำหนดยอดจ่ายเองต่อเดือน (ดอกเบี้ย 0%)</span>
                        </label>
                    </div>
                </div>

                <div class="input-field" id="interest-rate-field">
                    <i class="material-icons prefix">percent</i>
                    <input id="interest_rate" name="interest_rate" type="number" step="0.01" min="0" value="0" class="validate">
                    <label for="interest_rate">ดอกเบี้ยต่อปี (%)</label>
                </div>

                <div class="input-field" id="user-payment-field" style="display: none;">
                    <i class="material-icons prefix">payment</i>
                    <input id="monthly_payment_user" name="monthly_payment_user" type="number" step="0.01" min="1" class="validate">
                    <label for="monthly_payment_user">ยอดจ่ายเองต่อเดือน (บาท)</label>
                </div>

                <div class="input-field">
                    <i class="material-icons prefix">calendar_today</i>
                    <input id="due_day" name="due_day" type="number" min="1" max="31" class="validate" required>
                    <label for="due_day">กำหนดวันจ่ายทุกวันที่ (1-31)</label>
                </div>
                
                <div class="input-field">
                    <i class="material-icons prefix">check_circle_outline</i>
                    <input id="paid_months" name="paid_months" type="number" min="0" value="0" class="validate">
                    <label for="paid_months">จ่ายไปแล้วกี่เดือน (ค่าเริ่มต้น 0)</label>
                </div>

                <div class="file-field input-field row">
                    <div class="btn waves-effect waves-light blue-grey lighten-1 col s12">
                        <span><i class="material-icons left">photo</i> อัปโหลดรูป (หลักฐาน)</span>
                        <input type="file" name="debt_image" id="debt_image" accept="image/jpeg,image/png,image/gif">
                    </div>
                    <div class="file-path-wrapper col s12">
                        <input class="file-path validate" type="text" placeholder="เลือกไฟล์ (ไม่จำเป็น)">
                    </div>
                </div>

                <div class="input-field">
                    <i class="material-icons prefix">comment</i>
                    <textarea id="notes" name="notes" class="materialize-textarea"></textarea>
                    <label for="notes">หมายเหตุ</label>
                </div>

                <div class="row">
                    <div class="col s12 center-align">
                        <button class="btn waves-effect waves-light pink darken-1" type="submit">
                            บันทึกหนี้ <i class="material-icons right">add_circle_outline</i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col s12 m8">
        <div class="card-panel z-depth-1">
            <span class="card-title black-text">รายการหนี้ทั้งหมดที่ต้องจัดการ</span>

            <div class="row">
                <div class="input-field col s12">
                    <i class="material-icons prefix">search</i>
                    <input id="debt-search" type="text" class="validate">
                    <label for="debt-search">ค้นหารายการ/เจ้าของหนี้</label>
                </div>
            </div>
            <div class="divider"></div>

            <div id="debt-list" class="row">
                <p class="center-align" id="loading-debts">กำลังโหลดรายการหนี้...</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    
    // Global variable to store all debts fetched from the API
    let allDebts = [];

    // --- UI Logic (ดอกเบี้ย vs ยอดจ่ายเอง) ---
    $('input[name="interest_type"]').on('change', function() {
        const type = $(this).val();
        if (type === 'interest_rate') {
            $('#interest-rate-field').show();
            $('#user-payment-field').hide();
            $('#interest_rate').attr('required', true).val(0);
            $('#monthly_payment_user').attr('required', false).val('');
        } else if (type === 'user_payment') {
            $('#interest-rate-field').hide();
            $('#user-payment-field').show();
            $('#interest_rate').attr('required', false).val(0);
            $('#monthly_payment_user').attr('required', true);
        }
    }).trigger('change');

    // --- Core Logic ---
    fetchDebts();
    
    // Event Listener สำหรับการค้นหาแบบ Real-time
    $('#debt-search').on('input', function() {
        const query = $(this).val().toLowerCase();
        filterDebts(query);
    });

    // Submit Form
    $('#debt-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const interestType = formData.get('interest_type');
        const monthlyPaymentUser = parseFloat(formData.get('monthly_payment_user')) || 0;

        if (interestType === 'user_payment') {
            formData.set('interest_rate', 0);
            formData.set('is_interest_fixed', 0);
             if (monthlyPaymentUser <= 0) {
                Swal.fire('ข้อผิดพลาด', 'กรุณากำหนดยอดจ่ายเองต่อเดือนที่ถูกต้อง', 'error');
                return;
            }
        } else {
            formData.set('monthly_payment_user', 0);
            formData.set('is_interest_fixed', 1);
        }
        

        Swal.fire({ title: 'กำลังบันทึก...', text: 'กำลังบันทึกรายการหนี้', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        $.ajax({
            url: 'api/debts.php',
            type: 'POST',
            data: formData,
            contentType: false, 
            processData: false, 
            success: function(response) {
                Swal.close();
                if (response.success) {
                    Swal.fire('สำเร็จ!', response.message, 'success');
                    $('#debt-form')[0].reset();
                    $('input[name="interest_type"][value="interest_rate"]').prop('checked', true).trigger('change');
                    fetchDebts(); // Reload all debts
                } else {
                    Swal.fire('ข้อผิดพลาด', response.message, 'error');
                }
            },
            error: function(xhr) {
                 Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ' + xhr.responseText, 'error');
            }
        });
    });

    // --- Fetch & Render Debts ---
    function fetchDebts() {
        $('#loading-debts').show();
        $('#debt-list').empty(); // Clear list before loading
        
        return $.ajax({
            url: 'api/debts.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#loading-debts').hide();
                if (response.success) {
                    allDebts = response.debts; 
                    renderDebts(allDebts); // Render all initially
                } else {
                    $('#debt-list').html('<p class="red-text center-align">ไม่สามารถโหลดรายการหนี้ได้: ' + response.message + '</p>');
                }
            },
            error: function() {
                $('#loading-debts').text('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์เพื่อโหลดข้อมูลได้');
            }
        });
    }
    
    // *** ฟังก์ชันสำหรับกรองหนี้ (Real-time Filter) ***
    function filterDebts(query) {
        if (query === "") {
            renderDebts(allDebts); // แสดงทั้งหมดหากไม่มี query
            return;
        }

        const filteredDebts = allDebts.filter(debt => {
            const itemName = debt.item_name ? debt.item_name.toLowerCase() : '';
            const ownerName = debt.owner_name ? debt.owner_name.toLowerCase() : '';

            return itemName.includes(query) || ownerName.includes(query);
        });

        renderDebts(filteredDebts);
    }

    // --- Render Debts to Cards (แสดงรายการทั้งหมด) ---
    function renderDebts(debts) {
        const listDiv = $('#debt-list');
        listDiv.empty();

        if (debts.length === 0) {
            listDiv.html('<p class="center-align">ไม่พบรายการหนี้</p>');
            return;
        }

        const today = new Date();
        const currentDay = today.getDate();

        debts.forEach(function(debt) {
            
            const monthlyPayment = debt.monthly_payment_effective; 
            const effectiveMonths = debt.total_months_effective; 
            
            const percentage = (debt.paid_months / effectiveMonths) * 100;
            const progressWidth = Math.min(percentage, 100);

            // Logic สีตามวันจ่าย/สถานะ
            let colorClass = 'blue-grey'; 
            const daysRemaining = (debt.due_day > currentDay) ? (debt.due_day - currentDay) : (debt.due_day - currentDay + 30);
            
            if (debt.paid_months >= effectiveMonths) {
                 colorClass = 'green darken-2'; // จ่ายครบแล้ว
            } else if (daysRemaining <= 3 && daysRemaining >= 1) {
                colorClass = 'red lighten-1'; 
            } else if (daysRemaining <= 7 && daysRemaining >= 4) {
                colorClass = 'amber lighten-1'; 
            } else {
                 colorClass = 'blue-grey lighten-1'; // ปกติ
            }
            
            const isPaidOff = debt.paid_months >= effectiveMonths;
            const canUndo = debt.paid_months > 0;
            
            const payButton = `<a href="#!" class="btn-small green darken-3 save-money-btn ${isPaidOff ? 'disabled' : ''}" data-id="${debt.debt_id}" data-action="pay">จ่ายแล้ว</a>`;
            const undoButton = `<a href="#!" class="btn-small grey darken-1 save-money-btn ${canUndo ? '' : 'disabled'}" data-id="${debt.debt_id}" data-action="undo">ยกเลิกจ่าย</a>`;
            
            // *** NOTE: กลับไปใช้ col s12 m6 เพื่อให้แสดง 2 รายการต่อแถว ***
            const debtCard = `
                <div class="col s12 m6"> 
                    <div class="card ${colorClass} white-text z-depth-2 hoverable">
                        <div class="card-content">
                            <span class="card-title"><i class="material-icons left">local_atm</i> ${debt.item_name}</span>
                            <p><strong>เจ้าของ:</strong> ${debt.owner_name}</p>
                            <p><strong>วงเงิน:</strong> ฿ ${debt.principal_amount.toLocaleString('th-TH', { minimumFractionDigits: 2 })}</p>
                            <p><strong>ยอดจ่าย/เดือน:</strong> ฿ ${monthlyPayment.toLocaleString('th-TH', { minimumFractionDigits: 2 })}</p>
                            <p><strong>วันครบกำหนด:</strong> วันที่ ${debt.due_day} ของทุกเดือน</p>
                            <p><strong>สถานะ:</strong> ${isPaidOff ? 'ชำระครบแล้ว' : `ผ่อน ${debt.paid_months}/${effectiveMonths} เดือน`}</p>
                            
                            <div class="divider white" style="margin: 10px 0;"></div>
                            
                            <p class="mb-1">ผ่อนไปแล้ว: ${debt.paid_months}/${effectiveMonths} งวด (จากเป้าหมายเดิม ${debt.total_months})</p>
                            <div class="progress grey lighten-3" style="height: 15px; margin-top: 5px;">
                                <div class="determinate white" style="width: ${progressWidth}%"></div>
                            </div>
                            <p class="right-align fw-bold">${progressWidth.toFixed(2)}%</p>
                            
                            <div class="card-action p-0 pt-2" style="background-color: transparent; border-top: none;">
                                ${payButton}
                                ${undoButton}
                                <a href="#!" class="btn-small red darken-3 delete-debt-btn" data-id="${debt.debt_id}">ลบ</a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            listDiv.append(debtCard);
        });
    }


    // --- Pay/Undo Logic (ใช้ fetchDebts เพื่อรีโหลดรายการทั้งหมด) ---
    $(document).on('click', '.save-money-btn', function() {
        const debtId = $(this).data('id');
        const action = $(this).data('action');
        const actionText = action === 'pay' ? 'บันทึกการจ่าย' : 'ยกเลิกการจ่าย';

        if ($(this).hasClass('disabled')) return; 

        Swal.fire({
            title: `${actionText}หนี้?`,
            icon: action === 'pay' ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/debts.php',
                    type: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify({ debt_id: debtId, action: action }),
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('สำเร็จ!', response.message, 'success');
                            // โหลดข้อมูลทั้งหมดใหม่และใช้ filter เพื่อคงผลการค้นหา
                            const currentQuery = $('#debt-search').val();
                            fetchDebts().done(function() {
                                filterDebts(currentQuery.toLowerCase());
                            });
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

    // --- Delete Logic ---
    $(document).on('click', '.delete-debt-btn', function() {
        const debtId = $(this).data('id');
        Swal.fire({
            title: 'ยืนยันการลบหนี้?',
            text: "หนี้จะถูกลบถาวร คุณแน่ใจหรือไม่?",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/debts.php?id=' + debtId,
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('ลบสำเร็จ!', response.message, 'success');
                            const currentQuery = $('#debt-search').val();
                            fetchDebts().done(function() {
                                filterDebts(currentQuery.toLowerCase());
                            });
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

});
</script>