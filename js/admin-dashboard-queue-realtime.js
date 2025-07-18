// admin-dashboard-queue-realtime.js
// อัปเดตตารางคิวและตัวเลข dashboard แบบ realtime

document.addEventListener('DOMContentLoaded', function() {
  function updateDashboardQueue(dateStr) {
    // ถ้าไม่ส่ง dateStr ให้ใช้วันนี้
    if (!dateStr) {
      const today = new Date();
      dateStr = today.toISOString().slice(0,10);
    }
    fetch('api_dashboard_today.php?date=' + encodeURIComponent(dateStr))
      .then(res => res.json())
      .then(data => {
        // อัปเดตตัวเลข
        if (typeof data.queueToday !== 'undefined') {
          const el = document.getElementById('queueTodayVal');
          if (el) el.textContent = data.queueToday;
        }
        if (typeof data.newPatients !== 'undefined') {
          const el = document.getElementById('newPatientsVal');
          if (el) el.textContent = data.newPatients;
        }
        if (typeof data.queueWeek !== 'undefined') {
          const el = document.getElementById('queueWeekVal');
          if (el) el.textContent = data.queueWeek;
        }
        // อัปเดตตาราง
        const tbody = document.getElementById('queueTableBody');
        if (tbody && Array.isArray(data.list)) {
          tbody.innerHTML = data.list.length ? data.list.map(row =>
            `<tr><td>${row.time}</td><td>${row.name}</td><td>${row.username ?? ''}</td></tr>`
          ).join('') : '<tr><td colspan="3" class="text-center text-muted">ไม่มีคิววันนี้</td></tr>';
        }
        // อัปเดต title
        const title = document.getElementById('queueListTitle');
        if (title) {
          const d = new Date(dateStr);
          const thDate = d.toLocaleDateString('th-TH', {year:'numeric',month:'short',day:'numeric'});
          title.textContent = 'รายการคิววันที่ ' + thDate;
        }
      });
  }

  // อัปเดตเมื่อเปลี่ยนวันที่
  const dateInput = document.getElementById('dashboardDate');
  if (dateInput) {
    dateInput.addEventListener('change', function() {
      updateDashboardQueue(this.value);
    });
  }

  // อัปเดตอัตโนมัติทุก 3 วินาที (เฉพาะวันที่เลือก)
  setInterval(function() {
    const dateInput = document.getElementById('dashboardDate');
    updateDashboardQueue(dateInput ? dateInput.value : undefined);
  }, 3000);
});
