// JS เฉพาะ modal แนบผลตรวจ (queue_manage.php)
document.addEventListener('DOMContentLoaded', function() {
  var attachBtns = document.querySelectorAll('.attach-result-btn');
  var modalEl = document.getElementById('attachResultModal');
  var modal = null;
  if (window.bootstrap && bootstrap.Modal) {
    modal = new bootstrap.Modal(modalEl);
  } else if (window.$ && $.fn.modal) {
    modal = $(modalEl);
  }
  attachBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.getElementById('modalBookingId').value = btn.getAttribute('data-bookingid');
      document.getElementById('modalPatientName').textContent = btn.getAttribute('data-patientname');
      document.getElementById('modalBookingDate').textContent = btn.getAttribute('data-bookingdate');
      document.getElementById('attachResultForm').reset();
      // ลบแถวที่เกิน 1 ออก (เหลือแถวเดียว)
      var table = document.getElementById('resultDetailsTable').getElementsByTagName('tbody')[0];
      while (table.rows.length > 1) table.deleteRow(1);
      // เคลียร์ค่าในแถวแรก
      Array.from(table.rows[0].querySelectorAll('input')).forEach(input => input.value = '');
      Array.from(table.rows[0].querySelectorAll('select')).forEach(select => select.selectedIndex = 0);
      if (modal && modal.show) {
        modal.show();
      } else if (modal) {
        modal.modal('show');
      }
    });
  });
  // เพิ่มแถวผลตรวจ (ครั้งละ 1 แถวเท่านั้น)
  document.getElementById('addResultRow').addEventListener('click', function() {
    var table = document.getElementById('resultDetailsTable').getElementsByTagName('tbody')[0];
    if (!table || table.rows.length === 0) return;
    var newRow = table.rows[0].cloneNode(true);
    // reset input values in new row
    Array.from(newRow.querySelectorAll('input')).forEach(input => input.value = '');
    Array.from(newRow.querySelectorAll('select')).forEach(select => select.selectedIndex = 0);
    // ป้องกัน id ซ้ำใน input (ถ้ามี)
    Array.from(newRow.querySelectorAll('input')).forEach(input => { if(input.hasAttribute('id')) input.removeAttribute('id'); });
    table.appendChild(newRow);
  });
  // ปิด modal แล้วไม่รีเฟรชหน้าเว็บ (ลบ event window.location.reload())
  // ถ้าต้องการรีเฟรช ให้เพิ่ม event นี้กลับมา
  // ลบแถวผลตรวจ
  document.getElementById('resultDetailsTable').addEventListener('click', function(e) {
    // ป้องกันการ trigger หลายรอบจาก icon ในปุ่ม (bi-trash)
    let btn = e.target;
    if (btn.classList.contains('bi-trash')) {
      btn = btn.closest('button');
    }
    if (btn && btn.classList.contains('remove-row')) {
      var table = btn.closest('tbody');
      if (table.rows.length > 1) {
        btn.closest('tr').remove();
      } else {
        // clear values if only 1 row left
        Array.from(table.rows[0].querySelectorAll('input')).forEach(input => input.value = '');
        Array.from(table.rows[0].querySelectorAll('select')).forEach(select => select.selectedIndex = 0);
      }
    }
  });

  // Autofill ค่าเป้าหมายและหน่วยเมื่อเลือกรายการตรวจ
  document.getElementById('resultDetailsTable').addEventListener('change', function(e) {
    if (e.target.classList.contains('detail-name-select')) {
      var selected = e.target.options[e.target.selectedIndex];
      var tr = e.target.closest('tr');
      var targetInput = tr.querySelector('.detail-target-input');
      var unitInput = tr.querySelector('.detail-unit-input');
      if (selected && targetInput && unitInput) {
        targetInput.value = selected.getAttribute('data-target') || '';
        unitInput.value = selected.getAttribute('data-unit') || '';
      }
    }
  });
});
