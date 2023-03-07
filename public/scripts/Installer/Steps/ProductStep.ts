import Step from "../Components/Step";
import State from "../State";
import {i18n} from "../Language"
import Product, {ProductOptions, ProductViewMode} from "../Components/Product";

/**
 * An overview of the products of the associated license keys.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ProductStep extends Step
{
    /**
     * @inheritDoc
     */
    protected getTemplate(): string
    {
        return `
            <h2>${i18n('product.headline')}</h2>
            <div class="products"></div>
            <div class="actions">
                <button data-prev>${i18n('actions.back')}</button>
                <button data-next class="primary">${i18n('actions.next')}</button>
            </div>
        `
    }

    /**
     * @inheritDoc
     */
    protected events()
    {
        const container = this.element('.products')
        const props = State.get('config')

        for (const productConfig of props.products)
        {
            const product = new Product(productConfig)

            // ToDo: Allow selection of products to install - Overwrite config to respond to this situation

            product.setMode(ProductViewMode.PREVIEW_SELECTABLE)
            product.appendTo(container)
        }
    }

    protected selectProduct(productConfig: ProductOptions)
    {

    }
}
