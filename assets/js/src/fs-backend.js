import Alpine from "alpinejs";
import FS from "./lib/fs.js";

const fs=new FS();
fs.ajaxurl=window.FS_BACKEND.ajaxUrl;
fs.nonce=window.FS_BACKEND.nonce;

window.Alpine = Alpine;
document.addEventListener('alpine:init', () => {
    Alpine.store('FS',fs)
})

Alpine.start()