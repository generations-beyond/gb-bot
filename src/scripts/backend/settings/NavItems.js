export default class NavItems {
    constructor(location) {
        this.location = location
            ? window.location.hash ? window.location.hash.substr(1) : 'general'
            : 'general'
        this.navItems = document.querySelectorAll('.nav-tab')
        this.optionItems = document.querySelectorAll('.settings-form-options')

        this.navItems.forEach((navItem) => {
            navItem.addEventListener("click", () => {
                this.setActiveClass(navItem.dataset.tab) 
            })
        })
        
    }

    setActiveClass(activeItem = this.location) {
        this.optionItems.forEach((optionItem) => {
            optionItem.classList.remove('settings-active')
        })
        this.navItems.forEach((navItem) => {
            navItem.classList.remove('nav-tab-active')
            if (navItem.dataset.tab == activeItem && !navItem.classList.contains('nav-tab-active')) {
                console.log(navItem)
                document.querySelector('#' + activeItem + '-options').classList.add('settings-active')
                navItem.classList.add('nav-tab-active')
            }
        })
    }
}
