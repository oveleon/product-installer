import StepComponent from "../Components/StepComponent";
import ProductComponent from "../Components/ProductComponent";
import DropMenuComponent from "../Components/DropMenuComponent";
import {i18n} from "../Language"
import {call} from "../../Utils/network"
import State from "../State";

/**
 * An overview of registered products.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class DashboardStep extends StepComponent
{
    /**
     * @inheritDoc
     */
    protected getTemplate(): string
    {
        return `
            <h2>${i18n('dashboard.headline')}</h2>
            <div class="products"></div>
            <div class="actions">
                <button data-close>${i18n('actions.close')}</button>
                <button class="primary" data-next>${i18n('dashboard.actions.register')}</button>
            </div>
        `
    }

    /**
     * @inheritDoc
     */
    protected events(): void
    {
        // Skip dashboard when we have active redirects
        if(State.get('isRedirect'))
        {
            this.modal.next()
            return
        }

        // Show loader
        this.modal.loader(true, i18n('dashboard.loading'))

        // Check license
        call('/contao/api/license_connector/products').then((response) => {
            // Hide loader
            this.modal.loader(false)

            // Check errors
            if(response.error)
            {
                super.error(response)
                return
            }

            this.createProductList(response)

        }).catch((e: Error) => super.error(e))
    }

    /**
     * Create product list or empty message
     *
     * @param response
     * @protected
     */
    protected createProductList(response): void
    {
        const container = this.element('.products')
        let hasProducts = false

        for (const connector of response)
        {
            if(connector?.error)
            {
                super.error(connector)
                continue
            }

            if(!connector?.products.length)
            {
                continue
            }

            hasProducts = true

            // ToDo: Check also, that connectors have products. Since the upload connector is always installed we need another check here...
            // Create new connector information row if there are more than one
            if(response.length > 1)
            {
                const headingElement = <HTMLHeadingElement> document.createElement('h4')
                      headingElement.innerText = connector.connector.title

                container.append(headingElement)
            }

            // Collect products to sort them by removed flag
            const products = [];

            for(const productConfig of connector.products)
            {
                const product = new ProductComponent(productConfig)
                const menuOptions = []

                // Setup product
                if(!product.isRemoved())
                {
                    menuOptions.push({
                        label: i18n('product.setup'),
                        value: () => console.log('TEST 1'),
                        highlight: !product.get('setup')
                    })
                }

                // Product update option
                if(product.hasNewVersion() && !product.isRemoved())
                {
                    menuOptions.push({
                        label: i18n('product.update'),
                        value: () => console.log('TEST 1'),
                        highlight: true
                    })
                }

                // Product info
                menuOptions.push({
                    label: i18n('product.info'),
                    value: () => console.log('TEST 1'),
                    disabled: true
                })

                // Remove products from list
                if(product.isRemoved())
                {
                    menuOptions.push({
                        label: i18n('product.remove'),
                        value: () => console.log('TEST 1')
                    })
                }

                // Set product context menu
                product.setMenu(new DropMenuComponent(menuOptions))

                // Push product to sort by removed-flag
                products.push({
                    remove: product.get('remove'),
                    product
                })
            }

            // Sort products
            products.sort((a, b) => b.remove - a.remove);

            // Render products
            for (const product of products)
            {
                product.product.appendTo(container)
            }
        }

        if(!hasProducts)
        {
            container.innerHTML = `<div class="no-products">${i18n('dashboard.noProducts')}</div>`
            return
        }
    }
}
