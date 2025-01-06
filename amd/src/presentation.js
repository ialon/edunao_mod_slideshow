/**
 * Add navigation functionality to slideshow
 *
 * @module      mod_slideshow/presentation
 * @copyright   2025 Josemaria Bolanos <admin@mako.digital>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const Selectors = {
    slides: '.slide',
    prev: '.prev',
    next: '.next',
    currentSlide: '.currentslide',
    fullscreen: '.fullscreen',
};

/**
 * Initialize module
 * @param {Number} cmid The course module id to setup for
 */
export const init = ({cmid}) => {
    let container = document.querySelector('#slideshow-' + cmid);

    let slides = container.querySelectorAll(Selectors.slides);
    let total = slides.length;
    let current = 0;

    let prev = container.querySelector(Selectors.prev);
    let next = container.querySelector(Selectors.next);
    let currentslide = container.querySelector(Selectors.currentSlide);
    let fullscreen = container.querySelector(Selectors.fullscreen);

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
        } else {
            container.classList.remove('fullscreen');
        }
    });

    prev.addEventListener('click', () => {
        prevSlide();
    });

    next.addEventListener('click', () => {
        nextSlide();
    });

    const prevSlide = () => {
        window.console.log('Prev ' + current);
        if (current > 0) {
            slides[current].classList.add('hidden');
            slides[current - 1].classList.remove('hidden');
            current--;
            updateCurrentSlide();
        }
    };

    const nextSlide = () => {
        window.console.log('next ' + current);
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

    /**
     * Updates the text content of the current slide element to display the current slide number and the total number of slides.
     * @function
     * @name updateCurrentSlide
     * @global
     */
    const updateCurrentSlide = () => {
        currentslide.innerText = (current + 1) + '/' + total;
        if (current == 0) {
            prev.classList.add('disabled');
        } else {
            prev.classList.remove('disabled');
        }
        if (current == total - 1) {
            next.classList.add('disabled');
        } else {
            next.classList.remove('disabled');
        }
    };
};
