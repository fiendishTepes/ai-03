<?php 
// debt_summary.php
include 'includes/header.php'; 
?>

<div class="row">
    <div class="col s12">
        <h2><i class="material-icons">account_balance_wallet</i> สรุปยอดหนี้</h2>
        <div class="divider"></div>
    </div>
</div>

<div class="row" id="summary-cards">
    <div class="col s12 m4">
        <div class="card-panel red darken-1 white-text center-align hoverable">
            <i class="material-icons large">money_off</i>
            <h5>หนี้คงค้างทั้งหมด</h5>
            <p class="flow-text" id="total_principal_left">฿ 0.00</p>
        </div>
    </div>
    <div class="col s12 m4">
        <div class="card-panel orange darken-1 white-text center-align hoverable">
            <i class="material-icons large">trending_down</i>
            <h5>ยอดดอกเบี้ยรวม</h5>
            <p class="flow-text" id="total_interest">฿ 0.00</p>
        </div>
    </div>
    <div class="col s12 m4">
        <div class="card-panel teal darken-1 white-text center-align hoverable">
            <i class="material-icons large">check_circle</i>
            <h5>ยอดหนี้ที่จ่ายไปแล้ว</h5>
            <p class="flow-text" id="total_paid">฿ 0.00</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col s12 m6">
        <div class="card z-depth-1">
            <div class="card-content">
                <span class="card-title">สัดส่วนหนี้รวม (เงินต้น)</span>
                <canvas id="debtPieChart" width="400" height="400"></canvas>
            </div>
        </div>
    </div>

    <div class="col s12 m6">
        <div class="card z-depth-1">
            <div class="card-content">
                <span class="card-title">รายการหนี้ที่ยังต้องผ่อนชำระ</span>
                <div id="active-debts-list">
                    <p class="center-align" id="loading-active-debts">กำลังโหลด...</p>
                </div>
                <div class="center-align mt-3">
                    <a id="load-more-btn-active" class="waves-effect waves-light btn red darken-1 disabled" style="margin-top: 15px;">
                        แสดงเพิ่มเติม <i class="material-icons right">arrow_downward</i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col s12">
        <div class="card z-depth-1">
            <div class="card-content">
                <span class="card-title"><i class="material-icons green-text">check_circle</i> หนี้ที่ชำระหมดแล้ว</span>
                <div id="paid-off-debts-list">
                    <p class="center-align" id="loading-paid-off-debts">กำลังโหลด...</p>
                    </div>
                <div class="center-align mt-3">
                    <a id="load-more-btn-paid-off" class="waves-effect waves-light btn green darken-1 disabled" style="margin-top: 15px;">
                        แสดงเพิ่มเติม <i class="material-icons right">arrow_downward</i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>

<style>
/* CSS เหมือนเดิม */
.border-left-red { border-left: 4px solid #F44336 !important; } 
.border-left-orange { border-left: 4px solid #FF9800 !important; } 
.border-left-teal { border-left: 4px solid #009688 !important; } 
.text-bold { font-weight: 500; }
.flow-text-small { font-size: 1.2em; font-weight: bold; }
.active-debt-item {
    cursor: pointer; 
}
.paid-off-debt-item {
    cursor: pointer; 
}
.mt-3 { margin-top: 1rem; }
</style>

<script>
$(document).ready(function() {
    
    // Global Variables สำหรับการแบ่งหน้า
    let debtPieChart = null;
    const DEBTS_PER_PAGE = 10; 
    
    // Active Debts Variables
    let allActiveDebts = [];
    let activeDebtsStartIndex = 0;
    
    // Paid Off Debts Variables <--- NEW
    let allPaidOffDebts = [];
    let paidOffDebtsStartIndex = 0;

    // --- Fetch & Render Summary Data ---
    fetchDebtSummary();

    function fetchDebtSummary() {
        $('#loading-active-debts').show(); 
        $('#loading-paid-off-debts').show(); // Show loading for paid off list
        $('#load-more-btn-active').addClass('disabled');
        $('#load-more-btn-paid-off').addClass('disabled');

        $.ajax({
            url: 'api/debts.php?summary=true',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#loading-active-debts').hide();
                $('#loading-paid-off-debts').hide();
                
                if (response.success) {
                    updateSummaryCards(response.summary);
                    renderDebtPieChart(response.summary);
                    
                    // Active Debts
                    allActiveDebts = response.activeDebts;
                    activeDebtsStartIndex = 0;
                    renderInitialActiveDebts(); // เปลี่ยนชื่อฟังก์ชัน
                    
                    // Paid Off Debts <--- NEW
                    allPaidOffDebts = response.paidOffDebts;
                    paidOffDebtsStartIndex = 0;
                    renderInitialPaidOffDebts();

                } else {
                    Swal.fire('ข้อผิดพลาด', 'ไม่สามารถโหลดสรุปยอดหนี้ได้: ' + response.message, 'error');
                    $('#active-debts-list').html('<p class="red-text center-align">เกิดข้อผิดพลาดในการโหลดข้อมูลสรุป</p>');
                    $('#paid-off-debts-list').html('<p class="red-text center-align">เกิดข้อผิดพลาดในการโหลดข้อมูลสรุป</p>');
                }
            },
             error: function(xhr, status, error) {
                 $('#loading-active-debts').hide();
                 $('#loading-paid-off-debts').hide();
                 Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์เพื่อโหลดสรุปยอดหนี้ได้: ' + error, 'error');
                 $('#active-debts-list').html('<p class="red-text center-align">การเชื่อมต่อล้มเหลว</p>');
                 $('#paid-off-debts-list').html('<p class="red-text center-align">การเชื่อมต่อล้มเหลว</p>');
             }
        });
    }

    // --- Active Debts Logic (ปรับปรุงชื่อฟังก์ชันและปุ่ม) ---
    function renderInitialActiveDebts() {
        const listDiv = $('#active-debts-list');
        listDiv.empty(); 

        if (allActiveDebts.length === 0) {
            listDiv.html('<p class="center-align">ไม่มีหนี้คงค้างที่ต้องชำระแล้ว! 🎉</p>');
            $('#load-more-btn-active').hide(); 
            return;
        }

        loadMoreActiveDebts();
        
        if (allActiveDebts.length > DEBTS_PER_PAGE) {
            $('#load-more-btn-active').show().removeClass('disabled');
        } else {
            $('#load-more-btn-active').hide();
        }
    }

    function loadMoreActiveDebts() {
        const listDiv = $('#active-debts-list');
        const endIndex = Math.min(activeDebtsStartIndex + DEBTS_PER_PAGE, allActiveDebts.length);
        const debtsToRender = allActiveDebts.slice(activeDebtsStartIndex, endIndex);

        const today = new Date();
        const currentDay = today.getDate();

        debtsToRender.forEach(debt => {
            // ... (โค้ด render card เหมือนเดิม) ...
            const monthlyPayment = debt.monthly_payment_effective.toLocaleString('th-TH', { minimumFractionDigits: 2 });
            const progress = (debt.paid_months / debt.total_months) * 100;
            const progressWidth = Math.min(progress, 100);

            let alertColor = 'teal'; 
            const daysRemaining = (debt.due_day >= currentDay) 
                ? (debt.due_day - currentDay) 
                : (debt.due_day - currentDay + 30); 
            
            if (daysRemaining <= 3) {
                alertColor = 'red'; 
            } else if (daysRemaining <= 7) {
                alertColor = 'orange'; 
            } 
            
            const interestIcon = debt.is_interest_fixed 
                ? `<i class="material-icons left small orange-text text-darken-3">trending_up</i>` 
                : `<i class="material-icons left small green-text text-darken-3">check</i>`;
                
            const interestText = debt.is_interest_fixed 
                ? `ดอกเบี้ย ${parseFloat(debt.interest_rate).toFixed(2)}% ต่อปี` 
                : `ยอดจ่ายคงที่ (ดอกเบี้ย 0%)`;

            const debtDetails = JSON.stringify({
                name: debt.item_name, owner: debt.owner_name, principal: debt.principal_amount.toLocaleString('th-TH', { minimumFractionDigits: 2 }), total_months: debt.total_months, paid_months: debt.paid_months, monthly_payment: debt.monthly_payment_effective.toLocaleString('th-TH', { minimumFractionDigits: 2 }), repayment_left: debt.repayment_left.toLocaleString('th-TH', { minimumFractionDigits: 2 }), principal_left: debt.principal_left.toLocaleString('th-TH', { minimumFractionDigits: 2 }), due_day: debt.due_day, interest: debt.total_interest_calculated.toLocaleString('th-TH', { minimumFractionDigits: 2 }), notes: debt.notes
            });


            listDiv.append(`
                <div class="card-panel white z-depth-1 hoverable mb-2 p-3 border-left-${alertColor} active-debt-item" data-debt='${debtDetails}'>
                    <div class="row m-0">
                        <div class="col s12">
                            <h6 class="fw-bold m-0">${debt.item_name} <span class="badge ${alertColor} white-text right">วันที่ ${debt.due_day}</span></h6>
                            <p class="grey-text text-darken-1 mb-1" style="font-size: 0.9em;">
                                เจ้าหนี้: ${debt.owner_name} | วงเงิน: ฿ ${parseFloat(debt.principal_amount).toLocaleString('th-TH', { minimumFractionDigits: 2 })}
                            </p>
                        </div>
                        <div class="col s12">
                            <p class="mb-1 text-bold">
                                <span class="flow-text-small ${alertColor}-text">฿ ${monthlyPayment} / เดือน</span>
                            </p>
                            <p class="mb-2" style="font-size: 0.8em;">
                                ${interestIcon} ${interestText}
                            </p>
                        </div>
                        
                        <div class="col s12">
                            <p class="mb-1" style="font-size: 0.8em;">ผ่อนแล้ว: ${debt.paid_months}/${debt.total_months} งวด</p>
                            <div class="progress ${alertColor} lighten-4" style="height: 10px; margin: 0;">
                                <div class="determinate ${alertColor}" style="width: ${progressWidth}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        });

        activeDebtsStartIndex = endIndex;
        
        if (activeDebtsStartIndex >= allActiveDebts.length) {
            $('#load-more-btn-active').addClass('disabled').text('แสดงครบทุกรายการแล้ว');
        } else {
            $('#load-more-btn-active').removeClass('disabled').html('แสดงเพิ่มเติม <i class="material-icons right">arrow_downward</i>');
        }
    }
    
    // ผูก Event Handler กับปุ่ม "แสดงเพิ่มเติม" Active Debts
    $('#load-more-btn-active').on('click', function() {
        if (!$(this).hasClass('disabled')) {
            loadMoreActiveDebts();
        }
    });

    // --- Paid Off Debts Logic (ใหม่) ---
    
    function renderInitialPaidOffDebts() {
        const listDiv = $('#paid-off-debts-list');
        listDiv.empty(); 

        if (allPaidOffDebts.length === 0) {
            listDiv.html('<p class="center-align">ไม่มีรายการหนี้ที่ชำระครบถ้วน</p>');
            $('#load-more-btn-paid-off').hide(); 
            return;
        }

        loadMorePaidOffDebts();
        
        if (allPaidOffDebts.length > DEBTS_PER_PAGE) {
            $('#load-more-btn-paid-off').show().removeClass('disabled');
        } else {
            $('#load-more-btn-paid-off').hide();
        }
    }

    function loadMorePaidOffDebts() {
        const listDiv = $('#paid-off-debts-list');
        const endIndex = Math.min(paidOffDebtsStartIndex + DEBTS_PER_PAGE, allPaidOffDebts.length);
        const debtsToRender = allPaidOffDebts.slice(paidOffDebtsStartIndex, endIndex);

        let htmlContent = '<ul class="collection">';
        
        debtsToRender.forEach(debt => {
            const debtDetails = JSON.stringify({
                name: debt.item_name, owner: debt.owner_name, principal: debt.principal_amount.toLocaleString('th-TH', { minimumFractionDigits: 2 }), total_months: debt.total_months, paid_months: debt.paid_months, monthly_payment: debt.monthly_payment_effective.toLocaleString('th-TH', { minimumFractionDigits: 2 }), interest: debt.total_interest_calculated.toLocaleString('th-TH', { minimumFractionDigits: 2 }),
            });

            htmlContent += `
                <li class="collection-item paid-off-debt-item waves-effect waves-light" data-debt='${debtDetails}'>
                    <div>
                        <span class="green-text text-darken-3">${debt.item_name} (${debt.owner_name})</span>
                        <a href="#!" class="secondary-content"><i class="material-icons green-text">info_outline</i></a>
                    </div>
                </li>
            `;
        });
        
        htmlContent += '</ul>';
        listDiv.append(htmlContent); // เปลี่ยนจาก .html() เป็น .append() เพื่อเพิ่มรายการ

        paidOffDebtsStartIndex = endIndex;
        
        if (paidOffDebtsStartIndex >= allPaidOffDebts.length) {
            $('#load-more-btn-paid-off').addClass('disabled').text('แสดงครบทุกรายการแล้ว');
        } else {
            $('#load-more-btn-paid-off').removeClass('disabled').html('แสดงเพิ่มเติม <i class="material-icons right">arrow_downward</i>');
        }
    }
    
    // ผูก Event Handler กับปุ่ม "แสดงเพิ่มเติม" Paid Off Debts
    $('#load-more-btn-paid-off').on('click', function() {
        if (!$(this).hasClass('disabled')) {
            loadMorePaidOffDebts();
        }
    });

    // --- ฟังก์ชันอื่นๆ (เหมือนเดิม) ---
    
    function updateSummaryCards(summary) {
        $('#total_principal_left').text(`฿ ${summary.total_principal_left.toLocaleString('th-TH', { minimumFractionDigits: 2 })}`);
        $('#total_interest').text(`฿ ${summary.total_interest.toLocaleString('th-TH', { minimumFractionDigits: 2 })}`);
        $('#total_paid').text(`฿ ${summary.total_paid.toLocaleString('th-TH', { minimumFractionDigits: 2 })}`);
    }

    function renderDebtPieChart(summary) {
        if (debtPieChart) { debtPieChart.destroy(); }
        
        const totalRepayment = summary.total_principal_owed + summary.total_interest;

        const ctx = document.getElementById('debtPieChart').getContext('2d');
        debtPieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['ยอดหนี้ที่จ่ายไปแล้ว', 'ยอดหนี้ที่ต้องจ่ายคงค้าง'],
                datasets: [{
                    data: [summary.total_paid, summary.total_principal_left],
                    backgroundColor: ['#4CAF50', '#F44336'], 
                    hoverBackgroundColor: ['#66BB6A', '#E57373']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: `ยอดหนี้ทั้งหมด (รวมดอกเบี้ย): ฿ ${totalRepayment.toLocaleString('th-TH', { minimumFractionDigits: 2 })}`
                    }
                }
            }
        });
    }
    
    // --- Click handler สำหรับรายการหนี้ที่ชำระหมดแล้ว (Paid Off) ---
    $(document).on('click', '.paid-off-debt-item', function() {
        const debtData = $(this).data('debt');
        
        Swal.fire({
            title: `รายละเอียดหนี้: ${debtData.name}`,
            html: `
                <div style="text-align: left; font-size: 1.1em;">
                    <p>สถานะ: <strong class="green-text">ชำระครบถ้วนแล้ว 🎉</strong></p>
                    <p>เจ้าหนี้: <strong>${debtData.owner}</strong></p>
                    <hr>
                    <p>ยอดเงินต้นรวม: <strong>฿ ${debtData.principal}</strong></p>
                    <p>ยอดดอกเบี้ยรวม: <strong>฿ ${debtData.interest}</strong></p>
                    <hr>
                    <p>จำนวนงวดที่ผ่อน: <strong>${debtData.paid_months} / ${debtData.total_months} งวด</strong></p>
                    <p>ยอดจ่ายต่อเดือน: <strong>฿ ${debtData.monthly_payment}</strong></p>
                </div>
            `,
            icon: 'success',
            showCloseButton: true,
            showConfirmButton: true,
            confirmButtonText: 'ตกลง',
            width: '400px'
        });
    });

    // --- Click handler สำหรับรายการหนี้ที่ยังต้องผ่อน (Active) ---
    $(document).on('click', '.active-debt-item', function() {
        const debtData = $(this).data('debt');
        
        Swal.fire({
            title: `รายละเอียดหนี้: ${debtData.name}`,
            html: `
                <div style="text-align: left; font-size: 1.1em;">
                    <p>เจ้าหนี้: <strong>${debtData.owner}</strong></p>
                    <p>วงเงินหนี้ (เงินต้น): <strong>฿ ${debtData.principal}</strong></p>
                    <hr>
                    <p>ยอดที่ผ่อนไปแล้ว: <strong>${debtData.paid_months} / ${debtData.total_months} งวด</strong></p>
                    <p>ยอดจ่ายต่อเดือน: <strong>฿ ${debtData.monthly_payment}</strong></p>
                    <hr>
                    <p class="red-text"><strong>ยอดหนี้คงค้าง (รวมดอกเบี้ย): ฿ ${debtData.repayment_left}</strong></p>
                    <p class="orange-text text-darken-2">ยอดเงินต้นคงค้าง: ฿ ${debtData.principal_left}</p>
                    <p>ดอกเบี้ยรวม: ฿ ${debtData.interest}</p>
                </div>
            `,
            icon: 'info',
            showCloseButton: true,
            showConfirmButton: true,
            confirmButtonText: 'ตกลง',
            width: '400px'
        });
    });

});
</script>