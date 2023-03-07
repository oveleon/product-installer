import Container from "./Container"
import {i18n} from "../Language"

/**
 * Product view modes
 */
export enum ProductViewMode {
    PREVIEW_DASHBOARD,
    PREVIEW_SELECTABLE
}

/**
 * Operation config.
 */
export interface ProductOptions {
    title: string,
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
 * Product Component class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class Product extends Container
{
    /**
     * Dynamic auto-increment id.
     */
    static productId: number = 0

    private viewMode: ProductViewMode
    private selectFn: Function

    /**
     * Creates a product component instance.
     */
    constructor(protected product: ProductOptions)
    {
        // Auto-increment id
        Product.productId++

        // Create container
        super('product' + Product.productId)

        // Add class
        this.addClass('product')

        // Set default mode
        this.setMode(ProductViewMode.PREVIEW_DASHBOARD)

        // Generate template
        this.generate()
    }

    /**
     * Set view mode of the product item.
     *
     * @param viewMode
     */
    public setMode(viewMode: ProductViewMode): void
    {
        this.viewMode = viewMode
    }

    /**
     * Register on select method for handle selection in Product view mode PREVIEW_SELECTABLE.
     *
     * @param fn
     */
    public onSelect(fn: Function): void
    {
        this.selectFn = fn
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
                const product = new Product(packageProduct)

                packageContainer.append(product.template)
            }

            this.template.append(packageContainer)
        }
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

        // ToDo: React to view modes, e.g. make products selectable and trigger selectFn on select
    }
}
