// booking-history.js: custom JS for booking-history.php

document.addEventListener('DOMContentLoaded', function () {
    // แสดง Swal เมื่อยกเลิกสำเร็จ (redirect มาพร้อม cancel_success=1)
    if (window.location.search.indexOf('cancel_success=1') !== -1) {
        setTimeout(function () {
            Swal.fire({
                icon: 'success',
                title: 'ยกเลิกการจองสำเร็จ',
                showConfirmButton: false,
                timer: 1600
            });
        }, 300);
    }

    document.querySelectorAll('.cancel-booking-form').forEach(function (form) {
        form.addEventListener('submit', function handler(e) {
            e.preventDefault();
            Swal.fire({
                title: 'ยืนยันการยกเลิก?',
                text: 'คุณต้องการยกเลิกการจองนี้หรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ใช่, ยกเลิก',
                cancelButtonText: 'ไม่',
                reverseButtons: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.removeEventListener('submit', handler); // สำคัญ!
                    form.submit();
                }
            });
        });
    });

    // Modal print booking
    document.querySelectorAll('.btn-print-booking').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('print-queue').innerText = btn.getAttribute('data-queue') || '';
            document.getElementById('print-name').innerText = btn.getAttribute('data-name');
            document.getElementById('print-date').innerText = btn.getAttribute('data-date');
            document.getElementById('print-time').innerText = btn.getAttribute('data-time');
            document.getElementById('print-note').innerText = btn.getAttribute('data-note');
            var modal = new bootstrap.Modal(document.getElementById('printBookingModal'));
            modal.show();
        });
    });
    document.getElementById('btn-print-modal').addEventListener('click', function () {
        // ปิด modal ก่อนสั่งพิมพ์ เพื่อไม่ให้ backdrop ทับ
        var modalEl = document.getElementById('printBookingModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();
        setTimeout(function () {
            var printArea = document.getElementById('print-preview-area').innerHTML;
            var printWindow = window.open('', '', 'width=900,height=1200');
            printWindow.document.write('<html lang="th"><head><title>พิมพ์ใบจอง</title><style>body{font-family:Sarabun,Tahoma,sans-serif;background:#f9f9f9;}@media print{body *{visibility:hidden;}.form-print,.form-print *{visibility:visible;}.form-print{position:absolute;left:0;top:0;width:100%;border:none;}button{display:none;}}</style></head><body>' + printArea + '</body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.onload = function () {
                setTimeout(function () {
                    printWindow.print();
                }, 200);
            };
        }, 400); // รอ modal ปิดก่อน (400ms)
    });
});
