import Installer from "./Installer/Installer"

const bindProductInstaller = (pi) => {
    document.getElementById('product-installer')?.addEventListener('click', (e) => {
        e.preventDefault()
        pi.open()
    })
}

document.addEventListener('DOMContentLoaded', () => {
    const pi = new Installer('de')
    bindProductInstaller(pi)
})

document.addEventListener("turbo:load", () => {
    const pi = new Installer('de')
    bindProductInstaller(pi)
});
