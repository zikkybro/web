// ใช้ jQuery + jQuery UI Datepicker สำหรับเลือกหลายวัน
// ต้องแน่ใจว่าโหลด jQuery และ jQuery UI แล้ว
$(function() {
  // ป้องกัน submit ซ้อน
  var closedDatesInput = $('#closed_dates');
  var selectedDates = [];

  // แปลง value เดิมเป็น array
  if (closedDatesInput.val()) {
    selectedDates = closedDatesInput.val().split(',').map(function(d) { return d.trim(); });
  }

  // สร้าง datepicker
  closedDatesInput.datepicker({
    dateFormat: 'yy-mm-dd',
    beforeShowDay: function(date) {
      var d = $.datepicker.formatDate('yy-mm-dd', date);
      if (selectedDates.indexOf(d) > -1) {
        return [true, 'ui-state-highlight', 'ปิดรับจอง'];
      }
      return [true, '', ''];
    },
    onSelect: function(dateText) {
      var idx = selectedDates.indexOf(dateText);
      if (idx > -1) {
        selectedDates.splice(idx, 1); // toggle off
      } else {
        selectedDates.push(dateText); // toggle on
      }
      closedDatesInput.val(selectedDates.join(','));
      $(this).datepicker('refresh');
    }
  });

  // ป้องกัน submit ฟอร์มถ้าไม่มีการเลือกวัน
  closedDatesInput.closest('form').on('submit', function(e) {
    closedDatesInput.val(selectedDates.join(','));
  });
});
