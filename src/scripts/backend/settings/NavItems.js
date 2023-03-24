import capitalize from '../../helpers/capitalize'

export default class NavItems {
    constructor(location) {
        this.location = location
            ? location
            : window.location.hash ? window.location.hash.substr(1) : 'general'
        this.optionItems = document.querySelectorAll('.gbbot-settings [data-nav]')
        this.createNav()
        this.navItems = document.querySelectorAll('.nav-tab')
        
        this.navItems.forEach((navItem) => {
            navItem.addEventListener("click", () => {
                this.setActiveClass(navItem.dataset.tab) 
            })
        })
    }
    createNav() {
        const navWrapper = document.querySelector('.gbbot-nav-tab')
        this.optionItems.forEach( (optionItem) => {
            const a = document.createElement('a')
            a.setAttribute('href', '#' + optionItem.dataset.nav)
            a.setAttribute('class', 'nav-tab')
            a.setAttribute('data-tab', optionItem.dataset.nav)
            a.innerHTML = capitalize(optionItem.dataset.nav)
            navWrapper.appendChild(a)
        })
    }

    setActiveClass(activeItem = this.location) {
        this.optionItems.forEach((optionItem) => {
            optionItem.classList.remove('settings-active')
        })
        this.navItems.forEach((navItem) => {
            navItem.classList.remove('nav-tab-active')
            if (navItem.dataset.tab == activeItem && !navItem.classList.contains('nav-tab-active')) {
                document.querySelector('[data-nav="' + activeItem + '"').classList.add('settings-active')
                navItem.classList.add('nav-tab-active')
            }
        })
    }
}
