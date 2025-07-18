// admin-reports.js: JS เฉพาะของหน้า reports.php

document.addEventListener('DOMContentLoaded', function() {
  const daysSelect = document.getElementById('daysSelect');
  const daysLabel = document.getElementById('daysLabel');
  const tableTitle = document.getElementById('tableTitle');
  const queueChartTitle = document.getElementById('queueChartTitle');
  const patientChartTitle = document.getElementById('patientChartTitle');
  let queueChart, patientChart;

  function fetchAndRender(days) {
    fetch('api_report_summary.php?days=' + days)
      .then(res => res.json())
      .then(data => {
        // Chart: จำนวนคิว
        const ctx1 = document.getElementById('queueChart').getContext('2d');
        const gradient1 = ctx1.createLinearGradient(0,0,0,200);
        gradient1.addColorStop(0, '#2563eb');
        gradient1.addColorStop(1, '#93c5fd');
        if (queueChart) queueChart.destroy();
        queueChart = new Chart(ctx1, {
          type: 'bar',
          data: {
            labels: data.labels,
            datasets: [{
              label: 'จำนวนคิว',
              data: data.queue,
              backgroundColor: gradient1,
              borderRadius: 8,
            }]
          },
          options: {responsive:true, plugins:{legend:{display:false}}}
        });
        // Chart: ผู้ป่วยใหม่
        const ctx2 = document.getElementById('patientChart').getContext('2d');
        const gradient2 = ctx2.createLinearGradient(0,0,0,200);
        gradient2.addColorStop(0, '#22c55e');
        gradient2.addColorStop(1, '#bbf7d0');
        if (patientChart) patientChart.destroy();
        patientChart = new Chart(ctx2, {
          type: 'bar',
          data: {
            labels: data.labels,
            datasets: [{
              label: 'ผู้ป่วยใหม่',
              data: data.patients,
              backgroundColor: gradient2,
              borderRadius: 8,
            }]
          },
          options: {responsive:true, plugins:{legend:{display:false}}}
        });
        // Table
        let html = '';
        for(let i=0;i<data.labels.length;i++) {
          html += `<tr>
            <td><i class='bi bi-calendar'></i> ${data.labels[i]}</td>
            <td><span class='badge bg-primary'>${data.queue[i]}</span></td>
            <td><span class='badge bg-success'>${data.patients[i]}</span></td>
          </tr>`;
        }
        document.getElementById('reportTableBody').innerHTML = html;
        daysLabel.textContent = days;
        tableTitle.textContent = `ตารางสรุป (${days} วันล่าสุด)`;
        queueChartTitle.textContent = `จำนวนคิว (${days} วัน)`;
        patientChartTitle.textContent = `ผู้ป่วยใหม่ (${days} วัน)`;
      });
  }

  // Initial load
  fetchAndRender(daysSelect.value);

  daysSelect.addEventListener('change', function() {
    fetchAndRender(this.value);
  });
});
