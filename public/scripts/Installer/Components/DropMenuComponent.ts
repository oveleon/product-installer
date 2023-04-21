import ContainerComponent from "./ContainerComponent"

/**
 * DropMenu config.
 */
export interface DropMenuOptionConfig {
    label: string
    value: Function

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

    /**
     * Generates the drop menu template.
     *
     * @private
     */
    private setContent(): void
    {
        this.content(`
            <button>
                <img src="/bundles/productinstaller/icons/menu.svg" alt="⋮"/>
            </button>
            <div class="drop-list"></div>
        `)

        const dropList = this.element('.drop-list')

        // Create options
        for (const opt of this.options)
        {
            const option = <HTMLDivElement> document.createElement('div')

            option.innerHTML = opt.label

            if(opt.highlight)
            {
                option.classList.add('highlight')
                this.template.classList.add('highlight')
            }

            if(opt.disabled)
            {
                option.classList.add('disabled')
            }

            option.addEventListener('click', () => {
                if(opt.highlight)
                {
                    option.classList.remove('highlight')

                    if(!dropList.querySelector('.highlight'))
                    {
                        this.template.classList.remove('highlight')
                    }
                }

                this.toggle()
                opt.value.call(this)
            })

            dropList.append(option)
        }

        // Add toggle and close event
        this.element('button')
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
