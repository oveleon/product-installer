import Step from "../Components/Step";
import {i18n} from "../Language"

/**
 * An overview of installed products and more.
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
            <div class="products">
                
            </div>
            <div class="actions">
                <button class="primary" data-next>${i18n('actions.next')}</button>
            </div>
        `
    }

    /**
     * @inheritDoc
     */
    protected events(): void
    {

    }
}
