import Alpine from "alpinejs";
import FS from "./lib/fs.js";

window.Alpine = Alpine;
Alpine.start()
document.addEventListener('alpine:init', () => {
    const fs=new FS();
    fs.ajaxurl = window.ajaxurl;
    Alpine.store('FS',fs)
})