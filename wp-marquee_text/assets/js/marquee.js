(function() {
    function initMarqueeClose(container) {
        const btn = container.querySelector('.marquee-close');
        if (!btn) return;

        const hideDays = parseInt(container.dataset.hideDays, 10);
        const id = container.id;
        const KEY = 'marquee_closed_' + id;
        const now = Date.now();

        // Check if already hidden
        let shouldHide = false;
        const stored = localStorage.getItem(KEY);

        if (hideDays === -1) {
            if (stored === 'never') shouldHide = true;
        } else if (hideDays >= 0 && stored) {
            if (now < parseInt(stored)) shouldHide = true;
        }

        if (shouldHide) {
            container.style.display = 'none';
            return;
        }

        btn.addEventListener('click', function() {
            container.style.display = 'none';
            if (hideDays === -1) {
                localStorage.setItem(KEY, 'never');
            } else if (hideDays > 0) {
                localStorage.setItem(KEY, (now + hideDays * 86400000).toString());
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.marquee-container').forEach(initMarqueeClose);
    });
})();