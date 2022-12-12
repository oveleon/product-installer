import State from "../State";
import {call} from "../../Utils/network"
import {i18n} from "../Language"
import Step from "../Components/Step";
import ProductManager from "../Product/ProductManager";
import {TaskConfig, TaskType} from "../Product/Product";
import ContaoManager from "../ContaoManager";

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
                <div data-connection-state="active"></div>
                <form id="install-form">
                    <div class="widget checkbox center">
                        <input type="checkbox" name="install_manually" id="install_manually" required/>
                        <label for="install_manually">${i18n('contao_manager.install.label')}</label>
                    </div>
                </form>
                <div class="install" hidden>
                    <p>${i18n('contao_manager.install.description')}</p>
                    <h4>${i18n('contao_manager.dependencies.headline')}</h4>
                    <div class="tasks"></div>
                </div>
                <div class="actions">
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
        this.managerTasks = this.productManager.getTasksByType(TaskType.COMPOSER_UPDATE)

        const useManager = this.managerTasks.length > 0

        // Save information for further steps and processes
        State.set('useManager', useManager)

        // Check if contao manager steps could be skipped
        if(!useManager)
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

        // Enable next button
        nextBtn.disabled = false
        nextBtn.hidden = false

        this.createTasks()
    }

    /**
     * Create console tasks
     *
     * @private
     */
    private createTasks(): void
    {
        const cm = new ContaoManager()
        const privateKeys = cm.getPrivateKeyByTasks(this.managerTasks)
        const updateTasks = cm.summarizeComposerTasks(this.managerTasks)

        console.log(updateTasks)
    }
}
