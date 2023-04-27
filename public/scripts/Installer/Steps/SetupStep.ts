import StepComponent from "../Components/StepComponent";
import {i18n} from "../Language"
import {call} from "../../Utils/network"
import State from "../State";

/**
 * Setup products.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class SetupStep extends StepComponent
{
    /**
     * Initialize with product hash.
     *
     * @param productHash
     */
    constructor(protected productHash: string)
    {
        super();
    }

    /**
     * @inheritDoc
     */
    protected getTemplate(): string
    {
        return `
            <h2>${i18n('setup.headline')}</h2>
            <div class="products"></div>
            <div class="actions">
                <button data-close>${i18n('actions.close')}</button>
            </div>
        `
    }

    protected mount()
    {
        // Clear setup
        State.clear('setup')
    }

    /**
     * @inheritDoc
     */
    protected events(): void
    {
        // Show loader
        this.modal.loader(true, i18n('setup.loading'))

        // Check license
        call('/contao/api/setup/init', {
            hash: this.productHash
        }).then((response) => {
            // Hide loader
            this.modal.loader(false)

            // Check errors
            if(response.error)
            {
                super.error(response)
                return
            }

            State.set('setup', response)

            console.log(response)

        }).catch((e: Error) => super.error(e))
    }
}
