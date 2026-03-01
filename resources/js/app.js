import './bootstrap';

window.openLightbox = function (src) {
    document.getElementById('lightbox-image').src = src;
    document.getElementById('lightbox').showModal();
};
