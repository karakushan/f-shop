import Alpine from "alpinejs";

document.addEventListener('alpine:init', () => {
    Alpine.store('FS', {
        activeTab: localStorage.getItem('activeTab') || 'basic',
        setActiveTab(tab) {
            localStorage.setItem('activeTab', tab);
            this.activeTab = tab;
        }
    })
})

window.Alpine = Alpine;
Alpine.start()

