document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loginForm');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        // ลบกรอบแดงก่อน
        document.getElementById('username').classList.remove('is-invalid');
        document.getElementById('password').classList.remove('is-invalid');
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();
        let hasError = false;
        if (!username) {
            document.getElementById('username').classList.add('is-invalid');
            hasError = true;
        }
        if (!password) {
            document.getElementById('password').classList.add('is-invalid');
            hasError = true;
        }
        if (hasError) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                text: 'โปรดกรอกชื่อผู้ใช้และรหัสผ่าน',
                confirmButtonColor: '#d33'
            });
            return;
        }
        // ถ้ากรอกครบ ให้ submit ฟอร์มไปที่ PHP ตามปกติ (ไม่ต้อง Swal success/redirect JS)
    });

    // ถ้ามี error จาก PHP ให้แสดง Swal และกรอบแดง
    var errorMsgInput = document.getElementById('loginErrorMsg');
    if (errorMsgInput && errorMsgInput.value) {
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: errorMsgInput.value,
            confirmButtonColor: '#d33'
        });
        document.getElementById('username').classList.add('is-invalid');
        document.getElementById('password').classList.add('is-invalid');
    }
    // เพิ่ม CSS ถ้ายังไม่มี
    if (!document.getElementById('swal-invalid-style')) {
        const style = document.createElement('style');
        style.id = 'swal-invalid-style';
        style.innerHTML = `.is-invalid { border: 1.5px solid #dc3545 !important; box-shadow: 0 0 0 0.15rem rgba(220,53,69,.15) !important; }`;
        document.head.appendChild(style);
    }
});


