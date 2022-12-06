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
            <div class="actions">
                <button id="cm-authenticate" class="primary">${i18n('contao_manager.authorize')}</button>
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
        call('/contao/contao_manager_authorization').then((response) => {
            // Hide loader
            this.modal.loader(false)

            if(response?.status === 'OK')
            {
                //this.modal.next()
            }

            // Add button events
            this.template.querySelector('#cm-authenticate').addEventListener('click', () => {
                document.location.href = '/contao-manager.phar.php/#oauth?scope=admin&client_id=product_installer&return_url=http://contao413.local/contao?installer=' + this.modal.currentIndex
            })

            console.log(response);
        }).catch(() => {
            // ToDo: Error
            console.log('error catch')
        })
    }
}
