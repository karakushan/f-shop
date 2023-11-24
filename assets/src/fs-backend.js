import Alpine from "alpinejs";
import FS from "./lib/fs.js";

const fs=new FS(true);
window.FS=fs

window.Alpine = Alpine;
document.addEventListener('alpine:init', () => {
    Alpine.store('FS',fs)
})

Alpine.start()