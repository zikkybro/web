// admin-available-dates-range.js
// ใช้ jQuery UI Datepicker สำหรับเลือกช่วงวันที่จองได้ (range)
// ต้องแน่ใจว่าโหลด jQuery และ jQuery UI แล้ว
$(function() {
  var $rangeInput = $('#available_dates_range');
  var $from = $('#available_date_from');
  var $to = $('#available_date_to');
  var $resetBtn = $('#reset-available-dates');

  // ตั้งค่า datepicker
  $from.datepicker({
    dateFormat: 'yy-mm-dd',
    onSelect: function(selectedDate) {
      $to.datepicker('option', 'minDate', selectedDate);
      updateRangeInput();
    }
  });
  $to.datepicker({
    dateFormat: 'yy-mm-dd',
    onSelect: function(selectedDate) {
      $from.datepicker('option', 'maxDate', selectedDate);
      updateRangeInput();
    }
  });

  function updateRangeInput() {
    var from = $from.val();
    var to = $to.val();
    if (from && to) {
      $rangeInput.val(from + ' ถึง ' + to);
    } else {
      $rangeInput.val('');
    }
  }

  $resetBtn.on('click', function(e) {
    e.preventDefault();
    $from.val('');
    $to.val('');
    $from.datepicker('option', 'maxDate', null);
    $to.datepicker('option', 'minDate', null);
    $rangeInput.val('');
  });
});
