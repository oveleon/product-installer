import StepComponent from "../Components/StepComponent";
import {i18n} from "../Language"
import {call} from "../../Utils/network"
import State from "../State";
import {TaskConfig, TaskType} from "../Product/Product";
import ProductComponent from "../Components/ProductComponent";
import ImportContentPackageStep from "./ImportContentPackageStep";

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
            <div class="product-overview"></div>
            <h4 class="setup-headline">${i18n('setup.available_imports.headline')} (<span></span>)</h4>
            <div class="tasks-overview">
                
            </div>
            <div class="actions">
                <button class="prev">${i18n('actions.back')}</button>
            </div>
        `
    }

    /**
     * @inheritDoc
     */
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

        // Add back button event
        this.element('button.prev').addEventListener('click', () => this.gotToDashboard())

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

                // Go back to the dachboard
                this.gotToDashboard()

                return
            }

            State.set('setup', response)

            // Show product
            new ProductComponent(response.product).appendTo(this.element('.product-overview'))

            // Create tasks
            this.createTasks(response.tasks)

        }).catch((e: Error) => super.error(e))
    }

    protected createTasks(tasks: TaskConfig[]): void
    {
        const taskContainer: HTMLDivElement = <HTMLDivElement> this.element('.tasks-overview')
        const setupHeadline: HTMLHeadingElement = <HTMLHeadingElement> this.element('.setup-headline')

        // Set task number
        setupHeadline.querySelector('span').innerHTML = tasks.length.toString()

        for(const task of tasks)
        {
            // Create task
            const taskElement: HTMLDivElement = document.createElement('div')


            const runButton: HTMLButtonElement = document.createElement('button')
            runButton.innerText = 'Einrichten'

            // Set run action
            runButton.addEventListener('click', () => {

                // Get current setup state
                const setup = State.get('setup')

                setup['task'] = task.hash

                // Set new task hash (used to start an import process and further steps)
                State.set('setup', setup)

                // Create new step by task.type
                let importStep: StepComponent;

                switch (task.type)
                {
                    case TaskType.CONTENT_PACKAGE:
                        importStep = new ImportContentPackageStep()
                        break

                    case TaskType.REPOSITORY_IMPORT:
                    case TaskType.MANAGER_PACKAGE:
                        // ToDo: Create new setup-steps for other types...
                        return
                }

                this.modal.addSteps(importStep)
                this.modal.next()
            })

            // Fixme: make me pretty 🤡
            taskElement.innerHTML = task.type
            taskElement.appendChild(runButton)

            taskContainer.appendChild(taskElement)
        }
    }

    private gotToDashboard(): void
    {
        this.modal.open(this.modal.getStepIndex('DashboardStep'))
    }
}