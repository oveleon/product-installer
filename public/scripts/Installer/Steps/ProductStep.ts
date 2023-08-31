import StepComponent from "../Components/StepComponent";
import State from "../State";
import {i18n} from "../Language"
import ProductComponent, {ProductOptions} from "../Components/ProductComponent";
import NotificationComponent, {NotificationTypes} from "../Components/NotificationComponent";

/**
 * An overview of the products of the associated license keys.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ProductStep extends StepComponent
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

        //const nextBtn =  <HTMLButtonElement> this.element('[data-next]');
        //nextBtn.disabled = !props.installable

        // Add notification
        if(!props.installable)
        {
            (new NotificationComponent('System veraltet', i18n('product.no_version'), NotificationTypes.WARN, {
                closeable: true
            })).appendTo(this.modal.notificationContainer)
        }

        for (const productConfig of props.products)
        {
            const product = new ProductComponent(productConfig)

            // Show selection only if there are more than one product and the product is installable
            if(props.products.length > 1 && props.installable)
            {
                product.onSelect(this.selectProduct, true)
            }

            product.appendTo(container)
        }
    }

    protected selectProduct(checked: boolean, productConfig: ProductOptions)
    {
        let config = State.get('config')

        for(const index in config.products)
        {
            if (!config.products.hasOwnProperty(index)) {
                continue
            }

            const product = config.products[index]

            if(product.hash === productConfig.hash)
            {
                // Info: ProductManager is able to filter products to skip
                product.skip = !checked
                config.products[index] = product
                State.set('config', config)
                break
            }
        }
    }
}
