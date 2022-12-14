import Step from "../Components/Step";
import State from "../State";
import {i18n} from "../Language"

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
        const props = State.get('config')
        let products = ''

        for (const product of props.products)
        {
            const image = product.image ? `<img src="${product.image}" alt/>` : ''

            products += `
                 <div class="product">
                    <div class="image">
                        ${image}
                    </div>
                    <div class="content">
                        <div class="title">${product.name}</div>
                        <div class="description">${product.description}</div>
                        <div class="version">${product.version}</div>
                    </div>
                </div>
            `
        }

        return `
            <h2>${i18n('product.headline')}</h2>
            <div class="products">
                ${products}
            </div>
            <div class="actions">
                <button data-prev>${i18n('actions.back')}</button>
                <button data-next class="primary">${i18n('actions.next')}</button>
            </div>
        `
    }
}