import Alpine from "alpinejs";
import FS from "./lib/fs.js";

window.Alpine = Alpine;
Alpine.store('FS',FS)
Alpine.start();
document.addEventListener('alpine:init', () => {

})

