// admin.js: Custom JS for all admin pages (badge, bell, realtime, etc.)
document.addEventListener('DOMContentLoaded', function() {
  // Bell notification badge logic (for all admin pages)
  var notiBadge = document.getElementById('notiBadge');
  var notiBtn = document.getElementById('notiBtn');
  function updateNotiBadge() {
    const todayStr = new Date().toISOString().slice(0,10);
    fetch('api_dashboard_today.php?date=' + encodeURIComponent(todayStr))
      .then(res => res.json())
      .then(data => {
        if (notiBadge) notiBadge.textContent = data.queueToday > 0 ? data.queueToday : '';
      })
      .catch(() => { if (notiBadge) notiBadge.textContent = ''; });
  }
  if (notiBadge) {
    updateNotiBadge();
    setInterval(updateNotiBadge, 10000);
  }
  if (notiBtn) {
    notiBtn.addEventListener('click', function(e) {
      e.preventDefault();
      const todayStr = new Date().toISOString().slice(0,10);
      fetch('api_dashboard_today.php?date=' + encodeURIComponent(todayStr))
        .then(res => res.json())
        .then(data => {
          if (!Array.isArray(data.list) || data.list.length === 0) {
            Swal.fire({
              icon: 'info',
              title: 'แจ้งเตือน',
              text: 'วันนี้ยังไม่มีคิว',
              confirmButtonColor: '#2563eb'
            });
          } else {
            Swal.fire({
              icon: 'info',
              title: 'คิวของวันนี้',
              html: '<ul style="text-align:left;max-height:300px;overflow:auto;padding-left:1.2em;">'+data.list.map(q=>'<li>'+q.time+' - '+q.name+'</li>').join('')+'</ul>',
              confirmButtonColor: '#2563eb',
              width: 420
            });
          }
        })
        .catch(() => {
          Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: 'ไม่สามารถโหลดข้อมูลคิววันนี้ได้',
            confirmButtonColor: '#d33'
          });
        });
    });
  }
});
