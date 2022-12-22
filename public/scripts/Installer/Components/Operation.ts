import Container from "./Container"
import {TaskStatus} from "../ContaoManager";

/**
 * Operation config.
 */
export interface OperationConfig {
    console: string,
    summary: string,
    status: string,
    details?: string
}

/**
 * Console class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class Operation extends Container
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
        Operation.operationId++

        // Create container
        super('operation' + Operation.operationId)

        // Create console container
        this.consoleContainer = <HTMLDivElement> document.createElement('div')
        this.consoleContainer.classList.add('operation')
        this.consoleContainer.hidden = true

        // Create summary container
        this.summaryContainer = <HTMLDivElement> document.createElement('div')
        this.summaryContainer.classList.add('summary')
        this.summaryContainer.innerHTML = operation.summary
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

        if(!operation.console.trim())
        {
            console.log('Skipped becouse its empty', operation.console.length, operation.console)
            return
        }

        // Update console
        this.consoleContainer.innerHTML = operation.console
            .split("\n")
            .map((line) => `<div class="line">${line}</div>`)
            .join("")
    }
}
