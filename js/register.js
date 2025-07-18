// JS สำหรับ Register (validation + SweetAlert2)
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        // ถ้า form ถูก submit ผ่านปุ่ม submit (ไม่ใช่ JS), ไม่ต้อง e.preventDefault()
        if (window.fetch) {
            e.preventDefault();
        } else {
            return; // fallback ปล่อยให้ form submit ปกติ
        }
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();
        // ลบกรอบแดงก่อนทุกครั้ง
        document.getElementById('username').classList.remove('is-invalid');
        document.getElementById('email').classList.remove('is-invalid');
        document.getElementById('password').classList.remove('is-invalid');

        let hasError = false;
        if (!username) {
            document.getElementById('username').classList.add('is-invalid');
            hasError = true;
        }
        // ตรวจสอบ email format
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email) {
            document.getElementById('email').classList.add('is-invalid');
            hasError = true;
        } else if (!emailPattern.test(email)) {
            document.getElementById('email').classList.add('is-invalid');
            Swal.fire({
                icon: 'warning',
                title: 'รูปแบบอีเมลไม่ถูกต้อง',
                text: 'โปรดกรอกอีเมลให้ถูกต้อง เช่น user@email.com',
                confirmButtonColor: '#6366f1'
            });
            return;
        }
        if (!password) {
            document.getElementById('password').classList.add('is-invalid');
            hasError = true;
        }
        if (hasError) {
            Swal.fire({
                icon: 'warning',
                title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                text: 'โปรดกรอกชื่อผู้ใช้ อีเมล และรหัสผ่าน',
                confirmButtonColor: '#6366f1'
            });
            return;
        }

        // ส่งข้อมูลไปยัง server แบบ AJAX
        fetch('register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
        })
        .then(res => res.text())
        .then(html => {
            // ดึง script ที่ฝังมาจาก PHP แล้ว eval เพื่อให้ postMessage ทำงาน (รองรับ iframe หรือ window ปกติ)
            const temp = document.createElement('div');
            temp.innerHTML = html;
            const scripts = temp.querySelectorAll('script');
            scripts.forEach(s => {
                try { eval(s.innerText); } catch(e){}
            });
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้' });
        });
    });

    // รับ postMessage จาก register.php (Swal)
    window.addEventListener('message', function(event) {
        if (!event.data || !event.data.swalType) return;
        if (event.data.swalType === 'success') {
            Swal.fire({
                icon: 'success',
                title: event.data.title || '',
                text: event.data.text || '',
                showConfirmButton: false,
                timer: 1800
            }).then(() => {
                window.location.href = 'login.php';
            });
        } else if (event.data.swalType === 'error') {
            Swal.fire({
                icon: 'error',
                title: event.data.title || '',
                text: event.data.text || '',
                confirmButtonColor: '#d33'
            });
        }
    });
// เพิ่ม CSS ถ้ายังไม่มี
if (!document.getElementById('swal-invalid-style')) {
    const style = document.createElement('style');
    style.id = 'swal-invalid-style';
    style.innerHTML = `.is-invalid { border: 1.5px solid #dc3545 !important; box-shadow: 0 0 0 0.15rem rgba(220,53,69,.15) !important; }`;
    document.head.appendChild(style);
}
});
