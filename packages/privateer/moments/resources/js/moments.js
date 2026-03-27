document.addEventListener('alpine:init', () => {
    Alpine.store('momentsLightbox', {
        isOpen: false,
        images: [],
        currentIndex: 0,

        open(images, index = 0) {
            this.images = images;
            this.currentIndex = Math.max(0, Math.min(index, images.length - 1));
            this.isOpen = true;

            const dialog = document.getElementById('lightbox');

            if (dialog && ! dialog.open) {
                dialog.showModal();
            }
        },

        close() {
            const dialog = document.getElementById('lightbox');

            if (dialog?.open) {
                dialog.close();

                return;
            }

            this.reset();
        },

        reset() {
            this.isOpen = false;
            this.images = [];
            this.currentIndex = 0;
        },

        currentImage() {
            return this.images[this.currentIndex] ?? null;
        },

        hasMultiple() {
            return this.images.length > 1;
        },

        canPrev() {
            return this.currentIndex > 0;
        },

        canNext() {
            return this.currentIndex < this.images.length - 1;
        },

        prev() {
            if (! this.canPrev()) {
                return;
            }

            this.currentIndex -= 1;
        },

        next() {
            if (! this.canNext()) {
                return;
            }

            this.currentIndex += 1;
        },

        goTo(index) {
            if (index < 0 || index >= this.images.length) {
                return;
            }

            this.currentIndex = index;
        },

        statusLabel() {
            if (! this.images.length) {
                return '';
            }

            return `Image ${this.currentIndex + 1} of ${this.images.length}`;
        },
    });

    Alpine.data('momentGallery', ({ images }) => ({
        images,
        currentIndex: 0,

        hasMultiple() {
            return this.images.length > 1;
        },

        canPrev() {
            return this.currentIndex > 0;
        },

        canNext() {
            return this.currentIndex < this.images.length - 1;
        },

        prev() {
            if (! this.canPrev()) {
                return;
            }

            this.currentIndex -= 1;
        },

        next() {
            if (! this.canNext()) {
                return;
            }

            this.currentIndex += 1;
        },

        goTo(index) {
            if (index < 0 || index >= this.images.length) {
                return;
            }

            this.currentIndex = index;
        },

        open(index = this.currentIndex) {
            Alpine.store('momentsLightbox').open(this.images, index);
        },

        statusLabel() {
            return `Image ${this.currentIndex + 1} of ${this.images.length}`;
        },
    }));
});
