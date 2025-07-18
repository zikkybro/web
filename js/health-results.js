// JS เฉพาะสำหรับหน้า health-results.php
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('searchResultInput');
    const dateInput = document.getElementById('searchDateInput');
    const showAllBtn = document.getElementById('showAllBtn');
    function filterResults() {
        const q = input.value.trim().toLowerCase();
        const dateVal = dateInput.value;
        document.querySelectorAll('.health-result-card').forEach(function (card) {
            // ตรวจสอบวันที่
            let cardDate = card.querySelector('.badge.bg-primary, .badge.bg-primary.bg-gradient')?.textContent?.trim() || '';
            let matchDate = true;
            if (dateVal) {
                // แปลง dateVal (yyyy-mm-dd) เป็น d/m/Y
                const [y, m, d] = dateVal.split('-');
                const dateStr = `${d}/${m}/${y}`;
                matchDate = cardDate.indexOf(dateStr) !== -1;
            }
            let found = false;
            card.querySelectorAll('.health-result-table tbody tr').forEach(function (tr) {
                const text = tr.innerText.toLowerCase();
                if ((q === '' || text.indexOf(q) !== -1) && matchDate) {
                    tr.style.display = '';
                    found = true;
                } else {
                    tr.style.display = 'none';
                }
            });
            // ซ่อนทั้ง card ถ้าไม่เจอเลย หรือไม่ตรงวันที่
            card.style.display = (found && matchDate) || (q === '' && !dateVal) ? '' : 'none';
        });
    }
    input.addEventListener('input', filterResults);
    dateInput.addEventListener('input', filterResults);
    showAllBtn.addEventListener('click', function () {
        input.value = '';
        dateInput.value = '';
        filterResults();
        input.focus();
    });
});
