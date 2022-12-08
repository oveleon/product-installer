import State from "../State";
import {call} from "../../Utils/network"
import {i18n} from "../Language"
import Step from "../Components/Step";

/**
 * Contao Manager authentication step class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ContaoManagerStep extends Step
{
    /**
     * @inheritDoc
     */
    getTemplate(): string
    {
        return `
            <h2>${i18n('contao_manager.headline')}</h2>
            <p>${i18n('contao_manager.description')}</p>
            <div data-connection-state></div>
            <div class="actions">
                <button id="cm-authenticate" class="primary">${i18n('contao_manager.authorize')}</button>
                <button class="primary" data-next disabled>${i18n('actions.next')}</button>
            </div>
        `
    }

    /**
     * @inheritDoc
     */
    protected events()
    {
        // Show loader
        this.modal.loader(true, i18n('contao_manager.loading'))

        // Check if installer is authorized
        call('/contao/contao_manager/session').then((response) => {
            // Hide loader
            this.modal.loader(false)

            const connection = <HTMLDivElement> this.template.querySelector('[data-connection-state]')
            const authenticateBtn = <HTMLButtonElement> this.template.querySelector('#cm-authenticate')
            const nextBtn =  <HTMLButtonElement> this.template.querySelector('[data-next]')

            if(response?.status === 'OK')
            {
                authenticateBtn.disabled = true
                nextBtn.disabled = false
                connection.dataset.connectionState = 'active'

                return
            }

            // Add button events
            authenticateBtn.addEventListener('click', () => {
                const returnUrl = new URLSearchParams({
                    installer:  State.get('connector'),
                    start:      this.modal.currentIndex.toString()
                })

                const parameter = new URLSearchParams({
                    scope:      'admin',
                    client_id:  'product_installer',
                    return_url:  response.manager.return_url + '?' + returnUrl.toString()
                })

                document.location.href = response.manager.path + '/#oauth?' + parameter.toString()
            })

            console.log(response);
        }).catch(() => {
            // ToDo: Error
            console.log('error catch')
        })
    }
}
