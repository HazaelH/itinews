import { initNavbar } from './modules/navbar.js';
import { initScrolling } from './modules/scrolling.js';
import { initTyped } from './modules/typed.js';
import { initGallery } from './modules/gallery.js';
import { initVideoModal } from './modules/video-modal.js';

(function ($) {
    "use strict";

    initNavbar();
    initScrolling();
    initTyped();
    initGallery();
    initVideoModal();
    
})(jQuery);