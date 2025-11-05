<?php 
// summary.php
include 'includes/header.php'; 
?>
<div class="row">
    <div class="col s12">
        <h2><i class="material-icons">bar_chart</i> สรุปยอด</h2>
        <div class="divider"></div>
    </div>
</div>

<div class="row">
    <div class="col s12 m6 offset-m3">
        <div class="card-panel z-depth-1">
            <span class="card-title black-text">เลือกช่วงเดือนที่ต้องการดูสรุป</span>
            <form id="summary-form">
                <div class="row">
                    <div class="input-field col s12">
                        <i class="material-icons prefix">calendar_today</i>
                        <input type="text" id="month_year" name="month_year" class="datepicker" placeholder="YYYY-MM" required>
                        <label for="month_year">เดือน/ปี</label>
                        <span class="helper-text">เลือกเดือน/ปี (เช่น 2025-01)</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col s12 center-align">
                        <button class="btn waves-effect waves-light blue darken-1" type="submit">
                            ดูสรุปยอด <i class="material-icons right">search</i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="summary-results" style="display:none;">
    <div class="row" id="summary-cards">
        <div class="col s12 m4">
            <div class="card-panel teal lighten-1 white-text center-align">
                <h5>รายรับรวม</h5>
                <p class="flow-text" id="total_income_val">฿ 0.00</p>
            </div>
        </div>
        <div class="col s12 m4">
            <div class="card-panel red lighten-1 white-text center-align">
                <h5>รายจ่ายรวม</h5>
                <p class="flow-text" id="total_expense_val">฿ 0.00</p>
            </div>
        </div>
        <div class="col s12 m4">
            <div class="card-panel blue darken-1 white-text center-align">
                <h5>คงเหลือสุทธิ</h5>
                <p class="flow-text" id="net_balance_val">฿ 0.00</p>
            </div>
        </div>
    </div>
    
    <div class="row" id="special-expense-cards">
        <div class="col s12 m6 l3" id="special-card-1">
             <div class="card-panel light-green lighten-5 center-align z-depth-1">
                 <i class="material-icons green-text" id="icon-ลูกชิ้น">restaurant</i>
                 <p class="mb-0 black-text" style="font-weight: 500;">ค่าลูกชิ้นรวม:</p>
                 <h5 class="green-text" id="expense_ลูกชิ้น">฿ 0.00</h5>
             </div>
        </div>
        <div class="col s12 m6 l3" id="special-card-2">
             <div class="card-panel amber lighten-5 center-align z-depth-1">
                 <i class="material-icons amber-text text-darken-4" id="icon-บะหมี่">ramen_dining</i>
                 <p class="mb-0 black-text" style="font-weight: 500;">ค่าบะหมี่รวม:</p>
                 <h5 class="amber-text text-darken-4" id="expense_บะหมี่">฿ 0.00</h5>
             </div>
        </div>
        <div class="col s12 m0 l6 hide-on-med-and-down"></div>
    </div>

    <div class="row">
        <div class="col s12 m6">
            <div class="card z-depth-1">
                <div class="card-content">
                    <span class="card-title">สัดส่วนรายจ่ายตามหมวดหมู่</span>
                    <canvas id="expenseCategoryChart" width="400" height="400"></canvas>
                </div>
            </div>
        </div>
        <div class="col s12 m6">
            <div class="card z-depth-1">
                <div class="card-content">
                    <span class="card-title">รายรับ-รายจ่ายรายวันในเดือน</span>
                    <canvas id="monthlyTrendChart" width="400" height="400"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col s12">
            <div class="card z-depth-1">
                <div class="card-content">
                    <span class="card-title">รายการธุรกรรมประจำเดือน <span id="current-month-display" class="teal-text"></span></span>
                    <div id="monthly-transaction-list">
                         <div class="center-align grey-text text-lighten-1" style="padding: 20px;">กรุณาเลือกเดือนเพื่อดูรายการ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div> <?php include 'includes/footer.php'; ?>

<style>
    /* NEW CSS สำหรับรายการธุรกรรมแบบกลุ่ม */
    .day-group {
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        overflow: hidden;
    }
    .day-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 15px;
        background-color: #f5f5f5; 
        font-weight: bold;
        border-bottom: 1px solid #e0e0e0;
    }
    .day-header .date-text {
        font-size: 1.1em;
        color: #26a69a; 
    }
    .day-header .summary-text {
        font-size: 0.9em;
    }
    .transaction-item {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        border-bottom: 1px solid #eee;
        transition: background-color 0.1s;
    }
    .transaction-item:hover {
        background-color: #fafafa;
    }
    .transaction-actions {
        margin-left: 10px;
        white-space: nowrap;
    }
    .item-amount {
        font-weight: bold;
        width: 120px;
        text-align: right;
        margin-right: 15px;
    }
    .item-category {
        font-size: 0.9em;
        color: #757575; 
    }
    .item-description {
        font-size: 0.9em;
        color: #424242; 
    }
    .income-amount {
        color: #26a69a; 
    }
    .expense-amount {
        color: #ef5350; 
    }
    .net-positive {
        color: #26a69a;
    }
    .net-negative {
        color: #ef5350;
    }
    .image-icon {
        margin-right: 10px;
        color: #2196f3; 
    }
</style>

<script>
// Global object to store data
let currentMonthYear = '';
let expenseCategoryChart = null;
let monthlyTrendChart = null;
let allCategories = []; 
let transactionsDataByDate = {}; 

$(document).ready(function() {
    
    // --- 1. INITIAL SETUP ---
    // ดึงหมวดหมู่ทั้งหมดสำหรับฟอร์มแก้ไข
    $.ajax({
        url: 'api/transactions.php', 
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) { allCategories = response.categories; }
        }
    });

    // Initialise Datepicker และ Select
    $('.datepicker').datepicker({
        format: 'yyyy-mm', 
        showMonthAfterYear: true,
        defaultDate: new Date(), 
        setDefaultDate: true,
        onSelect: function(date) {
            const month = date.getMonth() + 1;
            const year = date.getFullYear();
            $('#month_year').val(`${year}-${month < 10 ? '0' : ''}${month}`);
            M.Datepicker.getInstance(this.el).close(); 
        },
        i18n: {
             cancel: 'ยกเลิก', clear: 'ล้าง', done: 'ตกลง', 
             months: ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'],
             weekdaysShort: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],
        }
    });
    $('select').formSelect();
    
    // --- 2. CORE FUNCTIONS ---

    // ฟังก์ชันดึงข้อมูลสรุป (ตัวเลขและกราฟ)
    function fetchSummaryData(monthYear) {
        Swal.fire({ title: 'กำลังโหลด...', text: 'กำลังประมวลผลข้อมูลสรุป', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
        
        $.ajax({
            url: `api/summary_data.php?period=${monthYear}&type=monthly`, 
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                Swal.close();
                if (response.success) {
                    $('#summary-results').show();
                    updateSummaryCards(response.summary, response.specialExpenses); 
                    renderExpenseCategoryChart(response.expenseByCategory);
                    renderMonthlyTrendChart(response.dailySummary);
                } else {
                    $('#summary-results').hide();
                    Swal.fire('ข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลได้: ' + response.message, 'error');
                }
            },
            error: function() {
                Swal.close();
                $('#summary-results').hide();
                Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
            }
        });
    }

    // ฟังก์ชันดึงรายการธุรกรรมแบบละเอียด (สำหรับรายการแบ่งกลุ่ม)
    function fetchMonthlyTransactions(monthYear) {
        $('#monthly-transaction-list').html('<div class="center-align grey-text text-lighten-1" style="padding: 20px;">กำลังโหลดรายการ...</div>');
        $('#current-month-display').text(monthYear);

        $.ajax({
            url: 'api/summary_details.php?month=' + monthYear,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    transactionsDataByDate = response.transactions_by_date;
                    renderMonthlyTransactions(transactionsDataByDate); 
                } else {
                    $('#monthly-transaction-list').html('<div class="center-align red-text" style="padding: 20px;">ไม่สามารถโหลดรายการธุรกรรมได้: ' + response.message + '</div>');
                }
            },
            error: function() {
                $('#monthly-transaction-list').html('<div class="center-align red-text" style="padding: 20px;">เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์</div>');
            }
        });
    }

    // Submits Form: จุดเริ่มต้นของการทำงานทั้งหมด
    $('#summary-form').on('submit', function(e) {
        e.preventDefault(); 
        
        const monthYear = $('#month_year').val();
        
        if (!monthYear || !/^\d{4}-\d{2}$/.test(monthYear)) {
            Swal.fire('ข้อผิดพลาด', 'กรุณาเลือกเดือน/ปีในรูปแบบ YYYY-MM', 'error');
            return;
        }
        
        currentMonthYear = monthYear; 

        // 1. ดึงข้อมูลสรุป (ตัวเลขและกราฟ)
        fetchSummaryData(currentMonthYear);
        
        // 2. ดึงรายการธุรกรรมละเอียด (สำหรับรายการแบ่งกลุ่ม)
        fetchMonthlyTransactions(currentMonthYear);
    });
    
    // ฟังก์ชันอัปเดต Card สรุปยอดรวม และ Card รายจ่ายพิเศษ
    function updateSummaryCards(summary, specialExpenses) {
        const income = parseFloat(summary.total_income || 0);
        const expense = parseFloat(summary.total_expense || 0);
        const net = income - expense;
        
        $('#total_income_val').text(`฿ ${income.toLocaleString('th-TH', { minimumFractionDigits: 2 })}`);
        $('#total_expense_val').text(`฿ ${expense.toLocaleString('th-TH', { minimumFractionDigits: 2 })}`);
        $('#net_balance_val').text(`฿ ${net.toLocaleString('th-TH', { minimumFractionDigits: 2 })}`);
        
        $('#net_balance_val').parent().parent().removeClass('blue darken-1 red darken-1');
        $('#net_balance_val').parent().parent().addClass(net >= 0 ? 'blue darken-1' : 'red darken-1');
        
        // อัปเดต Card รายจ่ายพิเศษ (ลูกชิ้น/บะหมี่)
        $('#expense_ลูกชิ้น').text(`฿ ${specialExpenses['ค่าลูกชิ้น'].toLocaleString('th-TH', { minimumFractionDigits: 2 })}`);
        $('#expense_บะหมี่').text(`฿ ${specialExpenses['ค่าบะหมี่'].toLocaleString('th-TH', { minimumFractionDigits: 2 })}`);
    }

    // ฟังก์ชันสร้างกราฟวงกลมรายจ่าย (โค้ดเดิม)
    function renderExpenseCategoryChart(data) {
        if (expenseCategoryChart) { expenseCategoryChart.destroy(); }
        const labels = data.map(item => item.category_name);
        const amounts = data.map(item => parseFloat(item.total_amount));
        const colorPalette = ['#EF5350', '#42A5F5', '#FFCA28', '#26A69A', '#4CAF50', '#00897B', '#FFA726', '#66BB6A', '#7E57C2', '#8D6E63', '#FF6384', '#36A2EB'];
        const targetCategory = 'ซื้อของเข้าร้าน(ร้านค้า)';
        const targetColor = '#00897B'; 
        const backgroundColors = labels.map((label, index) => {
            if (label === targetCategory) { return targetColor; }
            return colorPalette[index % colorPalette.length]; 
        });

        const ctx = document.getElementById('expenseCategoryChart').getContext('2d');
        expenseCategoryChart = new Chart(ctx, { 
            type: 'pie', 
            data: { labels: labels, datasets: [{ data: amounts, backgroundColor: backgroundColors, hoverBackgroundColor: backgroundColors }] }, 
            options: { 
                responsive: true, 
                plugins: { legend: { position: 'top' }, title: { display: true, text: 'สัดส่วนรายจ่ายตามหมวดหมู่' } } 
            } 
        });
    }

    // ฟังก์ชันสร้างกราฟรายรับ-รายจ่ายรายวัน (โค้ดเดิม)
    function renderMonthlyTrendChart(data) {
        if (monthlyTrendChart) { monthlyTrendChart.destroy(); }
        const labels = data.map(item => item.day);
        const incomeData = data.map(item => parseFloat(item.income));
        const expenseData = data.map(item => parseFloat(item.expense));
        const ctx = document.getElementById('monthlyTrendChart').getContext('2d');
        monthlyTrendChart = new Chart(ctx, { type: 'line', data: { labels: labels, datasets: [ { label: 'รายรับ (฿)', data: incomeData, borderColor: 'rgba(38, 166, 154, 1)', backgroundColor: 'rgba(38, 166, 154, 0.2)', fill: true, tension: 0.1 }, { label: 'รายจ่าย (฿)', data: expenseData, borderColor: 'rgba(239, 83, 80, 1)', backgroundColor: 'rgba(239, 83, 80, 0.2)', fill: true, tension: 0.1 } ] }, options: { responsive: true, scales: { y: { beginAtZero: true } }, plugins: { legend: { position: 'top' }, title: { display: true, text: 'รายรับ-รายจ่ายรายวัน' } } } });
    }
    
    // NEW/FIXED: ฟังก์ชันแสดงรายการธุรกรรมในรูปแบบแบ่งกลุ่มตามวัน
    function renderMonthlyTransactions(transactionsByDate) {
        const listDiv = $('#monthly-transaction-list');
        listDiv.empty();

        const sortedDates = Object.keys(transactionsByDate).sort().reverse();
        
        if (sortedDates.length === 0) {
            listDiv.append('<div class="center-align" style="padding: 20px;">ไม่พบรายการธุรกรรมในเดือนนี้</div>');
            return;
        }

        sortedDates.forEach(dateKey => {
            const dayData = transactionsByDate[dateKey];
            const items = dayData.items;
            
            if (items.length === 0) return; 

            // *** FIXED: ใช้ total_income และ total_expense ที่มาจาก API ***
            const income = parseFloat(dayData.total_income || 0);
            const expense = parseFloat(dayData.total_expense || 0);
            const net = income - expense;
            const netColorClass = net >= 0 ? 'net-positive' : 'net-negative';

            // แปลงวันที่ให้อยู่ในรูปแบบ DD/MM/YYYY
            const dateParts = dateKey.split('-');
            const formattedDate = dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0];

            let dayGroupHtml = `
                <div class="day-group z-depth-1">
                    <div class="day-header">
                        <span class="date-text">วันที่: ${formattedDate}</span>
                        <span class="summary-text">
                            รายรับ: <span class="income-amount">฿ ${income.toLocaleString('th-TH', { minimumFractionDigits: 2 })}</span> | 
                            รายจ่าย: <span class="expense-amount">฿ ${expense.toLocaleString('th-TH', { minimumFractionDigits: 2 })}</span> | 
                            สุทธิ: <span class="${netColorClass}">฿ ${net.toLocaleString('th-TH', { minimumFractionDigits: 2 })}</span>
                        </span>
                    </div>
                    <ul class="collection" style="border: none; margin: 0;">
            `;

            items.forEach(item => {
                const typeClass = item.type === 'income' ? 'income-amount' : 'expense-amount';
                const typeSymbol = item.type === 'income' ? '+' : '-';
                
                // สร้าง object ข้อมูลทั้งหมดสำหรับปุ่มแก้ไข/ลบ
                const itemDataJson = JSON.stringify({
                    id: item.transaction_id, type: item.type, amount: parseFloat(item.amount), 
                    category_id: item.category_id, description: item.description, 
                    transaction_date: item.transaction_date, image_path: item.image_path || null
                });

                let imageIcon = '';
                if (item.image_path && item.image_path !== '') {
                    imageIcon = `<a href="#!" class="view-image-btn image-icon" data-path="${item.image_path}" title="ดูหลักฐาน"><i class="material-icons tiny">image</i></a>`;
                }

                const deleteBtn = `<a href="#!" class="delete-btn red-text" data-id="${item.transaction_id}" title="ลบรายการ"><i class="material-icons tiny">delete_forever</i></a>`;
                const editBtn = `<a href="#!" class="edit-btn teal-text" data-item='${itemDataJson}' title="แก้ไขรายการ" style="margin-right: 5px;"><i class="material-icons tiny">edit</i></a>`;


                dayGroupHtml += `
                    <li class="transaction-item">
                        <div class="transaction-details">
                            <div class="item-description">${item.description || item.category_name}</div>
                            <div class="item-category">หมวดหมู่: ${item.category_name || '-'}</div>
                        </div>
                        <div class="item-amount ${typeClass}">
                            ${typeSymbol} ${parseFloat(item.amount).toLocaleString('th-TH', { minimumFractionDigits: 2 })}
                        </div>
                        <div class="transaction-actions">
                            ${imageIcon}
                            ${editBtn}
                            ${deleteBtn}
                        </div>
                    </li>
                `;
            });

            dayGroupHtml += `
                    </ul>
                </div>
            `;
            listDiv.append(dayGroupHtml);
        });
    }

    // --- Event Handlers สำหรับปุ่มในตาราง ---
    
    // ผูก Event Handler ดูรูป
    $(document).on('click', '#monthly-transaction-list .view-image-btn', function() {
        const imagePath = $(this).data('path');
        Swal.fire({
            title: 'หลักฐานการทำธุรกรรม',
            html: `<img src="${imagePath}" style="max-width: 100%; height: auto; border-radius: 8px;">`,
            showCloseButton: true, showConfirmButton: false, width: '80%',
        });
    });

    // ฟังก์ชันดำเนินการ (Delete/Edit) และรีโหลดข้อมูล
    function processUpdateAfterAction(itemId, action, newData, itemData) {
        Swal.fire({ title: 'กำลังดำเนินการ...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        const deletePromise = new Promise((resolve, reject) => {
            if (action === 'EDIT' || action === 'DELETE') {
                $.ajax({
                    url: 'api/transactions.php?id=' + itemId, type: 'DELETE',
                    success: (response) => {
                        if (response.success) resolve(response);
                        else reject('ลบรายการเก่าล้มเหลว: ' + response.message);
                    },
                    error: () => reject('เกิดข้อผิดพลาดในการเชื่อมต่อ (Delete)')
                });
            } else {
                resolve({ success: true, message: 'No deletion needed' });
            }
        });

        deletePromise.then(() => {
            if (action === 'EDIT') {
                return $.ajax({
                    url: 'api/transactions.php', type: 'POST', contentType: 'application/json', data: JSON.stringify(newData),
                    success: (response) => {
                        if (response.success) return response;
                        else throw new Error('เพิ่มรายการใหม่ล้มเหลว: ' + response.message);
                    },
                    error: () => { throw new Error('เกิดข้อผิดพลาดในการเชื่อมต่อ (Add)'); }
                });
            } else {
                return { success: true, message: 'รายการถูกลบเรียบร้อยแล้ว' };
            }
        })
        .then((finalResponse) => {
            Swal.fire(action === 'EDIT' ? 'แก้ไขสำเร็จ!' : 'ลบสำเร็จ!', finalResponse.message, 'success');
            
            // รีโหลดข้อมูลสรุปและตาราง
            fetchSummaryData(currentMonthYear);
            fetchMonthlyTransactions(currentMonthYear);
        })
        .catch((errorMsg) => {
            Swal.close();
            Swal.fire('ข้อผิดพลาด', errorMsg, 'error');
        });
    }

    function handleEditTransaction(itemData) {
        // สร้างตัวเลือกหมวดหมู่สำหรับ SweetAlert
        let catOptions = {};
        allCategories.forEach(cat => {
            if (cat.type === itemData.type) {
                catOptions[cat.category_id] = cat.category_name;
            }
        });
        
        Swal.fire({
            title: `แก้ไขรายการ ${itemData.id}`,
            html: `
                <input type="date" id="swal-date" class="swal2-input" value="${itemData.transaction_date}" disabled>
                <input type="number" id="swal-amount" class="swal2-input" placeholder="จำนวนเงิน" value="${itemData.amount}" step="0.01" min="0.01">
                <select id="swal-category" class="swal2-select">
                    ${Object.entries(catOptions).map(([key, value]) => 
                        `<option value="${key}" ${key == itemData.category_id ? 'selected' : ''}>${value}</option>`
                    ).join('')}
                </select>
                <input type="text" id="swal-description" class="swal2-input" placeholder="รายละเอียด" value="${itemData.description}">
                <p class="text-secondary" style="font-size: 0.9em; margin-top: 10px;">หลักฐาน: ${itemData.image_path ? 'มีรูปภาพ' : 'ไม่มี'}. (หากต้องการเปลี่ยนรูป ต้องลบรายการเดิมและสร้างใหม่)</p>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'บันทึกการแก้ไข',
            cancelButtonText: 'ยกเลิก',
            preConfirm: () => {
                const newAmount = parseFloat($('#swal-amount').val());
                const newCatId = $('#swal-category').val();
                const newDesc = $('#swal-description').val();

                if (newAmount <= 0 || !newCatId) {
                    Swal.showValidationMessage(`กรุณากรอกจำนวนเงินและเลือกหมวดหมู่`);
                    return false;
                }
                
                return {
                    id: itemData.id, type: itemData.type, amount: newAmount, category_id: newCatId,
                    description: newDesc, transaction_date: itemData.transaction_date, image_path: itemData.image_path 
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                processUpdateAfterAction(result.value.id, 'EDIT', result.value, null);
            }
        });
        
        // ต้อง re-initialize select ใน SweetAlert
        setTimeout(() => {
            const swalSelect = document.getElementById('swal-category');
            if (swalSelect) { M.FormSelect.init(swalSelect); }
        }, 100);
    }
    
    // ผูก Event Handler ลบ
    $(document).on('click', '#monthly-transaction-list .delete-btn', function() {
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
                processUpdateAfterAction(transactionId, 'DELETE', null, null);
            }
        });
    });

    // ผูก Event Handler แก้ไข
    $(document).on('click', '#monthly-transaction-list .edit-btn', function() {
        const itemDataJson = $(this).data('item');
        handleEditTransaction(itemDataJson);
    });
});
</script>