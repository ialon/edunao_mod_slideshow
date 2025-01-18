/**
 * Add navigation functionality to slideshow
 *
 * @module      mod_slideshow/presentation
 * @copyright   2025 Josemaria Bolanos <admin@mako.digital>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'qrcode',
], function(QRCode) {
    const Selectors = {
        slides: '.slide',
        prev: '.prev',
        next: '.next',
        currentSlide: '.currentslide',
        fontsize: '.fontsize',
        overlay: '.overlay',
        qrcode: '.qrcode',
        fullscreen: '.fullscreen',
        editslide: '.editslide',
    };

    return {
        /**
         * Initialize module
         * @param {Object} options Configuration options
         * @param {Number} options.cmid The course module id to setup for
         */
        init: function(options) {
            let container = document.querySelector('#slideshow-' + options.cmid);

            let slides = container.querySelectorAll(Selectors.slides);
            let total = slides.length;
            let current = 0;

            // Navigation buttons
            let prev = container.querySelector(Selectors.prev);
            let next = container.querySelector(Selectors.next);
            let currentslide = container.querySelector(Selectors.currentSlide);
            let overlay = container.querySelector(Selectors.overlay);
            let fontsize = container.querySelector(Selectors.fontsize);
            let fullscreen = container.querySelector(Selectors.fullscreen);

            // Edit slide button
            let editslide = document.querySelector(Selectors.editslide);

            // Set width and height for the QR Code container
            overlay.setAttribute('style', 'width: ' + container.offsetWidth + 'px; height: ' + container.offsetWidth * (9/16) + 'px;');

            // Hide overlay on click
            overlay.addEventListener('click', () => {
                overlay.classList.toggle('hidden');
            });

            // Enrol URL is set, add QR logic
            if (options.enrolurl) {
                let qrcode = container.querySelector(Selectors.qrcode);

                // Toggle overlay button
                qrcode.addEventListener('click', () => {
                    overlay.classList.toggle('hidden');
                });

                // Generate QR code
                new QRCode(overlay, {
                    text: options.enrolurl,
                    width: container.offsetHeight * 0.5,
                    height: container.offsetHeight * 0.5,
                });
            }

            // Navigate slides with arrow keys
            document.addEventListener('keyup', (e) => {
                if (document.fullscreenElement) {
                    if (e.key === 'ArrowLeft') {
                        prevSlide();
                    }
                    if (e.key === 'ArrowRight') {
                        nextSlide();
                    }
                }
            });

            // Listen for fullscreen change event
            document.addEventListener('fullscreenchange', () => {
                if (document.fullscreenElement) {
                    container.classList.add('fullscreen');
                    fontsize.value = '300';
                } else {
                    container.classList.remove('fullscreen');
                    fontsize.value = '150';
                }
                fontsize.dispatchEvent(new Event('input', { 'bubbles': true }));
                updateSlideDimensions();
            });

            // Previous slide button
            prev.addEventListener('click', () => {
                prevSlide();
            });

            // Navigate to the previous slide
            const prevSlide = () => {
                if (current > 0) {
                    slides[current].classList.add('hidden');
                    slides[current - 1].classList.remove('hidden');
                    current--;
                    updateCurrentSlide();
                }
            };

            // Next slide button
            next.addEventListener('click', () => {
                nextSlide();
            });

            // Navigate to the next slide
            const nextSlide = () => {
                if (current < total - 1) {
                    slides[current].classList.add('hidden');
                    slides[current + 1].classList.remove('hidden');
                    current++;
                    updateCurrentSlide();
                }
            };

            // Fullscreen button
            fullscreen.addEventListener('click', () => {
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                } else {
                    container.requestFullscreen();
                }
            });

            // Font size slider
            fontsize.addEventListener('input', function() {
                container.style.fontSize = this.value +"%";
            });

            // Edit slide button
            editslide.addEventListener('click', () => {
                editSlide();
            });
            
            // Navigate to edit page for the current slide
            const editSlide = () => {
                let slideid = slides[current].getAttribute('data-slideid');
                window.location.href = '/mod/slideshow/edit.php?cm=' + options.cmid + '&id=' + slideid;
            };

            // Set the current slide indicator
            const updateCurrentSlide = () => {
                currentslide.innerText = (current + 1) + '/' + total;
                if (current === 0) {
                    prev.classList.add('disabled');
                } else {
                    prev.classList.remove('disabled');
                }
                if (current === total - 1) {
                    next.classList.add('disabled');
                } else {
                    next.classList.remove('disabled');
                }
            };

            // Resize slide to keep a 16:9 aspect ratio
            window.addEventListener('resize', () => {
                updateSlideDimensions();
            }, true);

            /**
             * Updates the height of the slide container based on the current slide's width.
             * The height is set to maintain a 16:9 aspect ratio.
             */
            const updateSlideDimensions = () => {
                // QR Code overlay
                overlay.setAttribute('style', 'width: ' + container.offsetWidth + 'px; height: ' + container.offsetWidth * (9/16) + 'px;');

                // Slides
                slides.forEach(slide => {
                    slide.setAttribute('style', 'height: ' + container.offsetWidth * (9/16) + 'px;');
                });
            };

            // Set inital slide dimensions
            updateSlideDimensions();
        }
    };
});

