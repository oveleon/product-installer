import State from "../State";
import {call} from "../../Utils/network"
import {i18n} from "../Language"
import Step from "../Components/Step";
import ProductManager from "../Product/ProductManager";
import {TaskType} from "../Product/Product";
import {ComposerConfig} from "../ContaoManager";

/**
 * Contao Manager step class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ContaoManagerStep extends Step
{
    private productManager: ProductManager
    private managerTasks: ComposerConfig[]

    private isAuthenticated: boolean = false
    private isComposerReady: boolean = false

    private authContainer: HTMLDivElement
    private installContainer: HTMLDivElement
    private manuallyContainer: HTMLDivElement

    private manualCheckbox: HTMLInputElement
    private authenticateBtn: HTMLButtonElement
    private manuallyBtn: HTMLButtonElement
    private closeBtn: HTMLButtonElement
    private nextBtn: HTMLButtonElement

    /**
     * @inheritDoc
     */
    getTemplate(): string
    {
        return `
            <h2>${i18n('contao_manager.headline')}</h2>
            <div class="authentication inherit">
                <p>${i18n('contao_manager.description')}</p>
                <div data-connection-state>${i18n('contao_manager.connection.inactive')}</div>
            </div>
            <div class="install inherit" hidden>
                <p>${i18n('contao_manager.description.success')}</p>
                <div data-connection-state="active" class="connection">${i18n('contao_manager.connection.active')}</div>
            </div>
            <div class="manually inherit" hidden>
                <p>${i18n('contao_manager.install.description')}</p>
                <h4>${i18n('contao_manager.dependencies.headline')}</h4>
                <div class="tasks inherit"></div>
                <form>
                    <div class="widget checkbox center">
                        <input type="checkbox" name="manual" id="manual" value="1" />
                        <label for="manual">${i18n('contao_manager.dependencies.installed')}</label>
                    </div>
                </form>
            </div>
            <div class="actions">
                <button class="cm-manually">${i18n('contao_manager.install.button')}</button>
                <button class="cm-manually-close" hidden>${i18n('actions.back')}</button>
                <button id="cm-authenticate" class="primary">${i18n('contao_manager.authorize')}</button>
                <button class="primary" data-next hidden>${i18n('actions.next')}</button>
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

        this.authContainer = <HTMLDivElement> this.template.querySelector('.authentication')
        this.installContainer = <HTMLDivElement> this.template.querySelector('.install')
        this.manuallyContainer = <HTMLDivElement> this.template.querySelector('.manually')
        this.authenticateBtn = <HTMLButtonElement> this.template.querySelector('#cm-authenticate')
        this.manuallyBtn = <HTMLButtonElement> this.template.querySelector('.cm-manually')
        this.closeBtn = <HTMLButtonElement> this.template.querySelector('.cm-manually-close')
        this.nextBtn = <HTMLButtonElement> this.template.querySelector('[data-next]')
        this.manualCheckbox = <HTMLInputElement> this.template.querySelector('input#manual')

        // Check if installer is authorized to communicate with contao manager
        call('/contao/api/contao_manager/session').then((response) => {
            // Hide loader
            this.modal.loader(false)

            // Bind manually install button and checkbox events
            this.manuallyBtn.addEventListener('click', () => this.sectionManuallyInstall(true))
            this.closeBtn.addEventListener('click', () => this.sectionManuallyInstall(false))
            this.manualCheckbox.addEventListener('change', () => {
                this.nextBtn.disabled = !this.manualCheckbox.checked
            })

            if(response?.status === 'OK')
            {
                this.isAuthenticated = true
                this.sectionInstall(response)
            }
            else
            {
                this.isAuthenticated = false
                this.sectionAuth(response)
            }
        }).catch((e: Error) => super.error(e))
    }

    /**
     * Handle events of the authentication section
     *
     * @private
     */
    private sectionAuth(response): void
    {
        // Add button events
        this.authenticateBtn.addEventListener('click', () => {
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
    private sectionInstall(response): void
    {
        this.authContainer.hidden = true
        this.installContainer.hidden = false
        this.authenticateBtn.hidden = true
        this.nextBtn.hidden = false
    }

    /**
     * Create composer requirement elements.
     *
     * @private
     */
    private createRequirements(): void
    {
        const tasksContainer = this.template.querySelector('div.tasks')

        // Clear container
        tasksContainer.innerHTML = ''

        for (const task of this.managerTasks)
        {
            for (const requirement of task.require)
            {
                const element = document.createElement('div')

                element.classList.add('task')
                element.innerHTML = `composer require ${requirement}`

                tasksContainer.append(element)
            }
        }
    }

    /**
     * Enable/disable manually installation section.
     *
     * @param state
     *
     * @private
     */
    private sectionManuallyInstall(state: boolean): void
    {
        this.manuallyBtn.hidden = state
        this.manuallyContainer.hidden = !state
        this.closeBtn.hidden = !state

        if(state === true)
        {
            // Create task elements
            this.createRequirements()

            if(!this.isComposerReady)
            {
                // Show loader
                this.modal.loader(true, i18n('contao_manager.loading.composer'))

                // Write repositories to composer
                call('/contao/api/composer/repositories/set', this.managerTasks).then((response) => {
                    // Hide loader
                    this.modal.loader(false)

                    this.isComposerReady = true

                }).catch((e: Error) => super.error(e))
            }

            this.authContainer.hidden = true
            this.installContainer.hidden = true
            this.authenticateBtn.hidden = true
            this.nextBtn.hidden = false
            this.nextBtn.disabled = true
            this.manualCheckbox.checked = false
        }
        else if(this.isAuthenticated)
        {
            this.installContainer.hidden = false
            this.nextBtn.hidden = false
            this.nextBtn.disabled = false
        }
        else
        {
            this.authenticateBtn.hidden = false
            this.authContainer.hidden = false
            this.nextBtn.hidden = true
        }
    }
}
