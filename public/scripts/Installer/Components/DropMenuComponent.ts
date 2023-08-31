import ContainerComponent from "./ContainerComponent"

/**
 * DropMenu config.
 */
export interface DropMenuOptionConfig {
    label: string
    value: Function
    separator?: boolean
    disabled?: boolean
    highlight?: boolean
}

/**
 * DropMenu component class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class DropMenuComponent extends ContainerComponent
{
    /**
     * Dynamic auto-increment id.
     */
    static dropMenuId: number = 0

    /**
     * Open state.
     *
     * @private
     */
    private open: boolean = false

    /**
     * DropMenu Container.
     *
     * @private
     */
    private container: HTMLDivElement = null

    /**
     * Creates a drop menu instance.
     */
    constructor(
        private options: DropMenuOptionConfig[]
    ){
        // Auto-increment id
        DropMenuComponent.dropMenuId++

        // Create container
        super('drop-menu' + DropMenuComponent.dropMenuId)

        // Add class
        this.addClass('drop-menu')

        // Create content
        this.setContent()
    }

    /**
     * Toggle the drop-down list.
     */
    toggle(): void
    {
        this.open = !this.open
        this.template.classList.toggle('open', this.open)
    }

    enableOptions(label: string)
    {
        (<HTMLDivElement> this.container.querySelector(`[data-option-name="${label}"]`))?.classList.remove('disabled')
    }

    disableOptions(label: string)
    {
        const option = <HTMLDivElement> this.container.querySelector(`[data-option-name="${label}"]`)

        option?.classList.add('disabled')
        option?.classList.remove('highlight')

        if(!this.container.querySelector('.highlight'))
        {
            this.template.classList.remove('highlight')
        }
    }

    /**
     * Generates the drop menu template.
     *
     * @private
     */
    private setContent(): void
    {
        this.content(`
            <button id="${this.id}">
                <img src="/bundles/productinstaller/images/icons/menu.svg" alt="⋮"/>
            </button>
            <div class="drop-list"></div>
        `)

        this.container = <HTMLDivElement> this.element('.drop-list')

        // Create options
        for (const opt of this.options)
        {
            const option = <HTMLDivElement> document.createElement('div')

            option.dataset.optionName = opt.label
            option.innerHTML = opt.label

            if(opt.highlight)
            {
                option.classList.add('highlight')
                this.template.classList.add('highlight')
            }

            if(opt.separator)
            {
                option.classList.add('separator')
            }

            if(opt.disabled)
            {
                option.classList.add('disabled')
            }

            option.addEventListener('click', () => {
                if(opt.highlight)
                {
                    option.classList.remove('highlight')

                    if(!this.container.querySelector('.highlight'))
                    {
                        this.template.classList.remove('highlight')
                    }
                }

                this.toggle()
                opt.value.call(this)
            })

            this.container.append(option)
        }

        // Add toggle and close event
        this.element(`button#${this.id}`)
            .addEventListener('click', () => this.toggle())

        // Close drop-list when click outside
        document.addEventListener('click', (e: MouseEvent) => {
            if(this.open)
            {
                const element = e.target as Element

                if(element.id !== this.id && element.closest('#' + this.id) === null)
                {
                    this.toggle()
                }
            }
        })
    }
}
