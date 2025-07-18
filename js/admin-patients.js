// admin-patients.js: Patients page specific JS (history modal, etc.)
document.addEventListener('DOMContentLoaded', function() {
  // Modal ประวัติการตรวจ
  var historyBtns = document.querySelectorAll('.view-history-btn');
  var historyModalEl = document.getElementById('historyModal');
  var historyModal = null;
  if (window.bootstrap && bootstrap.Modal) {
    historyModal = new bootstrap.Modal(historyModalEl);
  } else if (window.$ && $.fn.modal) {
    historyModal = $(historyModalEl);
  }
  historyBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      var userId = btn.getAttribute('data-userid');
      var username = btn.getAttribute('data-username');
      document.getElementById('historyModalUsername').textContent = username;
      var contentDiv = document.getElementById('historyModalContent');
      contentDiv.innerHTML = '<div class="text-center text-muted">กำลังโหลดข้อมูล...</div>';
      // AJAX ดึงข้อมูลประวัติ (ตัวอย่าง: api_get_history.php?user_id=...)
      fetch('api_get_history.php?user_id=' + encodeURIComponent(userId))
        .then(res => res.json())
        .then(data => {
          if (data && data.length > 0) {
            let html = '<div class="table-responsive"><table class="table table-bordered table-sm"><thead><tr><th>วันที่จอง</th><th>เวลา</th><th>ชื่อผู้ป่วย</th></tr></thead><tbody>';
            data.forEach(item => {
              html += `<tr><td>${item.booking_date || '-'}</td><td>${item.booking_time || '-'}</td><td>${item.patient_name || '-'}</td></tr>`;
            });
            html += '</tbody></table></div>';
            contentDiv.innerHTML = html;
          } else {
            contentDiv.innerHTML = '<div class="text-center text-muted">ไม่พบประวัติการตรวจ</div>';
          }
        })
        .catch(() => {
          contentDiv.innerHTML = '<div class="text-danger text-center">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>';
        });
      if (historyModal && historyModal.show) {
        historyModal.show();
      } else if (historyModal) {
        historyModal.modal('show');
      }
    });
  });
});
