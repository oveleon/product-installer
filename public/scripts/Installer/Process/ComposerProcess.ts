import Process from "./Process"
import {call} from "../../Utils/network"
import {TaskStatus} from "../ContaoManager";
import NotificationComponent, {NotificationTypes} from "../Components/NotificationComponent";
import ConsoleComponent from "../Components/ConsoleComponent";

/**
 * Composer process class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ComposerProcess extends Process
{
    /**
     * Console.
     *
     * @protected
     */
    protected console: ConsoleComponent

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
                    const notification = new NotificationComponent('Nicht beendete Aufgaben werden beendet.', NotificationTypes.WARN, {
                        timer: {
                            ms: 5000
                        }
                    })

                    notification.appendTo(this.errorContainer)

                    call('/contao/api/contao_manager/task/delete', response.task).then(() => {
                        notification.remove()
                        this.process()
                    })
                }
                // Try again
                else
                {
                    (new NotificationComponent('Der Contao Manager führt derzeit eine andere Aufgabe durch.', NotificationTypes.WARN, {
                        timer: {
                            ms: 5000,
                            text: `Versuche erneut in #seconds# Sekunden.`,
                            autoClose: true,
                            onComplete: () => this.process()
                        }
                    })).appendTo(this.errorContainer)
                }

                return
            }

            // Set initial console operations
            this.console = new ConsoleComponent();
            this.console.hide()
            this.console.appendTo(this.template)
            this.console.set(response.operations)

            // Disable button
            const detailsBtn = this.element('.details')

            detailsBtn.hidden = false
            detailsBtn.addEventListener('click', () => this.console.toggle())

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

            // Update console
            this.console.update(response.operations)

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
