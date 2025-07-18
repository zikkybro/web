// admin-settings.js: JS สำหรับ settings.php (แยกจาก inline script)
$(function() {
  // Toggle booking_enabled
  $('#booking_enabled').on('change', function() {
    var enabled = $(this).is(':checked') ? '1' : '0';
    var label = enabled === '1' ? 'เปิด' : 'ปิด';
    $('#booking-enabled-label').text(label);
    $.ajax({
      url: '',
      method: 'POST',
      data: { booking_enabled: enabled },
      dataType: 'json',
      success: function(res) {
        Swal.fire({
          icon: res.success ? 'success' : 'error',
          title: res.success ? 'สำเร็จ' : 'ผิดพลาด',
          text: res.message || (enabled === '1' ? 'เปิดรับจองคิวแล้ว' : 'ปิดรับจองคิวเรียบร้อย'),
          confirmButtonText: 'ตกลง'
        });
      },
      error: function() {
        Swal.fire({
          icon: 'error',
          title: 'ผิดพลาด',
          text: 'ไม่สามารถบันทึกสถานะได้',
          confirmButtonText: 'ตกลง'
        });
      }
    });
  });

  // Intercept submit ฟอร์มวันปิดรับจอง
  $('#closed-dates-form').on('submit', function(e) {
    e.preventDefault();
    var closedDates = $('#closed_dates').val();
    $.ajax({
      url: '',
      method: 'POST',
      data: { closed_dates: closedDates },
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: res.message || 'บันทึกวันปิดรับจองคิวเรียบร้อย',
            confirmButtonText: 'ตกลง'
          }).then(() => {
            window.location.reload(true);
          });
        } else {
          Swal.fire({
            icon: 'warning',
            title: 'ผิดพลาด',
            text: res.message || 'กรุณาเลือกวันปิดรับจองอย่างน้อย 1 วัน',
            confirmButtonText: 'ตกลง'
          });
        }
      },
      error: function() {
        Swal.fire({
          icon: 'error',
          title: 'ผิดพลาด',
          text: 'ไม่สามารถบันทึกวันปิดรับจองได้',
          confirmButtonText: 'ตกลง'
        });
      }
    });
  });

  // Reset วันปิดรับจอง
  $('#reset-closed-dates').on('click', function(e) {
    e.preventDefault();
    $('#closed_dates').val('');
    if ($.datepicker) {
      $('#closed_dates').datepicker('setDate', null);
      if (window.selectedDates) {
        window.selectedDates = [];
        $('#closed_dates').datepicker('refresh');
      }
    }
    $.ajax({
      url: '',
      method: 'POST',
      data: { closed_dates: '' },
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: res.message || 'รีเซตวันปิดรับจองเรียบร้อย',
            confirmButtonText: 'ตกลง'
          }).then(() => {
            window.location.reload(true);
          });
        } else {
          Swal.fire({
            icon: 'warning',
            title: 'กรุณาเลือกวันปิดรับจอง',
            text: res.message || 'กรุณาเลือกวันปิดรับจองอย่างน้อย 1 วัน',
            confirmButtonText: 'ตกลง'
          });
        }
      },
      error: function() {
        Swal.fire({
          icon: 'error',
          title: 'ผิดพลาด',
          text: 'ไม่สามารถรีเซตวันปิดรับจองได้',
          confirmButtonText: 'ตกลง'
        });
      }
    });
  });

  // Save available dates range
  $('#save-available-dates').on('click', function(e) {
    e.preventDefault();
    var from = $('#available_date_from').val();
    var to = $('#available_date_to').val();
    if (!from || !to) {
      Swal.fire({ icon: 'warning', title: 'กรุณาเลือกช่วงวันที่', confirmButtonText: 'ตกลง' });
      return;
    }
    $.ajax({
      url: '',
      method: 'POST',
      data: { available_date_from: from, available_date_to: to },
      dataType: 'json',
      success: function(res) {
        if (res.success) {
          Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: res.message || '',
            confirmButtonText: 'ตกลง'
          }).then(() => {
            window.location.reload(true);
          });
        } else {
          Swal.fire({
            icon: 'warning',
            title: 'ผิดพลาด',
            text: res.message || 'กรุณาเลือกช่วงวันที่ให้ถูกต้อง',
            confirmButtonText: 'ตกลง'
          });
        }
      },
      error: function() {
        Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'ไม่สามารถบันทึกช่วงวันที่ได้', confirmButtonText: 'ตกลง' });
      }
    });
  });

  // Reset available dates range
  $('#reset-available-dates').on('click', function(e) {
    e.preventDefault();
    $.ajax({
      url: '',
      method: 'POST',
      data: { reset_available_dates: 1 },
      dataType: 'json',
      success: function(res) {
        Swal.fire({
          icon: res.success ? 'success' : 'error',
          title: res.success ? 'สำเร็จ' : 'ผิดพลาด',
          text: res.message || '',
          confirmButtonText: 'ตกลง'
        }).then(() => {
          if (res.success) window.location.reload(true);
        });
      },
      error: function() {
        Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'ไม่สามารถรีเซตช่วงวันที่ได้', confirmButtonText: 'ตกลง' });
      }
    });
  });
});
