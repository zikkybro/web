// JS เฉพาะของ queue_manage.php (Swal validate, sessionStorage, etc.)
document.addEventListener('DOMContentLoaded', function () {
    // แสดง Swal เมื่อบันทึกผลตรวจสำเร็จ (ไม่เปิด modal ซ้ำ)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
        setTimeout(function () {
            Swal.fire({
                icon: 'success',
                title: 'บันทึกผลตรวจสำเร็จ',
                text: 'ข้อมูลผลตรวจถูกบันทึกเรียบร้อย',
                showConfirmButton: false,
                timer: 1500
            });
        }, 300);
    }
    // ก่อน submit ฟอร์ม ให้เก็บค่าทั้งหมดไว้ใน sessionStorage (แบบเก็บทุก booking_id)
    const form = document.getElementById('attachResultForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            // ตรวจสอบฟอร์มก่อน submit ถ้ากรอกไม่ครบให้ Swal แจ้งเตือน
            let valid = true;
            // ตรวจสอบทุกแถวในตารางผลตรวจ
            document.querySelectorAll('#resultDetailsTable tbody tr').forEach(function (row) {
                const name = row.querySelector('select[name="detail_name[]"]');
                const target = row.querySelector('input[name="detail_target[]"]');
                const result = row.querySelector('input[name="detail_result[]"]');
                const unit = row.querySelector('input[name="detail_unit[]"]');
                const status = row.querySelector('select[name="detail_status[]"]');
                // รีเซ็ตสี
                name.classList.remove('is-invalid');
                target.classList.remove('is-invalid');
                result.classList.remove('is-invalid');
                unit.classList.remove('is-invalid');
                status.classList.remove('is-invalid');
                // เช็คแต่ละช่อง
                if (!name.value) { name.classList.add('is-invalid'); valid = false; }
                if (!target.value) { target.classList.add('is-invalid'); valid = false; }
                if (!result.value) { result.classList.add('is-invalid'); valid = false; }
                if (!unit.value) { unit.classList.add('is-invalid'); valid = false; }
                if (!status.value) { status.classList.add('is-invalid'); valid = false; }
            });
            // ตรวจสอบ doctor_comment (ถ้าต้องการบังคับ)
            // const doctorComment = document.getElementById('doctor_comment');
            // if (!doctorComment.value) { doctorComment.classList.add('is-invalid'); valid = false; }
            if (!valid) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                    text: 'ช่องที่ขาดจะมีขอบสีแดง',
                    confirmButtonColor: '#d33',
                    customClass: { popup: 'swal2-border-red' }
                });
                return false;
            }
            const details = [];
            document.querySelectorAll('#resultDetailsTable tbody tr').forEach(function (row) {
                details.push({
                    name: row.querySelector('select[name="detail_name[]"]').value,
                    target: row.querySelector('input[name="detail_target[]"]').value,
                    result: row.querySelector('input[name="detail_result[]"]').value,
                    unit: row.querySelector('input[name="detail_unit[]"]').value,
                    status: row.querySelector('select[name="detail_status[]"]').value
                });
            });
            const formData = {
                booking_id: document.getElementById('modalBookingId').value,
                patient_name: document.getElementById('modalPatientName').innerText,
                booking_date: document.getElementById('modalBookingDate').innerText,
                doctor_comment: document.getElementById('doctor_comment').value,
                details: details
            };
            let allForms = {};
            try { allForms = JSON.parse(sessionStorage.getItem('allAttachResultForms') || '{}'); } catch (e) { allForms = {}; }
            allForms[formData.booking_id] = formData;
            sessionStorage.setItem('allAttachResultForms', JSON.stringify(allForms));
        });
    }
});

// ฟังก์ชันเติมข้อมูลผลตรวจจากฐานข้อมูล (API)
async function fillAttachResultModalFromDB(bookingId, patientName, bookingDate) {
    document.getElementById('modalBookingId').value = bookingId || '';
    document.getElementById('modalPatientName').innerText = patientName || '';
    document.getElementById('modalBookingDate').innerText = bookingDate || '';
    document.getElementById('doctor_comment').value = '';
    // ลบแถวเดิม เหลือ 1 แถว
    const tbody = document.querySelector('#resultDetailsTable tbody');
    while (tbody.rows.length > 1) tbody.deleteRow(1);
    // ดึงข้อมูลจาก API
    try {
        const res = await fetch('api_get_result.php?booking_id=' + encodeURIComponent(bookingId));
        const data = await res.json();
        // เติม doctor_comment เสมอ (แม้ไม่มีผลตรวจ)
        document.getElementById('doctor_comment').value = data.doctor_comment || '';
        // เติมข้อมูลผลตรวจ ถ้ามี
        if (data.success && Array.isArray(data.results) && data.results.length > 0) {
            for (let i = 0; i < data.results.length; i++) {
                if (i > 0) document.getElementById('addResultRow').click();
                const row = tbody.rows[i];
                if (!row) continue;
                row.querySelector('select[name="detail_name[]"]').value = data.results[i].detail_name;
                row.querySelector('input[name="detail_target[]"]').value = data.results[i].detail_target;
                row.querySelector('input[name="detail_result[]"]').value = data.results[i].detail_result;
                row.querySelector('input[name="detail_unit[]"]').value = data.results[i].detail_unit;
                row.querySelector('select[name="detail_status[]"]').value = data.results[i].detail_status;
            }
        }
    } catch (e) {
        // ไม่เติมอะไร
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Hook ปุ่มแนบผลตรวจ (attach-result-btn) ให้เติมข้อมูลจากฐานข้อมูล
    document.querySelectorAll('.attach-result-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var bookingId = btn.getAttribute('data-bookingid');
            var patientName = btn.getAttribute('data-patientname');
            var bookingDate = btn.getAttribute('data-bookingdate');
            setTimeout(function () {
                fillAttachResultModalFromDB(bookingId, patientName, bookingDate);
            }, 200); // รอ modal เปิด
        });
    });
});
