// --- Modal ยืนยันตัวตน, เลือกดูประวัติ/ผลตรวจ, iframe, ปุ่มย้อนกลับ ---
document.addEventListener('DOMContentLoaded', function () {
  var openCombinedBtn = document.getElementById('openCombinedBtn');
  var verifyInfoModal = new bootstrap.Modal(document.getElementById('verifyInfoModal'));
  var chooseActionModal = new bootstrap.Modal(document.getElementById('chooseActionModal'));
  var verifyInfoForm = document.getElementById('verifyInfoForm');
  var verifyIdCard = document.getElementById('verifyIdCard');
  var verifyPhone = document.getElementById('verifyPhone');
  var historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
  var resultsModal = new bootstrap.Modal(document.getElementById('resultsModal'));
  var historyIframe = document.getElementById('historyIframe');
  var resultsIframe = document.getElementById('resultsIframe');
  var lastIdCard = '';
  var lastPhone = '';

  if (openCombinedBtn) {
    openCombinedBtn.addEventListener('click', function () {
      verifyIdCard.value = '';
      verifyPhone.value = '';
      verifyIdCard.classList.remove('is-invalid');
      verifyPhone.classList.remove('is-invalid');
      verifyInfoModal.show();
    });
  }

  if (verifyInfoForm) {
    verifyInfoForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var idCard = verifyIdCard.value.trim();
      var phone = verifyPhone.value.trim();
      var valid = true;
      verifyIdCard.classList.remove('is-invalid');
      verifyPhone.classList.remove('is-invalid');
      if (!idCard) {
        verifyIdCard.classList.add('is-invalid');
        valid = false;
      }
      if (!phone) {
        verifyPhone.classList.add('is-invalid');
        valid = false;
      }
      if (!idCard || !phone) {
        Swal.fire({ icon: 'warning', title: 'กรุณากรอกข้อมูลให้ครบถ้วน', confirmButtonText: 'ตกลง' });
        return;
      }
      var idCardOk = /^\d{13}$/.test(idCard);
      var phoneOk = /^\d{9,15}$/.test(phone);
      if (!idCardOk) {
        verifyIdCard.classList.add('is-invalid');
        valid = false;
      }
      if (!phoneOk) {
        verifyPhone.classList.add('is-invalid');
        valid = false;
      }
      if (!idCardOk || !phoneOk) {
        Swal.fire({ icon: 'error', title: 'ข้อมูลไม่ถูกต้อง', text: 'กรุณาตรวจสอบรหัสประจำตัว 13 หลัก และเบอร์โทรศัพท์', confirmButtonText: 'ตกลง' });
        return;
      }
      Swal.fire({
        icon: 'success',
        title: 'ยืนยันตัวตนสำเร็จ',
        showConfirmButton: false,
        timer: 900
      });
      lastIdCard = idCard;
      lastPhone = phone;
      setTimeout(function () {
        verifyInfoModal.hide();
        setTimeout(function () { chooseActionModal.show(); }, 350);
      }, 950);
    });
  }

  var chooseHistoryBtn = document.getElementById('chooseHistoryBtn');
  var chooseResultsBtn = document.getElementById('chooseResultsBtn');
  var chooseBackBtn = document.getElementById('chooseBackBtn');
  if (chooseHistoryBtn) {
    chooseHistoryBtn.addEventListener('click', function () {
      chooseActionModal.hide();
      setTimeout(function () {
        historyIframe.src = 'booking-history.php?modal=1&id_card=' + encodeURIComponent(lastIdCard) + '&phone=' + encodeURIComponent(lastPhone);
        historyModal.show();
      }, 350);
    });
  }
  if (chooseResultsBtn) {
    chooseResultsBtn.addEventListener('click', function () {
      chooseActionModal.hide();
      setTimeout(function () {
        resultsIframe.src = 'health-results.php?modal=1&id_card=' + encodeURIComponent(lastIdCard) + '&phone=' + encodeURIComponent(lastPhone);
        resultsModal.show();
      }, 350);
    });
  }
  if (chooseBackBtn) {
    chooseBackBtn.addEventListener('click', function () {
      chooseActionModal.hide();
      setTimeout(function () {
        verifyInfoModal.show();
      }, 350);
    });
  }
  if (historyModal && historyIframe) {
    document.getElementById('historyModal').addEventListener('hidden.bs.modal', function () {
      historyIframe.src = '';
    });
  }
  if (resultsModal && resultsIframe) {
    document.getElementById('resultsModal').addEventListener('hidden.bs.modal', function () {
      resultsIframe.src = '';
    });
  }
  var historyBackBtn = document.getElementById('historyBackBtn');
  var resultsBackBtn = document.getElementById('resultsBackBtn');
  if (historyBackBtn) {
    historyBackBtn.addEventListener('click', function () {
      historyModal.hide();
      setTimeout(function () {
        chooseActionModal.show();
      }, 350);
    });
  }
  if (resultsBackBtn) {
    resultsBackBtn.addEventListener('click', function () {
      resultsModal.hide();
      setTimeout(function () {
        chooseActionModal.show();
      }, 350);
    });
  }
});
console.log('DEBUG: booking-calendar.js loaded');
const monthNames = [
  "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
  "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
];

// bookings จะถูกอัปเดตแบบ realtime จาก API
let currentBookings = {};

function populateMonthYearSelectors() {
  const monthSelect = document.getElementById('monthSelect');
  const yearSelect = document.getElementById('yearSelect');
  const today = new Date();
  const thisYear = today.getFullYear();

  // เดือน
  monthSelect.innerHTML = '';
  for (let m = 0; m < 12; m++) {
    const opt = document.createElement('option');
    opt.value = m;
    opt.text = monthNames[m];
    monthSelect.appendChild(opt);
  }
  // ปี (ย้อนหลัง 2 ปี ถึงล่วงหน้า 2 ปี)
  yearSelect.innerHTML = '';
  for (let y = thisYear - 2; y <= thisYear + 2; y++) {
    const opt = document.createElement('option');
    opt.value = y;
    opt.text = y + 543; // แสดงปี พ.ศ.
    yearSelect.appendChild(opt);
  }
  // ตั้งค่าปัจจุบัน
  monthSelect.value = today.getMonth();
  yearSelect.value = thisYear;
}

function renderCalendar(month, year, bookings) {
  const today = new Date();
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  let jsDay = firstDay.getDay();
  let startDay = jsDay === 0 ? 6 : jsDay - 1;
  const daysInMonth = lastDay.getDate();
  // แปลง closedDates เป็น Set เพื่อเช็คเร็ว
  const closedSet = (typeof closedDates !== 'undefined' && Array.isArray(closedDates)) ? new Set(closedDates) : new Set();
  // กำหนดช่วงวันที่เปิดจอง
  let minDate = availableFrom;
  let maxDate = availableTo;

  // รอบเวลาทั้งหมดใน 1 วัน
  const allTimes = [
    "08:30", "08:50", "09:10", "09:30", "09:50", "10:10", "10:30", "10:50", "11:10", "11:30", "11:50",
    "12:10", "12:30", "12:50", "13:10", "13:30", "13:50", "14:10", "14:30", "14:50", "15:10", "15:30", "15:50", "16:10"
  ];

  console.log('DEBUG: renderCalendar called', month, year, bookings);
  let html = '<thead><tr>';
  const days = ['จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.', 'อา.'];
  for (let d of days) html += `<th>${d}</th>`;
  html += '</tr></thead><tbody>';

  let day = 1;
  while (day <= daysInMonth) {
    html += '<tr>';
    for (let i = 0; i < 7; i++) {
      if ((day === 1 && i < startDay) || day > daysInMonth) {
        html += '<td></td>';
      } else {
        let classes = [];
        let dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        let isToday = (day === today.getDate() && month === today.getMonth() && year === today.getFullYear());
        let booked = bookings[dateStr] || {};
        let isFull = allTimes.every(time => booked[time]);
        let isClosed = closedSet.has(dateStr);
        // เช็คว่าอยู่นอกช่วงที่เปิดจองหรือไม่
        let isOutOfRange = false;
        if (minDate && dateStr < minDate) isOutOfRange = true;
        if (maxDate && dateStr > maxDate) isOutOfRange = true;
        if (isToday) classes.push('today');
        if (isFull) classes.push('booked');
        if (isClosed) classes.push('closed-booking');
        if (isOutOfRange) classes.push('out-of-range');

        // เพิ่ม data-date ให้ td ที่ selectable
        if (!isFull && !isClosed && !isOutOfRange) {
          classes.push('calendar-date-selectable');
          html += `<td class="${classes.join(' ')} align-top" data-date="${dateStr}">`;
        } else {
          html += `<td class="${classes.join(' ')} align-top">`;
        }
        html += `<div class=\"fw-bold fs-5 mb-2\">${day}</div>`;
        // แสดงจำนวนจอง/จำนวนทั้งหมดใต้ตัวเลขวันที่ (ตัวเล็ก สีจาง)
        if (typeof bookingCounts !== 'undefined' && typeof maxBookingPerDay !== 'undefined') {
          let dateStrForCount = dateStr;
          let count = bookingCounts[dateStrForCount] || 0;
          html += `<span style=\"font-size:0.6em;color:#888;margin-bottom:2px;line-height:1;white-space:nowrap;display:inline-block;\">${count} / ${maxBookingPerDay}</span>`;
        }
        // เฉพาะ desktop เท่านั้นที่แสดงข้อความสถานะ
        if (window.innerWidth > 576) {
          if (isClosed) {
            html += `<div class=\"text-secondary small mb-1 fw-bold\">ปิดรับจอง</div>`;
          } else if (isOutOfRange) {
            html += `<div class=\"text-secondary small mb-1 fw-bold\">ไม่เปิดจอง</div>`;
          } else if (isFull) {
            html += `<div class=\"text-danger small mb-1 fw-bold\">เต็มแล้ว</div>`;
          }
        }
        if (!isFull && !isClosed && !isOutOfRange) {
          // เพิ่ม class ให้ td เพื่อให้คลิก cell ได้เลย
          classes.push('calendar-date-selectable');
        }
        html += `</td>`;
        day++;
      }
    }
    html += '</tr>';
  }
  html += '</tbody>';
  document.getElementById('calendar-table').innerHTML = html;
  // DEBUG: log ว่า renderCalendar สร้าง td.selectable หรือไม่
  setTimeout(() => {
    const selectable = document.querySelectorAll('td.calendar-date-selectable');
    console.log('DEBUG: calendar-date-selectable count:', selectable.length);
    selectable.forEach(td => {
      console.log('DEBUG: selectable td', td.getAttribute('data-date'), td);
    });
  }, 100);

  // ใช้ event delegation ที่ <table> เพื่อรองรับ cell ใหม่และป้องกัน bind ซ้ำ
  const table = document.getElementById('calendar-table');
  if (table) {
    // ลบ handler เดิมก่อน (ถ้ามี)
    table.removeEventListener('click', window._calendarCellClickHandler || (() => { }));
    window._calendarCellClickHandler = function (e) {
      const td = e.target.closest('td.calendar-date-selectable');
      if (!td) return;
      td.style.cursor = 'pointer';
      td.classList.add('calendar-date-hover');
      // ใช้ data-date ที่ฝังใน td
      const date = td.getAttribute('data-date');
      if (!date) { alert('ไม่พบ data-date ใน cell'); return; }
      // DEBUG log
      console.log('Cell clicked', date);
      // reset ก่อน แล้วค่อย set ข้อมูลใหม่
      resetBookingModal();
      document.getElementById('bookingDate').value = date;
      document.getElementById('bookingModalLabel').innerText = 'จองคิววันที่ ' + date.split('-').reverse().join('/');
      updateTimeOptions(date, bookings);
      try {
        const modalEl = document.getElementById('bookingModal');
        // ปิด modal เดิมถ้าเปิดอยู่ (แก้ aria-hidden bug)
        if (modalEl.classList.contains('show')) {
          bootstrap.Modal.getInstance(modalEl)?.hide();
        }
        // รีเซ็ต aria-hidden
        modalEl.setAttribute('aria-hidden', 'false');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
      } catch (e) {
        alert('เกิดข้อผิดพลาดในการเปิด Modal: ' + e);
      }
    };
    table.addEventListener('click', window._calendarCellClickHandler);
  }
}

function updateTimeOptions(date, bookings) {
  const allTimes = [
    "08:30", "08:50", "09:10", "09:30", "09:50", "10:10", "10:30", "10:50", "11:10", "11:30", "11:50",
    "12:10", "12:30", "12:50", "13:10", "13:30", "13:50", "14:10", "14:30", "14:50", "15:10", "15:30", "15:50", "16:10"
  ];
  const booked = bookings[date] || {};
  const grid = document.getElementById('bookingTimeGrid');
  let html = '';
  allTimes.forEach(time => {
    if (booked[time]) {
      html += `<button type="button" class="btn btn-danger mb-2 me-2" disabled>${time}</button>`;
    } else {
      html += `<button type="button" class="btn btn-success mb-2 me-2 btn-time-select" data-time="${time}">${time}</button>`;
    }
  });
  grid.innerHTML = html;
  document.getElementById('bookingTime').value = '';

  // เลือกเวลา
  document.querySelectorAll('.btn-time-select').forEach(btn => {
    btn.addEventListener('click', function () {
      document.getElementById('bookingTime').value = this.getAttribute('data-time');
      document.querySelectorAll('.btn-time-select').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      // ซ่อนข้อความเตือนเมื่อเลือกเวลา
      document.getElementById('bookingTimeFeedback').style.display = 'none';
      document.getElementById('bookingTimeGrid').classList.remove('border', 'border-danger', 'rounded-2');

      // คำนวณลำดับคิว (slot ที่เลือกในวันนั้น)
      const time = this.getAttribute('data-time');
      const queue = allTimes.indexOf(time) + 1;
      const queueNumberEl = document.getElementById('queueNumber');
      queueNumberEl.innerText = 'คิวที่ ' + queue;
      queueNumberEl.classList.add('show');
    });
  });
}

function resetBookingModal() {
  // รีเซ็ตข้อความเตือนและกรอบแดงทุกช่อง
  document.getElementById('bookingTimeFeedback').style.display = 'none';
  document.getElementById('bookingTimeGrid').classList.remove('border', 'border-danger', 'rounded-2');
  document.getElementById('bookingTime').value = '';
  document.querySelectorAll('.btn-time-select').forEach(b => b.classList.remove('active'));
  // รีเซ็ต is-invalid ทุก input/select/textarea
  document.querySelectorAll('#bookingForm .is-invalid').forEach(el => el.classList.remove('is-invalid'));
  // รีเซ็ตค่า input (ยกเว้นวันที่)
  document.getElementById('bookingName').value = '';
  document.getElementById('bookingIdCard').value = '';
  document.getElementById('bookingPhone').value = '';
  document.getElementById('bookingEmail').value = '';
  document.getElementById('bookingServiceType').value = '';
  document.getElementById('bookingGender').value = '';
  document.getElementById('bookingAge').value = '';
  document.getElementById('bookingNote').value = '';
  const queueNumberEl = document.getElementById('queueNumber');
  queueNumberEl.innerText = '';
  queueNumberEl.classList.remove('show');
}


document.addEventListener('DOMContentLoaded', function () {
  populateMonthYearSelectors();
  const monthSelect = document.getElementById('monthSelect');
  const yearSelect = document.getElementById('yearSelect');

  function updateCalendarWithBookings() {
    renderCalendar(parseInt(monthSelect.value), parseInt(yearSelect.value), currentBookings);
  }

  monthSelect.addEventListener('change', updateCalendarWithBookings);
  yearSelect.addEventListener('change', updateCalendarWithBookings);

  // ฟังก์ชันโหลด bookings แบบ realtime
  async function fetchBookings() {
    const res = await fetch('api-bookings.php?_=' + Date.now());
    if (res.ok) {
      return await res.json();
    } else {
      return {};
    }
  }

  async function updateBookingsRealtime() {
    currentBookings = await fetchBookings();
    updateCalendarWithBookings();
  }

  // อัปเดต bookings ทุก 3 วินาที
  setInterval(updateBookingsRealtime, 3000);
  // เรียกครั้งแรก
  updateBookingsRealtime();
});


// Modal form submit + validation ทุกช่อง
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('bookingForm');
  if (!form) return;
  form.addEventListener('submit', function (e) {
    let valid = true;
    function setInvalid(id) {
      const el = document.getElementById(id);
      if (el) el.classList.add('is-invalid');
    }
    function setValid(id) {
      const el = document.getElementById(id);
      if (el) el.classList.remove('is-invalid');
    }
    // ชื่อ-นามสกุล
    const nameInput = document.getElementById('bookingName');
    if (!nameInput.value.trim()) {
      setInvalid('bookingName');
      valid = false;
    } else {
      setValid('bookingName');
    }
    // รหัสประจำตัว 13 หลัก
    const idCard = document.getElementById('bookingIdCard');
    if (!/^\d{13}$/.test(idCard.value)) {
      setInvalid('bookingIdCard');
      valid = false;
    } else {
      setValid('bookingIdCard');
    }
    // เบอร์โทรศัพท์
    const phone = document.getElementById('bookingPhone');
    if (!/^\d{9,15}$/.test(phone.value)) {
      setInvalid('bookingPhone');
      valid = false;
    } else {
      setValid('bookingPhone');
    }
    // อีเมล
    const email = document.getElementById('bookingEmail');
    if (!/^\S+@\S+\.\S+$/.test(email.value)) {
      setInvalid('bookingEmail');
      valid = false;
    } else {
      setValid('bookingEmail');
    }
    // การเข้ารับบริการตรวจสุขภาพ
    const serviceType = document.getElementById('bookingServiceType');
    if (!serviceType.value) {
      setInvalid('bookingServiceType');
      valid = false;
    } else {
      setValid('bookingServiceType');
    }
    // เพศ
    const gender = document.getElementById('bookingGender');
    if (!gender.value) {
      setInvalid('bookingGender');
      valid = false;
    } else {
      setValid('bookingGender');
    }
    // อายุ
    const age = document.getElementById('bookingAge');
    if (!age.value || isNaN(age.value) || age.value < 0 || age.value > 120) {
      setInvalid('bookingAge');
      valid = false;
    } else {
      setValid('bookingAge');
    }
    // เวลานัดหมาย
    const timeInput = document.getElementById('bookingTime');
    if (!timeInput.value) {
      document.getElementById('bookingTimeFeedback').style.display = 'block';
      document.getElementById('bookingTimeGrid').classList.add('border', 'border-danger', 'rounded-2');
      valid = false;
    } else {
      document.getElementById('bookingTimeFeedback').style.display = 'none';
      document.getElementById('bookingTimeGrid').classList.remove('border', 'border-danger', 'rounded-2');
    }
    if (!valid) {
      e.preventDefault();
      Swal.fire({
        icon: 'warning',
        title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
        html: '<div class="text-danger">โปรดตรวจสอบข้อมูลที่มีกรอบแดง</div>',
        customClass: {
          htmlContainer: 'text-danger',
          popup: 'swal2-border-danger'
        },
        confirmButtonText: 'ตกลง',
        confirmButtonColor: '#198754'
      });
      return false;
    }
    // รีเซ็ตฟอร์มเมื่อ modal ปิด
    document.addEventListener('DOMContentLoaded', function () {
      const modalEl = document.getElementById('bookingModal');
      if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function () {
          resetBookingModal();
        });
      }
    });
  });

  // แจ้งเตือนผลลัพธ์การจอง (ไม่รวม login success)
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('booking') === 'success') {
    Swal.fire({
      icon: 'success',
      title: 'จองคิวสำเร็จ!',
      text: 'บันทึกการจองของคุณเรียบร้อยแล้ว',
      showConfirmButton: false,
      timer: 1800
    });
  }
  if (urlParams.get('booking') === 'fail') {
    let reason = urlParams.get('reason');
    let msg = reason === 'out_of_range'
      ? 'วันดังกล่าวอยู่นอกช่วงวันที่เปิดให้จอง กรุณาเลือกวันใหม่'
      : 'มีผู้จองคิวนี้ไปแล้ว กรุณาเลือกเวลาอื่น';
    Swal.fire({
      icon: 'error',
      title: 'จองคิวไม่สำเร็จ',
      text: msg,
      confirmButtonColor: '#d33'
    });
  }

  // if (sessionStorage.getItem('loginSuccess') === '1') {
  //   Swal.fire({
  //     icon: 'success',
  //     title: 'เข้าสู่ระบบสำเร็จ!',
  //     text: 'ยินดีต้อนรับ!',
  //     showConfirmButton: false,
  //     timer: 1800
  //   });
  //   sessionStorage.removeItem('loginSuccess');
  // }
});