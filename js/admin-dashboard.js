// admin-dashboard.js: Dashboard-specific JS (date picker, dashboard update, etc.)
document.addEventListener('DOMContentLoaded', function() {
  const dateInput = document.getElementById('dashboardDate');
  const queueTodayVal = document.getElementById('queueTodayVal');
  const newPatientsVal = document.getElementById('newPatientsVal');
  const queueWeekVal = document.getElementById('queueWeekVal');
  const queueTableBody = document.getElementById('queueTableBody');
  const queueListTitle = document.getElementById('queueListTitle');

  function updateDashboard(dateStr) {
    fetch('api_dashboard_today.php?date=' + encodeURIComponent(dateStr))
      .then(res => res.json())
      .then(data => {
        queueTodayVal.textContent = data.queueToday;
        newPatientsVal.textContent = data.newPatients;
        queueWeekVal.textContent = data.queueWeek;

        // ตาราง
        queueTableBody.innerHTML = '';
        if (Array.isArray(data.list)) {
          data.list.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${item.time}</td><td>${item.name}</td><td>${item.username || ''}</td>`;
            queueTableBody.appendChild(tr);
          });
        }

        // เปลี่ยนชื่อหัวตาราง
        const todayStr = new Date().toISOString().slice(0,10);
        if (dateStr === todayStr) {
          queueListTitle.textContent = 'รายการคิววันนี้';
        } else {
          const [y, m, d] = dateStr.split('-');
          const thMonths = ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
          queueListTitle.textContent = `รายการคิววันที่ ${parseInt(d)} ${thMonths[parseInt(m)-1]} ${parseInt(y)+543}`;
        }
      });
  }

  updateDashboard(dateInput.value);
  setInterval(() => updateDashboard(dateInput.value), 10000);
  dateInput.addEventListener('change', () => updateDashboard(dateInput.value));
});
