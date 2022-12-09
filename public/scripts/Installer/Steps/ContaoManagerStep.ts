import State from "../State";
import {call} from "../../Utils/network"
import {i18n} from "../Language"
import Step from "../Components/Step";
import ProductManager from "../Product/ProductManager";
import {TaskConfig, TaskType} from "../Product/Product";

/**
 * Contao Manager step class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ContaoManagerStep extends Step
{
    private productManager: ProductManager
    private managerTasks: TaskConfig[]

    /**
     * @inheritDoc
     */
    getTemplate(): string
    {
        return `
            <div class="authentication inherit">
                <h2>${i18n('contao_manager.headline')}</h2>
                <p>${i18n('contao_manager.description')}</p>
                <div data-connection-state></div>
                <div class="actions">
                    <button id="cm-authenticate" class="primary">${i18n('contao_manager.authorize')}</button>
                </div>
            </div>
            <div class="tasks inherit" hidden>
                <h2>${i18n('contao_manager.headline')}</h2>
                <div class="console"></div>
                <div class="actions">
                    <button class="start primary">${i18n('actions.start')}</button>
                    <button class="primary" data-next hidden disabled>${i18n('actions.next')}</button>
                </div>
            </div>
        `
    }

    /**
     * @inheritDoc
     */
    protected events()
    {
        // Create product manager to handle tasks
        this.productManager = new ProductManager(State.get('config').products)
        this.managerTasks = this.productManager.getTasksOfType(
            TaskType.COMPOSER_UPDATE,
            TaskType.COMPOSER_INSTALL
        )

        // Check if contao manager steps could be skipped
        if(!this.managerTasks.length)
        {
            this.modal.next()
            return
        }

        // Show loader
        this.modal.loader(true, i18n('contao_manager.loading'))

        // Check if installer is authorized to communicate with contao manager
        call('/contao/api/contao_manager/session').then((response) => {
            // Hide loader
            this.modal.loader(false)

            if(response?.status === 'OK')
                this.sectionTask(response)
            else
                this.sectionAuth(response)

        }).catch((e: Error) => super.error(e))
    }

    /**
     * Handle events of the authentication section
     *
     * @private
     */
    private sectionAuth(response): void
    {
        const authenticateBtn = <HTMLButtonElement> this.template.querySelector('#cm-authenticate')

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
    }

    /**
     * Handle events of the task section
     *
     * @private
     */
    private sectionTask(response): void
    {
        const authContainer = <HTMLDivElement> this.template.querySelector('.authentication')
        const taskContainer = <HTMLDivElement> this.template.querySelector('.tasks')
        const nextBtn =  <HTMLButtonElement> this.template.querySelector('[data-next]')

        // Switch container
        authContainer.hidden = true
        taskContainer.hidden = false

        console.log(this.managerTasks)

        // ToDo: Enable after install is complete
        nextBtn.disabled = false
        nextBtn.hidden = false
    }
}
