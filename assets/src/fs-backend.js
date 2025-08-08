import Alpine from "alpinejs";
import FS from "./lib/fs.js";

window.Alpine = Alpine;
Alpine.store("FS", new FS(true));
Alpine.start();
