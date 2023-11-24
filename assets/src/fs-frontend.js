import Alpine from "alpinejs";
import FS from "./lib/fs.js";

window.Alpine = Alpine;
Alpine.start()
document.addEventListener('alpine:init', () => {
    Alpine.store('FS',new FS)
})