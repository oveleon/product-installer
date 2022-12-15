import Installer from "./Installer/Installer"

document.addEventListener('DOMContentLoaded', () => {
    const installer = new Installer('de') // ToDo: set locale

    document.getElementById('product-installer')?.addEventListener('click', (e) => {
        e.preventDefault()
        installer.open()
    })
})
