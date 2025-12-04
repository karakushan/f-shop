window.noUiSlider = require("nouislider");

// Import Swiper CSS and JS
import Swiper from "swiper";
import { Navigation, Pagination } from "swiper/modules";
import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";

// Make Swiper available globally
if (typeof window !== "undefined") {
  window.Swiper = Swiper;
  window.Navigation = Navigation;
  window.Pagination = Pagination;
}

import Alpine from "alpinejs";

window.Alpine = Alpine;

// phone mask plugin
import mask from "@alpinejs/mask";

Alpine.plugin(mask);

// plugin libs
import FS from "./lib/fs.js";

Alpine.store("FS", new FS(false));
Alpine.start();
