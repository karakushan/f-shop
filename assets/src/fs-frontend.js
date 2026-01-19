window.noUiSlider = require("nouislider");

// Import Swiper and modules
import Swiper from 'swiper';
import { Navigation, Pagination, Thumbs } from 'swiper/modules';

// Import Swiper styles
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/thumbs';

// Expose Swiper globally for use in inline scripts
window.Swiper = Swiper;
window.SwiperNavigation = Navigation;
window.SwiperPagination = Pagination;
window.SwiperThumbs = Thumbs;

import Alpine from "alpinejs";

window.Alpine = Alpine;

// phone mask plugin
import mask from "@alpinejs/mask";

Alpine.plugin(mask);

// plugin libs
import FS from "./lib/fs.js";

Alpine.store("FS", new FS(false));
Alpine.start();
