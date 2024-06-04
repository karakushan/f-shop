import Alpine from "alpinejs";
window.Alpine = Alpine;


// phone mask plugin
import mask from '@alpinejs/mask';
Alpine.plugin(mask);

// plugin libs
import FS from "./lib/fs.js";

Alpine.store('FS',new FS(false));
Alpine.start();
