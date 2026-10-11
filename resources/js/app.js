

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Navigasi layar kecil: kunci gulir halaman dan jaga fokus di dalam menu.
Alpine.data('simNavigation', () => ({
    sidebarOpen: false,
    desktop: false,
    breakpoint: null,
    onBreakpoint: null,
    init() {
        this.breakpoint = window.matchMedia('(min-width: 1024px)');
        this.desktop = this.breakpoint.matches;
        this.onBreakpoint = (event) => {
            this.desktop = event.matches;
            this.closeSidebar(false);
        };
        this.breakpoint.addEventListener('change', this.onBreakpoint);
    },
    openSidebar() {
        this.sidebarOpen = true;
        document.body.classList.add('sim-menu-open');
        this.$nextTick(() => this.$refs.sidebarClose.focus());
    },
    closeSidebar(restoreFocus = true) {
        const wasOpen = this.sidebarOpen;
        this.sidebarOpen = false;
        document.body.classList.remove('sim-menu-open');
        if (wasOpen && restoreFocus && !this.desktop) {
            this.$nextTick(() => this.$refs.sidebarToggle.focus());
        }
    },
    trapFocus(event) {
        if (this.desktop || !this.sidebarOpen) return;
        const elements = [...this.$refs.sidebar.querySelectorAll('a[href], button:not([disabled]), [tabindex="0"]')]
            .filter((element) => element.getClientRects().length);
        const first = elements[0];
        const last = elements.at(-1);
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last?.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first?.focus();
        }
    },
    destroy() {
        this.breakpoint.removeEventListener('change', this.onBreakpoint);
        document.body.classList.remove('sim-menu-open');
    },
}));

Alpine.start();
