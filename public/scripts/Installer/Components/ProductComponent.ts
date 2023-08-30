import ContainerComponent from "./ContainerComponent"
import {i18n} from "../Language"
import DropMenuComponent from "./DropMenuComponent";

/**
 * Product config.
 */
export interface ProductOptions {
    title: string,
    type: string,
    description: string

    hash?: string,
    registered?: boolean
    skip?: boolean
    remove?: boolean        // Product was removed
    setup?: boolean         // If false, product need a setup
    updated?: number
    version?: string
    connectorImage?: string
    latestVersion?: string
    image?: string,
    package?: ProductOptions[],
}

/**
 * Product component class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ProductComponent extends ContainerComponent
{
    /**
     * Dynamic auto-increment id.
     */
    static productId: number = 0

    private menu: DropMenuComponent = null
    private selectable: boolean = false
    private isChecked: boolean = false
    private selectFn: Function

    /**
     * Creates a product component instance.
     */
    constructor(protected product: ProductOptions)
    {
        // Auto-increment id
        ProductComponent.productId++

        // Create container
        super('product' + ProductComponent.productId)

        // Add class
        this.addClass('product', 'box')

        // Generate template
        this.generate()
    }

    /**
     * Returns a specific product option.
     *
     * @param option
     */
    get(option: string): string|boolean|number|ProductOptions[]
    {
        return this.product[option]
    }

    /**
     * Register on select method for handle selection.
     * When registering a method, the product automatically becomes selectable.
     *
     * @param fn
     * @param checked
     */
    public onSelect(fn: Function, checked: boolean = false): void
    {
        this.selectFn = fn
        this.selectable = true
        this.isChecked = checked
        this.generate()
    }

    /**
     * Set a menu for the product.
     *
     * @param menu
     */
    public setMenu(menu: DropMenuComponent): void
    {
        this.menu = menu
        this.generate()
    }

    /**
     * Compares version numbers and return true if a newer version exists.
     */
    public hasNewVersion(): boolean
    {
        return this.product?.latestVersion?.localeCompare(this.product.version, undefined, { numeric: true, sensitivity: 'base' }) === 1
    }

    /**
     * Returns if the product is to be removed.
     */
    public isRemoved(): boolean
    {
        return this.product?.remove
    }

    /**
     * Generates the product state badge.
     *
     * @private
     */
    private generateState(): void
    {
        // Product is to be removed
        if(this.isRemoved())
        {
            const state = this.element('.state')

            this.addClass('removed')

            state.classList.add('removed')
            state.innerHTML = i18n('product.badge.removed')
        }
        // Product is registered
        else if(this.product?.registered)
        {
            const state = this.element('.state')

            state.classList.add('registered')
            state.innerHTML = i18n('product.badge.registered')
        }
    }

    /**
     * Compares versions, checks if there is a new version and generates the output if needed.
     *
     * @private
     */
    private createVersionCompare(): void
    {
        if(this.hasNewVersion())
        {
            const versionElement = this.element('.version')
            const newVersionElement = <HTMLDivElement> document.createElement('div')
                  newVersionElement.classList.add('version', 'new')
                  newVersionElement.innerHTML = this.product.latestVersion

            versionElement.after(newVersionElement)
        }
    }

    /**
     * Generates the output of the included products if it is a package.
     *
     * @private
     */
    private createPackageProducts(): void
    {
        if(this.product?.package)
        {
            const packageContainer = <HTMLDivElement> document.createElement('div')
            packageContainer.classList.add('packages')

            for (const packageProduct of this.product.package)
            {
                const product = new ProductComponent(packageProduct)

                packageContainer.append(product.template)
            }

            this.template.append(packageContainer)
        }
    }

    /**
     * Enable product selection.
     *
     * @private
     */
    private buildSelection(): void
    {
        const selectContainer = <HTMLDivElement> document.createElement('div')
              selectContainer.classList.add('selectable', 'widget', 'checkbox')
              selectContainer.innerHTML = `<label for="${this.id}_checkbox"></label>`

        const selectInput = <HTMLInputElement> document.createElement('input')
              selectInput.type = 'checkbox'
              selectInput.value = '1'
              selectInput.id = `${this.id}_checkbox`
              selectInput.name = selectInput.id
              selectInput.checked = this.isChecked

        selectInput.addEventListener('change', () => {
            this.selectFn.call(this, selectInput.checked, this.product)
        })

        selectContainer.prepend(selectInput)
        this.element('.inside').append(selectContainer)
    }

    /**
     * Generates the product template.
     *
     * @private
     */
    private generate(): void
    {
        const image = this.product.image ? `<img src="${this.product.image}" alt/>` : ''

        this.content(`
            <div class="inside">
                <div class="image">${image}</div>
                <div class="content">
                  <div class="title">${this.product.title}</div>
                  <div class="description">${this.product.description}</div>
                </div>
                <div class="info">
                    <div class="state badge"></div>
                    <div class="version">${this.product.version}</div>
                </div>
            </div>
        `)

        /**
         <div class="badges">
            <span class="type badge ${this.product.type}">${i18n('type.' + this.product.type)}</span>
         </div>
         */

        this.generateState()
        this.createVersionCompare()
        this.createPackageProducts()

        if(this.menu)
            this.menu.appendTo(this.element('.inside'))

        if(this.selectable)
            this.buildSelection()
    }
}
