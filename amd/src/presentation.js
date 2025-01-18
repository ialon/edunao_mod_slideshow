/**
 * Add navigation functionality to slideshow
 *
 * @module      mod_slideshow/presentation
 * @copyright   2025 Josemaria Bolanos <admin@mako.digital>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {
    const Selectors = {
        slides: '.slide',
        prev: '.prev',
        next: '.next',
        currentSlide: '.currentslide',
        fullscreen: '.fullscreen',
        fontsize: '.fontsize',
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
            let fullscreen = container.querySelector(Selectors.fullscreen);
            let fontsize = container.querySelector(Selectors.fontsize);

            // Edit slide button
            let editslide = document.querySelector(Selectors.editslide);

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

            document.addEventListener('fullscreenchange', () => {
                if (document.fullscreenElement) {
                    container.classList.add('fullscreen');
                    fontsize.value = '300';
                } else {
                    container.classList.remove('fullscreen');
                    fontsize.value = '150';
                }
                fontsize.dispatchEvent(new Event('input', { 'bubbles': true }));
            });

            prev.addEventListener('click', () => {
                prevSlide();
            });

            next.addEventListener('click', () => {
                nextSlide();
            });

            const prevSlide = () => {
                // window.console.log('Prev ' + current);
                if (current > 0) {
                    slides[current].classList.add('hidden');
                    slides[current - 1].classList.remove('hidden');
                    current--;
                    updateCurrentSlide();
                }
            };

            const nextSlide = () => {
                // window.console.log('next ' + current);
                if (current < total - 1) {
                    slides[current].classList.add('hidden');
                    slides[current + 1].classList.remove('hidden');
                    current++;
                    updateCurrentSlide();
                }
            };

            fullscreen.addEventListener('click', () => {
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                } else {
                    container.requestFullscreen();
                }
            });

            fontsize.addEventListener('input', function() {
                container.style.fontSize = this.value +"%";
            });

            editslide.addEventListener('click', () => {
                editSlide();
            });

            /**
             * Navigates to the slide edit page for the current slide.
             */
            const editSlide = () => {
                let slideid = slides[current].getAttribute('data-slideid');
                window.location.href = '/mod/slideshow/edit.php?cm=' + options.cmid + '&id=' + slideid;
            };

            /**
             * Updates the text content of the current slide element to display the current slide number and the total number of slides.
             * @function
             * @name updateCurrentSlide
             * @global
             */
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

            window.addEventListener('resize', () => {
                updateSlideHeight();
            }, true);

            /**
             * Updates the height of the slide container based on the current slide's width.
             * The height is set to maintain a 16:9 aspect ratio.
             */
            const updateSlideHeight = () => {
                console.log("updateSlideHeight");
                slides.forEach(slide => {
                    slide.setAttribute('style', 'height: ' + slide.offsetWidth * (9/16) + 'px;');
                });
            };

            // Set inital slide height
            updateSlideHeight();
        }
    };
});

