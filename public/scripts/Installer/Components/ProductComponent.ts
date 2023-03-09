import ContainerComponent from "./ContainerComponent"
import {i18n} from "../Language"

/**
 * Operation config.
 */
export interface ProductOptions {
    title: string,
    hash: string,
    type: string,
    description: string
    installed?: boolean
    remove?: boolean
    updated?: number
    version?: string
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
        this.addClass('product')

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
     * Generates the product state badge.
     *
     * @private
     */
    private generateState(): void
    {
        // Product is to be removed
        if(this.product?.remove)
        {
            const state = this.element('.state')

            this.addClass('removed')

            state.classList.add('removed')
            state.innerHTML = i18n('product.removed')
        }
        // Product is installed
        else if(this.product?.installed)
        {
            const state = this.element('.state')

            state.classList.add('installed')
            state.innerHTML = i18n('product.installed')
        }
    }

    /**
     * Compares versions, checks if there is a new version and generates the output if needed.
     *
     * @private
     */
    private checkNewVersion(): void
    {
        if(this.product?.latestVersion?.localeCompare(this.product.version, undefined, { numeric: true, sensitivity: 'base' }))
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
    private enableSelection(): void
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
                  <div class="badges">
                    <span class="type badge ${this.product.type}">${i18n('type.' + this.product.type)}</span>
                  </div>
                </div>
                <div class="info">
                    <div class="state badge"></div>
                    <div class="version">${this.product.version}</div>
                </div>
            </div>
        `)

        this.generateState()
        this.checkNewVersion()
        this.createPackageProducts()

        // ToDo: Add DropListComponent (Menu) before selection!

        if(this.selectable)
            this.enableSelection()
    }
}
