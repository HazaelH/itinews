import { initNavbar } from '../../public/js/modules/navbar.js';
import { initScrolling } from '../../public/js/modules/scrolling.js';
import { initTyped } from '../../public/js/modules/typed.js';
import { initGallery } from '../../public/js/modules/gallery.js';
import { initVideoModal } from '../../public/js/modules/video-modal.js';


(function ($) {
    "use strict";

    initNavbar();
    initScrolling();
    initTyped();
    initGallery();
    initVideoModal();
    
})(jQuery);