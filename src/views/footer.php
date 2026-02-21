</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    const toast = document.getElementById('app-toast');
    const toastWrap = document.getElementById('app-toast-wrap');
    if (toast && toastWrap) {
        const updateToastOffset = () => {
            const vv = window.visualViewport;
            if (!vv) return;
            const occludedBottom = Math.max(0, window.innerHeight - (vv.height + vv.offsetTop));
            toastWrap.style.bottom = `${14 + occludedBottom}px`;
        };

        updateToastOffset();
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', updateToastOffset);
            window.visualViewport.addEventListener('scroll', updateToastOffset);
        }

        requestAnimationFrame(() => {
            toast.classList.add('toast-show');
        });

        setTimeout(() => {
            toast.classList.remove('toast-show');
            toast.classList.add('toast-hide');
        }, 1000);

        setTimeout(() => {
            if (window.visualViewport) {
                window.visualViewport.removeEventListener('resize', updateToastOffset);
                window.visualViewport.removeEventListener('scroll', updateToastOffset);
            }
            toastWrap.remove();
        }, 2050);
    }

    const formatUtc = (value) => {
        if (!value) return '';
        const iso = String(value).replace(' ', 'T') + 'Z';
        const date = new Date(iso);
        if (Number.isNaN(date.getTime())) return String(value);
        return date.toLocaleString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
    };

    document.querySelectorAll('[data-utc-datetime]').forEach((el) => {
        const raw = el.getAttribute('data-utc-datetime');
        const next = formatUtc(raw);
        if (next) el.textContent = next;
    });

    const localDate = new Date();
    const y = localDate.getFullYear();
    const m = String(localDate.getMonth() + 1).padStart(2, '0');
    const d = String(localDate.getDate()).padStart(2, '0');
    const todayLocal = `${y}-${m}-${d}`;

    document.querySelectorAll('input[data-fill-local-date=\"true\"]').forEach((el) => {
        if (!el.value) {
            el.value = todayLocal;
        }
    });
})();
</script>
</body>
</html>
