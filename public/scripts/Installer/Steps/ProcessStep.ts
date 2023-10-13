import {i18n} from "../Language/"
import StepComponent from "../Components/StepComponent";
import ProcessManager from "../Process/ProcessManager";
import {createInstance} from "../Utils/InstanceUtils";
import State from "../State";

/**
 * Process step class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ProcessStep extends StepComponent
{
    /**
     * Process manager instance.
     *
     * @private
     */
    private manager: ProcessManager

    /**
     * @inheritDoc
     */
    protected getTemplate(): string
    {
        return `
            <h2>${i18n('install.headline')}</h2>
            <div class="process inherit"></div>
            <div class="actions">
                <button class="back" data-prev>${i18n('actions.back')}</button>
                <button class="start primary">${i18n('actions.start')}</button>
                <button data-close disabled hidden>${i18n('actions.close')}</button>
                <button class="dashboard primary" disabled hidden>${i18n('actions.products')}</button>
            </div>
        `
    }

    /**
     * @inheritDoc
     */
    protected events()
    {
        // Get the container in which the processes should be appended
        const container = <HTMLDivElement> this.element('.process')

        const backButton = <HTMLButtonElement> this.element('button.back')
        const startButton = <HTMLButtonElement> this.element('button.start')
        const dashboardButton = <HTMLButtonElement> this.element('button.dashboard')
        const closeButton = <HTMLButtonElement> this.element('[data-close]')

        // Method for reset the step
        const resetProcess = () => {
            backButton.hidden = false
            backButton.disabled = false
            startButton.hidden = false
            startButton.disabled = false

            dashboardButton.disabled = true
            dashboardButton.hidden = false
            closeButton.disabled = true
            closeButton.hidden = false

            // Clear the entire state
            State.clear()

            this.manager.reset()
        }

        const finishProcess = () => {
            dashboardButton.disabled = false
            closeButton.disabled = false

            closeButton.addEventListener('click', () => {
                // Reset all
                resetProcess()

                this.modal.hide()
            })

            dashboardButton.addEventListener('click', () => {
                // Reset all
                resetProcess()

                this.modal.open(this.modal.getStepIndex('DashboardStep'))
            })
        }

        // Create process manager
        this.manager = new ProcessManager()

        for(const process of this.config.attributes.processes)
        {
            // Create instance
            const instance = createInstance(process.name, container, process)

            // Add processes
            this.manager.addProcess(instance)
        }

        // Register on finish method
        this.manager.onFinish(() => finishProcess())
        this.manager.onReject(() => finishProcess())

        // Start process manager
        startButton.addEventListener('click', () => {
            backButton.hidden = true
            backButton.disabled = true
            startButton.hidden = true
            startButton.disabled = true

            dashboardButton.disabled = true
            dashboardButton.hidden = false
            closeButton.disabled = true
            closeButton.hidden = false

            this.manager.start()
        })
    }
}
