/**
 * script.js
 * ------------------------------------------------------------
 * General site-wide behaviour: sidebar toggle (mobile),
 * delete confirmation, auto-hiding alerts, table search filter.
 * ------------------------------------------------------------
 */

document.addEventListener('DOMContentLoaded', function () {

    /* ---------- Mobile sidebar toggle ---------- */
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
        document.addEventListener('click', function (e) {
            if (window.innerWidth <= 768 &&
                !sidebar.contains(e.target) &&
                !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    /* ---------- Delete confirmation ---------- */
    document.querySelectorAll('.confirm-delete').forEach(function (el) {
        el.addEventListener('click', function (e) {
            const name = el.getAttribute('data-name') || 'this record';
            if (!confirm('Are you sure you want to delete "' + name + '"? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    /* ---------- Auto-hide alerts after 4 seconds ---------- */
    document.querySelectorAll('.alert').forEach(function (alertBox) {
        setTimeout(function () {
            alertBox.style.transition = 'opacity .4s ease';
            alertBox.style.opacity = '0';
            setTimeout(function () { alertBox.remove(); }, 400);
        }, 4000);
    });

    /* ---------- Live client-side table search filter ---------- */
    const liveFilter = document.getElementById('liveTableFilter');
    if (liveFilter) {
        liveFilter.addEventListener('keyup', function () {
            const query = liveFilter.value.toLowerCase();
            const rows = document.querySelectorAll('#dataTable tbody tr');
            rows.forEach(function (row) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    /* ---------- Auto-calc due date (14 days after issue date) ---------- */
    const issueDateInput = document.getElementById('issue_date');
    const dueDateInput = document.getElementById('due_date');
    if (issueDateInput && dueDateInput) {
        issueDateInput.addEventListener('change', function () {
            if (issueDateInput.value) {
                const issueDate = new Date(issueDateInput.value);
                issueDate.setDate(issueDate.getDate() + 14);
                dueDateInput.value = issueDate.toISOString().split('T')[0];
            }
        });
    }
});
