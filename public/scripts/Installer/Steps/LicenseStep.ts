import State from "../State";
import {call} from "../../Utils/network"
import {i18n} from "../Language"
import Step from "../Components/Step";

/**
 * License step class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class LicenseStep extends Step
{
    /**
     * @inheritDoc
     */
    getTemplate(): string
    {
        return `
            <h2>${this.getAttribute('title', i18n('license.headline'))}</h2>
            <p>${this.getAttribute('description', i18n('license.description'))}</p>
            <form id="license-form" class="inherit" autocomplete="off">
                <div class="widget text">
                    <label for="license">${i18n('license.form.label.license')}</label>
                    <input type="text" name="license" id="license" placeholder="XXXX-XXXX-XXXX-XXXX-XXXX" autocomplete="off" required/>
                </div>
            </form>
            <div class="actions">
                <button data-close>${i18n('actions.close')}</button>
                <button type="submit" form="license-form" class="check primary">${i18n('license.actions.next')}</button>
            </div>
        `
    }

    /**
     * @inheritDoc
     */
    protected submit(form: HTMLFormElement, data: FormData)
    {
        // Save license form data
        State.set('license', data.get('license'))

        // Show loader
        this.modal.loader(true, i18n('license.loading'))

        // Check license
        call('/contao/api/license_connector/license', {
            license: data.get('license')
        }).then((response) => {
            // Hide loader
            this.modal.loader(false)

            // Check errors
            if(response.error)
            {
                super.error(response)
                return
            }

            // Save information
            State.set('config', response)

            // Reset form
            form.reset()

            // Unlock form
            this.lockedForm = false

            // Show next step
            this.modal.next()
        }).catch((e: Error) => {
            // Trigger error
            super.error(e)

            // Unlock form
            this.lockedForm = false
        })
    }
}
