import Process from "./Process"
import {call} from "../../Utils/network"
import {TaskStatus} from "../ContaoManager";

/**
 * Composer process class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ComposerProcess extends Process
{
    /**
     * @inheritDoc
     */
    protected getTemplate(): string {
        return `
            <div data-loader></div>
            <div class="content">
                <div class="title">${this.config.attributes.title}</div>
                <p>${this.config.attributes.description}</p>
            </div>
            <div class="actions">
                <button class="details" hidden>Details</button>
            </div>
        `;
    }

    /**
     * @inheritDoc
     */
    protected process(): void
    {
        call(this.getRoute('start'), this.getParameter()).then((response) => {
            // Check errors
            if(response.error)
            {
                this.reject(response)
                return
            }

            // Check if tasks are set
            if(response.status === TaskStatus.NOT_AVAILABLE)
            {
                this.resolve(response)
                return
            }

            // Check if a task already running
            if(response.status === TaskStatus.ALREADY_RUNNING)
            {
                // Delete task if status is error
                if(response.task.status === 'error')
                {
                    // ToDo: Show notification

                    call('/contao/api/contao_manager/task/delete', response.task).then((deleteResponse) => {
                        this.process()
                    })
                }
                // Try again
                else
                {
                    // ToDo: Show notification and stop after 5 trys and show manager button or similar

                    setTimeout(() => {
                        this.process()
                    }, 5000)
                }

                return
            }

            // Disable button
            this.element('.details').hidden = false

            // Update console
            this.updateConsole()

        }).catch((e: Error) => this.reject(e))
    }

    updateConsole(): void
    {
        // Check task status and update console
        call(this.getRoute('update'), this.getParameter()).then((response) => {
            // Check errors
            if(response.error)
            {
                this.reject(response)
                return
            }

            switch (response.status)
            {
                case TaskStatus.ABORTING:
                case TaskStatus.ERROR:
                case TaskStatus.STOPPED:
                    this.reject(response)
                    break
                case TaskStatus.COMPLETE:
                    this.resolve(response)
                    break
                default:
                    setTimeout(() => this.updateConsole(), 5000)
            }

        }).catch((e: Error) => this.reject(e))
    }
}
