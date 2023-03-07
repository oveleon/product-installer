import Step from "../Components/Step";
import {i18n} from "../Language"
import {call} from "../../Utils/network"
import Product from "../Components/Product";
import State from "../State";

/**
 * An overview of installed products.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class DashboardStep extends Step
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

            // Create new connector information row
            const headingElement = <HTMLHeadingElement> document.createElement('h4')
                  headingElement.innerText = connector.connector.title

            container.append(headingElement)

            for(const productConfig of connector.products)
            {
                const product = new Product(productConfig);

                container.append(product.template)
            }
        }

        if(!hasProducts)
        {
            container.innerHTML = `<div class="no-products">${i18n('dashboard.noProducts')}</div>`
            return
        }
    }
}
