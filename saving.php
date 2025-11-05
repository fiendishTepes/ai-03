<?php 
// saving.php
include 'includes/header.php'; 
?>

<div class="row">
    <div class="col s12">
        <h2><i class="material-icons">savings</i> โมดูลออมเงิน</h2>
        <div class="divider"></div>
    </div>
</div>

<div class="row">
    <div class="col s12 m5">
        <div class="card-panel z-depth-1">
            <span class="card-title black-text">ตั้งเป้าหมายกระปุกออมสินใหม่</span>
            <form id="saving-pot-form">
                
                <div class="row">
                    <div class="input-field col s12">
                        <i class="material-icons prefix">title</i>
                        <input id="pot_name" name="pot_name" type="text" class="validate" required>
                        <label for="pot_name">ชื่อเป้าหมายการออม</label>
                    </div>
                </div>

                <div class="row">
                    <div class="input-field col s12">
                        <i class="material-icons prefix">attach_money</i>
                        <input id="target_amount" name="target_amount" type="number" step="0.01" min="1" class="validate" required>
                        <label for="target_amount">จำนวนเงินเป้าหมายรวม (฿)</label>
                    </div>
                </div>

                <div class="row">
                    <div class="col s12">
                        <label>กำหนดช่วงเวลาการออม:</label>
                        <select id="target_type" name="target_type" required>
                            <option value="" disabled selected>เลือกวิธีการคำนวณเป้าหมาย</option>
                            <option value="yearly">เป้าหมายรายปี (ระบบคำนวณ 12 เดือน)</option>
                            <option value="period">กำหนดเอง (ภายใน X เดือน)</option> 
                        </select>
                    </div>
                </div>

                <div class="row" id="period-field" style="display: none;">
                    <div class="input-field col s12">
                        <i class="material-icons prefix">calendar_today</i>
                        <input id="period_months" name="period_months" type="number" min="1" class="validate">
                        <label for="period_months">ระยะเวลาที่ต้องการออม (จำนวนเดือน)</label>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col s12">
                        <div class="card-panel teal lighten-5">
                            <h6 class="teal-text fw-bold mb-3"><i class="material-icons left">calculate</i> สรุปยอดเงินออมที่ต้องเก็บ:</h6>
                            <ul style="list-style-type: none;">
                                <li class="mb-1"><i class="material-icons tiny left">brightness_low</i> **ต่อวัน**: <span id="daily-target-display" class="fw-bold">฿ 0.00</span></li>
                                <li class="mb-1 monthly-target-row" style="display: none;"><i class="material-icons tiny left">calendar_month</i> **ต่อเดือน**: <span id="monthly-target-display" class="fw-bold">฿ 0.00</span></li>
                                <li class="yearly-target-row" style="display: none;"><i class="material-icons tiny left">date_range</i> **ต่อปี**: <span id="yearly-target-display" class="fw-bold">฿ 0.00</span></li>
                            </ul>
                            <input type="hidden" id="calculated_monthly_target" name="calculated_monthly_target">
                            <input type="hidden" id="calculated_months_used" name="calculated_months_used">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col s12 center-align">
                        <button class="btn waves-effect waves-light teal darken-1" type="submit">
                            <i class="material-icons left">create</i> สร้างกระปุก
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col s12 m7">
        <div class="card-panel z-depth-1">
            <span class="card-title black-text">กระปุกออมสินทั้งหมด</span>
            <div id="savings-list" class="row">
                <p class="center-align" id="loading-pots">กำลังโหลดรายการกระปุกออมสิน...</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Initialise Select
    $('select').formSelect();
    
    // ฟังก์ชันคำนวณและแสดงยอดที่ต้องเก็บต่อวัน/เดือน/ปี
    function calculateMonthlyTarget() {
        const targetAmount = parseFloat($('#target_amount').val()) || 0;
        const targetType = $('#target_type').val();
        let calculatedMonthly = 0;
        let months = 0;

        // ซ่อนยอดรายเดือนและรายปีทั้งหมดก่อน
        $('.monthly-target-row').hide();
        $('.yearly-target-row').hide();

        if (targetAmount > 0) {
            if (targetType === 'yearly') {
                months = 12;
            } else if (targetType === 'period') {
                months = parseInt($('#period_months').val()) || 0;
            }
        }
        
        if (months > 0) {
            calculatedMonthly = targetAmount / months;
        }

        const calculatedYearly = calculatedMonthly * 12;
        const calculatedDaily = calculatedMonthly / 30; // ประมาณ 30 วันต่อเดือน

        // แสดงผล
        $('#daily-target-display').text(`฿ ${calculatedDaily.toLocaleString('th-TH', { minimumFractionDigits: 2 })}`);
        $('#monthly-target-display').text(`฿ ${calculatedMonthly.toLocaleString('th-TH', { minimumFractionDigits: 2 })}`);
        $('#yearly-target-display').text(`฿ ${calculatedYearly.toLocaleString('th-TH', { minimumFractionDigits: 2 })}`);
        
        // แสดงตามเงื่อนไข
        if (months >= 1) {
             $('.monthly-target-row').show();
        }
        if (months >= 12) {
             $('.yearly-target-row').show();
        }

        // บันทึกยอดรายเดือนและจำนวนเดือนสำหรับส่งไปยัง Backend
        $('#calculated_monthly_target').val(calculatedMonthly.toFixed(2));
        $('#calculated_months_used').val(months); // เก็บจำนวนเดือนที่ใช้คำนวณ (ใช้ในฟอร์ม submit)
    }
    
    // ฟังการเปลี่ยนแปลงของ Target Type, Amount, และ Months
    $('#target_type, #target_amount').on('change', function() {
        const type = $('#target_type').val();
        if (type === 'period') {
            $('#period-field').show();
            $('#period_months').attr('required', true);
        } else {
            $('#period-field').hide();
            $('#period_months').attr('required', false).val('');
        }
        calculateMonthlyTarget();
    });
    
    // ฟังการเปลี่ยนแปลงของจำนวนเดือน
    $('#period_months').on('input', calculateMonthlyTarget);
    
    // Initial calculation on load
    calculateMonthlyTarget(); 

    // โหลดรายการกระปุกออมสินเมื่อเปิดหน้า
    fetchSavingPots();

    // Submit Form สร้างกระปุก
    $('#saving-pot-form').on('submit', function(e) {
        e.preventDefault();
        
        const pot_name = $('#pot_name').val();
        const target_amount = parseFloat($('#target_amount').val());
        const target_type = $('#target_type').val();
        
        const monthlyTarget = parseFloat($('#calculated_monthly_target').val()) || 0;
        const monthsUsed = parseInt($('#calculated_months_used').val()) || 0;
        
        // ค่าที่จะใช้บันทึกใน DB ในฟิลด์ target_value
        let target_value_to_save; 

        if (target_type === 'period') {
            // Target Value = จำนวนเดือนที่ผู้ใช้กรอก
            target_value_to_save = monthsUsed;
        } else if (target_type === 'yearly') {
            // Target Value = ยอดที่ต้องเก็บต่อเดือน (คำนวณ)
            target_value_to_save = monthlyTarget;
        }


        if (!pot_name || target_amount <= 0 || !target_type || monthsUsed <= 0) {
            Swal.fire('ข้อผิดพลาด', 'กรุณากรอกข้อมูลให้ครบถ้วนและถูกต้อง (ตรวจสอบยอดคำนวณ/ระยะเวลา)', 'error');
            return;
        }

        const formData = {
            pot_name: pot_name,
            target_amount: target_amount,
            target_type: target_type,
            target_value: target_value_to_save
        };

        $.ajax({
            url: 'api/savings.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    Swal.fire('สำเร็จ!', response.message, 'success');
                    $('#saving-pot-form')[0].reset();
                    $('select').formSelect();
                    // รีเซ็ตหน้าจอแสดงผล
                    $('#daily-target-display').text('฿ 0.00'); 
                    $('.monthly-target-row').hide();
                    $('.yearly-target-row').hide();
                    $('#period-field').hide();
                    fetchSavingPots();
                } else {
                    Swal.fire('ข้อผิดพลาด', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
            }
        });
    });

    // --- ส่วนแสดงผลกระปุกออมสิน (ปรับปรุงการแสดงผล targetText ใน Card) ---

    // ฟังก์ชันดึงรายการกระปุก
    function fetchSavingPots() {
        $('#loading-pots').show();
        $.ajax({
            url: 'api/savings.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#loading-pots').hide();
                if (response.success) {
                    renderSavingPots(response.pots);
                } else {
                    $('#savings-list').html('<p class="red-text center-align">ไม่สามารถโหลดรายการกระปุกได้: ' + response.message + '</p>');
                }
            },
            error: function() {
                $('#loading-pots').text('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์เพื่อโหลดข้อมูลได้');
            }
        });
    }

    // ฟังก์ชันแสดงรายการกระปุก
    function renderSavingPots(pots) {
        const listDiv = $('#savings-list');
        listDiv.empty();

        if (pots.length === 0) {
            listDiv.html('<p class="center-align">ยังไม่มีกระปุกออมสิน สร้างกระปุกแรกของคุณเลย!</p>');
            return;
        }

        pots.forEach(function(pot) {
            const percentage = (pot.current_amount / pot.target_amount) * 100;
            const progressColor = percentage >= 100 ? 'green' : 'teal';
            const progressWidth = Math.min(percentage, 100);
            
            let targetText = '';
            let monthlyTarget = 0;
            let dailyTarget = 0;
            let monthsUsed = 0;

            if (pot.target_type === 'yearly') {
                 // Target Value = ยอดที่ต้องเก็บต่อเดือน
                 monthlyTarget = parseFloat(pot.target_value);
                 monthsUsed = 12;
                 targetText = `เป้าหมายที่ตั้งไว้: รายปี (${monthsUsed} เดือน)`;
            } else if (pot.target_type === 'period') {
                 // Target Value = จำนวนเดือน
                 monthsUsed = parseInt(pot.target_value);
                 monthlyTarget = pot.target_amount / monthsUsed;
                 targetText = `เป้าหมายที่ตั้งไว้: ภายใน ${monthsUsed} เดือน`;
            }
            
            const yearlyTarget = monthlyTarget * 12;
            dailyTarget = monthlyTarget / 30;

            let targetDetails = `<li>ต่อวัน: **฿ ${dailyTarget.toLocaleString('th-TH', { minimumFractionDigits: 2 })}**</li>`;
            
            if (monthsUsed >= 1) {
                targetDetails += `<li>ต่อเดือน: **฿ ${monthlyTarget.toLocaleString('th-TH', { minimumFractionDigits: 2 })}**</li>`;
            }
            if (monthsUsed >= 12) {
                targetDetails += `<li>ต่อปี: **฿ ${yearlyTarget.toLocaleString('th-TH', { minimumFractionDigits: 2 })}**</li>`;
            }

            const potCard = `
                <div class="col s12 m12 l6">
                    <div class="card z-depth-2 hoverable">
                        <div class="card-content" style="padding-bottom: 0;">
                            <span class="card-title" style="font-size: 1.5em;"><i class="material-icons left ${progressColor}-text">account_balance_wallet</i> ${pot.pot_name}</span>
                            <p class="mb-2"><em>${targetText}</em></p>
                            
                            <h6 class="mt-2"><strong>เป้าหมายรวม:</strong> <span class="teal-text">฿ ${parseFloat(pot.target_amount).toLocaleString('th-TH', { minimumFractionDigits: 2 })}</span></h6>
                            <h6 class="mb-3"><strong>สะสมแล้ว:</strong> <span class="${progressColor}-text">฿ ${parseFloat(pot.current_amount).toLocaleString('th-TH', { minimumFractionDigits: 2 })}</span></h6>

                            <div class="progress ${progressColor} lighten-3" style="height: 10px;">
                                <div class="determinate ${progressColor} darken-1" style="width: ${progressWidth}%"></div>
                            </div>
                            <p class="right-align mb-3" style="font-weight: bold;">ความคืบหน้า: ${progressWidth.toFixed(2)}%</p>
                            
                            <div class="divider"></div>
                            
                            <h6 class="mt-3">ยอดที่ต้องเก็บเพื่อถึงเป้าหมาย:</h6>
                            <ul style="list-style-type: disc; margin-left: 20px; font-size: 0.9em; margin-bottom: 15px;">
                                ${targetDetails}
                            </ul>
                        </div>
                        
                        <div class="card-action">
                            <a href="#!" class="btn-small green darken-1 waves-effect save-money-btn" data-id="${pot.pot_id}" data-name="${pot.pot_name}">ออมเงินเพิ่ม</a>
                            <a href="#!" class="btn-small red darken-1 waves-effect delete-pot-btn" data-id="${pot.pot_id}">ลบกระปุก</a>
                        </div>
                    </div>
                </div>
            `;
            listDiv.append(potCard);
        });
    }

    // --- ฟังก์ชันจัดการ (โค้ดเดิม) ---
    // ... (ฟังก์ชันจัดการ Save/Delete โค้ดเดิม) ...
    $(document).on('click', '.save-money-btn', function() {
        const potId = $(this).data('id');
        const potName = $(this).data('name');
        Swal.fire({
            title: `ออมเงินเข้ากระปุก "${potName}"`,
            html: `
                <input type="number" id="swal-save-amount" class="swal2-input" placeholder="จำนวนเงินที่ต้องการออม (฿)" step="0.01" min="1">
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'บันทึกการออม',
            cancelButtonText: 'ยกเลิก',
            preConfirm: () => {
                const amount = parseFloat($('#swal-save-amount').val());
                if (amount <= 0 || isNaN(amount)) {
                    Swal.showValidationMessage(`กรุณากรอกจำนวนเงินที่ถูกต้อง`);
                    return false;
                }
                return amount;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const amount = result.value;
                $.ajax({
                    url: 'api/savings.php',
                    type: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify({ pot_id: potId, amount: amount }),
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('สำเร็จ!', response.message, 'success');
                            fetchSavingPots(); 
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
    
    $(document).on('click', '.delete-pot-btn', function() {
        const potId = $(this).data('id');
        Swal.fire({
            title: 'ยืนยันการลบกระปุก?',
            text: "ข้อมูลกระปุกออมสินจะถูกลบถาวร (รวมถึงยอดเงินที่สะสมไว้) คุณแน่ใจหรือไม่?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/savings.php?id=' + potId,
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('ลบสำเร็จ!', response.message, 'success');
                            fetchSavingPots(); 
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