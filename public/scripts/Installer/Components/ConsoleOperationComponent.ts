import ContainerComponent from "./ContainerComponent"
import {TaskStatus} from "../ContaoManager";

/**
 * Operation config.
 */
export interface OperationConfig {
    status: string,
    summary?: string,
    name?: string
    console?: string,
    details?: string
}

/**
 * Console class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ConsoleOperationComponent extends ContainerComponent
{
    /**
     * Dynamic auto-increment id.
     */
    static operationId: number = 0

    /**
     * The container for the operation summary.
     *
     * @private
     */
    private readonly summaryContainer: HTMLDivElement

    /**
     * The container for the operation.
     *
     * @private
     */
    private readonly consoleContainer: HTMLDivElement

    /**
     * Creates a console instance.
     */
    constructor(operation: OperationConfig)
    {
        // Auto-increment id
        ConsoleOperationComponent.operationId++

        // Create container
        super('operation' + ConsoleOperationComponent.operationId)

        // Create console container
        this.consoleContainer = <HTMLDivElement> document.createElement('div')
        this.consoleContainer.classList.add('operation')
        this.consoleContainer.hidden = true

        // Create summary container
        this.summaryContainer = <HTMLDivElement> document.createElement('div')
        this.summaryContainer.classList.add('summary')
        this.summaryContainer.innerHTML = operation?.summary ?? operation.name
        this.summaryContainer.addEventListener('click', () => {
            switch(this.template.dataset.status)
            {
                case TaskStatus.STOPPED:
                case TaskStatus.PENDING:
                    return
            }

            this.template.classList.toggle('open')
            this.consoleContainer.hidden = !this.consoleContainer.hidden
        })

        // Add container
        this.template.append(this.summaryContainer)
        this.template.append(this.consoleContainer)

        // Update
        this.update(operation);
    }

    public update(operation: OperationConfig): void
    {
        // Update status
        this.template.dataset.status = operation.status

        // Single line operation
        if(operation?.name)
        {
            // Update console
            this.consoleContainer.innerHTML = operation.name.trim()
            return
        }

        if(!operation.console?.trim())
        {
            return
        }

        this.summaryContainer.classList.add('has-operation')

        // Update console
        this.consoleContainer.innerHTML =
            operation.console
                .split("\n")
                .map((line) => `<div class="line">${line}</div>`)
                .join("")
    }
}
