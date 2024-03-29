import State from "../State";
import {call} from "../../Utils/network"
import {i18n} from "../Language"
import StepComponent from "../Components/StepComponent";
import ContaoManager, {ComposerConfig} from "../ContaoManager";

/**
 * Contao Manager step class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ContaoManagerStep extends StepComponent
{
    private managerTasks: ComposerConfig[]

    private isAuthenticated: boolean = false
    private isComposerReady: boolean = false

    private connectActiveContainer: HTMLDivElement
    private connectInactiveContainer: HTMLDivElement
    private authContainer: HTMLDivElement
    private notInstalledContainer: HTMLDivElement
    private installContainer: HTMLDivElement
    private manuallyContainer: HTMLDivElement

    private manualCheckbox: HTMLInputElement
    private authenticateBtn: HTMLButtonElement
    private manuallyBtn: HTMLButtonElement
    private closeBtn: HTMLButtonElement
    private closeModalBtn: HTMLButtonElement
    private nextBtn: HTMLButtonElement

    /**
     * @inheritDoc
     */
    getTemplate(): string
    {
        return `
            <h2>${i18n('contao_manager.headline')}</h2>
            <div class="authentication inherit" hidden>
                <p>${i18n('contao_manager.description')}</p>
                <div class="graphic con-inactive" data-connection-state><span>${i18n('contao_manager.connection.inactive')}</span></div>
            </div>
            <div class="not-installed inherit" hidden>
                <p>${i18n('contao_manager.description.notInstalled')}</p>
                <div class="graphic not-installed"><span>${i18n('contao_manager.connection.notInstalled')}</span></div>
            </div>
            <div class="install inherit" hidden>
                <p>${i18n('contao_manager.description.success')}</p>
                <div class="graphic con-active" data-connection-state><span>${i18n('contao_manager.connection.active')}</span></div>
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
                <button data-prev>${i18n('actions.back')}</button>
                <button id="cm-authenticate" class="primary">${i18n('contao_manager.authorize')}</button>
                <button class="primary" data-next hidden>${i18n('actions.next')}</button>
                <button data-close hidden>${i18n('actions.close')}</button>
            </div>
        `

        // <button class="cm-manually">${i18n('contao_manager.install.button')}</button>
        // <button class="cm-manually-close" hidden>${i18n('actions.back')}</button>
    }

    /**
     * @inheritDoc
     */
    protected events()
    {
        // Get products from state
        const products = State.get('config').products

        // Get task to handle
        const contaoManager = new ContaoManager()
        this.managerTasks = contaoManager.getComposerTasksByProducts(products)

        // Check if there are tasks that need to be done by the Contao Manager
        const useManager = contaoManager.hasTasks(products)

        // Save information for further steps and processes
        State.set('useManager', useManager)
        State.set('installManually', false)

        // Reset skip attribute
        this.skip = false

        // Skip the step if there are no tasks for the Contao Manager
        if(!useManager)
        {
            this.skip = true
            this.modal.next()
            return
        }

        // Show loader
        this.modal.loader(true, i18n('contao_manager.loading'))

        this.connectActiveContainer = <HTMLDivElement> this.element('.con-active')
        this.connectInactiveContainer = <HTMLDivElement> this.element('.con-inactive')

        this.authContainer      = <HTMLDivElement> this.element('.authentication')
        this.installContainer   = <HTMLDivElement> this.element('.install')
        this.notInstalledContainer = <HTMLDivElement> this.element('.not-installed')
        this.manuallyContainer  = <HTMLDivElement> this.element('.manually')

        this.authenticateBtn    = <HTMLButtonElement> this.element('#cm-authenticate')
        //this.manuallyBtn        = <HTMLButtonElement> this.element('.cm-manually')
        //this.closeBtn           = <HTMLButtonElement> this.element('.cm-manually-close')
        this.nextBtn            = <HTMLButtonElement> this.element('[data-next]')
        this.closeModalBtn      = <HTMLButtonElement> this.element('[data-close]')

        this.manualCheckbox     = <HTMLInputElement> this.element('input#manual')

        // Check if installer is authorized to communicate with contao manager
        call('/contao/api/contao_manager/session').then((response) => {
            // Set button events
            /*this.manuallyBtn.addEventListener('click', () => this.sectionManuallyInstall(true))
            this.closeBtn.addEventListener('click', () => this.sectionManuallyInstall(false))
            this.manualCheckbox.addEventListener('change', () => {
                // Disable/enable next button
                this.nextBtn.disabled = !this.manualCheckbox.checked

                // Save state to skip processes
                State.set('installManually', this.manualCheckbox.checked)
            })*/

            // Check the status to display the corresponding mask
            if(response?.status === 'OK')
            {
                // Define that the authorization is done
                this.isAuthenticated = true

                // Set the visibility of the action button
                this.authContainer.hidden = true
                this.installContainer.hidden = false
                this.authenticateBtn.hidden = true
                this.nextBtn.hidden = false

                // Set connection
                this.connectActiveContainer.dataset.connectionState = 'active'
            }
            else
            {
                this.authContainer.hidden = false

                // Check if contao manager exits
                if(response?.error && response?.exists === false)
                {
                    this.closeModalBtn.hidden = false
                    this.authenticateBtn.hidden = true
                    //this.manuallyBtn.hidden = true

                    // Set the visibility of the action button
                    this.authContainer.hidden = true
                    this.notInstalledContainer.hidden = false
                }

                // Define that the authorization is not yet complete.
                this.isAuthenticated = false

                // Set the event for authorization
                this.authenticateBtn.addEventListener('click', () => {
                    const returnUrlParams = new URLSearchParams({
                        installer:  State.get('connector'),
                        start:      this.modal.currentIndex.toString()
                    })

                    const parameter = new URLSearchParams({
                        scope:         'admin',
                        client_id:     'product_installer',
                        response_type: 'token',
                        redirect_uri:  response.manager.return_url + '?' + returnUrlParams.toString()
                    })

                    document.location.href = response.manager.path + '/#oauth?' + parameter.toString()
                })

                // Set connection
                this.connectInactiveContainer.dataset.connectionState = 'inactive'
            }

            // Hide loader
            this.modal.loader(false)

        }).catch((e: Error) => super.error(e))
    }

    /**
     * Create composer requirement elements.
     *
     * @private
     */
    private createRequirements(): void
    {
        const tasksContainer = this.element('div.tasks')

        // Clear container
        tasksContainer.innerHTML = ''

        for (const task of this.managerTasks)
        {
            for (let packageName in task.require)
            {
                const element = document.createElement('div')

                element.classList.add('task')
                element.innerHTML = `composer require ${packageName} "${task.require[packageName]}"`

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

        State.set('installManually', false)

        if(state === true)
        {
            // Create task elements
            this.createRequirements()

            if(!this.isComposerReady)
            {
                // Show loader
                this.modal.loader(true, i18n('contao_manager.loading.composer'))

                // Add necessary properties to composer.json to be able to install dependencies manually
                call('/contao/api/composer/repositories/set', this.managerTasks).then(() => {
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
            this.closeModalBtn.hidden = true
        }
        else
        {
            this.authenticateBtn.hidden = false
            this.authContainer.hidden = false
            this.nextBtn.hidden = true
            this.closeModalBtn.hidden = true
        }
    }
}
