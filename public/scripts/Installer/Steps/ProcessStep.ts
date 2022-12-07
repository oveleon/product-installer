import {i18n} from "../Language/"
import Step from "../Components/Step";
import ProcessManager from "../Process/ProcessManager";
import {createInstance} from "../Utils/InstanceUtils";
import State from "../State";

/**
 * Process step class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ProcessStep extends Step
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
            <div class="process"></div>
            <div class="actions">
                <button data-close disabled>${i18n('actions.close')}</button>
                <button class="add primary" disabled>${i18n('install.actions.add')}</button>
            </div>
        `
    }

    /**
     * @inheritDoc
     */
    protected events()
    {
        // Get the container in which the processes should be appended
        const container = <HTMLDivElement> this.template.querySelector('.process')

        const addButton = <HTMLButtonElement> this.template.querySelector('button.add')
        const closeButton = <HTMLButtonElement> this.template.querySelector('[data-close]')

        // Method for reset the step
        const resetProcess = () => {
            addButton.disabled = true
            closeButton.disabled = true

            this.manager.reset()
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
        this.manager.finish(() => {
            addButton.disabled = false
            closeButton.disabled = false

            // Clear the entire state
            State.clear()

            closeButton.addEventListener('click', () => {
                // Reset all
                resetProcess()

                this.modal.hide()
            })

            addButton.addEventListener('click', () => {
                // Reset all
                resetProcess()

                this.modal.open(0)
            })
        })

        // Start process manager
        this.manager.start()
    }
}
