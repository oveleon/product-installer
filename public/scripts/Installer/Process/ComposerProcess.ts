import Process from "./Process"
import {call, get} from "../../Utils/network"
import {TaskStatus} from "../ContaoManager";
import NotificationComponent, {NotificationTypes} from "../Components/NotificationComponent";
import ConsoleComponent from "../Components/ConsoleComponent";
import DropMenuComponent from "../Components/DropMenuComponent";
import {i18n} from "../Language"

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
     * CM Token.
     *
     * @protected
     */
    protected token: string

    /**
     * Update route.
     *
     * @protected
     */
    protected updateRoute: string

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
                <!--<button class="details" hidden>Details</button>-->
            </div>
        `;
    }

    /**
     * @inheritDoc
     */
    protected process(): void
    {
        call('/contao/api/contao_manager/task/set', this.getParameter()).then((response) => {
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

            // Get update route info
            this.token = response.token
            this.updateRoute = response.updateRoute

            // Create menu
            const menu = new DropMenuComponent([
                {
                    label: i18n('actions.console.toggle'),
                    value: () => { this.console.toggle() },
                    highlight: true
                }
            ])

            menu.appendTo(this.element('.actions'))

            // Update console
            this.updateConsole()

        }).catch((e: Error) => this.reject(e))
    }

    updateConsole(): void
    {
        /**
         * ! Because of the maintenance-mode, we are not allowed to query the tasks via our own controller but have to access the API of the Contao manager directly.
         */
        // Check task status and update console
        get(this.updateRoute, {'Contao-Manager-Auth': this.token}).then((response) => {
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

                    call('/contao/api/contao_manager/task/delete', {}).then(() => {
                        this.resolve(response)
                    })

                    break
                default:
                    setTimeout(() => this.updateConsole(), 5000)
            }

        }).catch((e: Error) => this.reject(e))
    }
}
