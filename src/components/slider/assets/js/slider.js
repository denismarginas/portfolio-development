const SLIDER_TEMPLATE_URL = 'components/slider/assets/html/template.html';

document.addEventListener('DOMContentLoaded', function () {
    const sliders = document.querySelectorAll('.component-slider');

    sliders.forEach(function (slider) {
        const items = slider.querySelectorAll('.slider-item');
        let current = 0;

        function showSlide(index) {
            items.forEach((item, idx) => {
                item.style.display = idx === index ? 'block' : 'none';
            });
        }

        if (items.length > 0) {
            showSlide(current);
            setInterval(function () {
                current = (current + 1) % items.length;
                showSlide(current);
            }, 5000);
        }
    });
});
