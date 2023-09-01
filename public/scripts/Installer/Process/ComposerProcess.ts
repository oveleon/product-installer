import Process from "./Process"
import {call, get} from "../../Utils/network"
import {TaskStatus} from "../ContaoManager";
import NotificationComponent, {NotificationTypes} from "../Components/NotificationComponent";
import DropMenuComponent from "../Components/DropMenuComponent";
import {i18n} from "../Language"
import PopupComponent, {PopupType} from "../Components/PopupComponent";
import {OperationConfig} from "../Components/ConsoleOperationComponent";
import Installer from "../Installer";

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
    protected consolePopup: PopupComponent

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
     * The current console response.
     *
     * @protected
     */
    protected currentConsoleOperations: OperationConfig[]

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
            <div class="actions"></div>
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
                    const notification = new NotificationComponent(
                        i18n('process.contao_manager.title'),
                        i18n('process.composer.running.stop.title'),
                        NotificationTypes.WARN,
                        {
                            timer: {
                                ms: 5000
                            }
                        }
                    )

                    notification.appendTo(Installer.modal.notificationContainer)

                    call('/contao/api/contao_manager/task/delete', response.task).then(() => {
                        notification.remove()
                        this.process()
                    })
                }
                // Try again
                else
                {
                    (new NotificationComponent(
                        i18n('process.contao_manager.title'),
                        i18n('process.composer.running.try.title'),
                        NotificationTypes.WARN,
                        {
                            timer: {
                                ms: 5000,
                                text: i18n('process.composer.running.try.timer'),
                                autoClose: true,
                                onComplete: () => this.process()
                            }
                        }
                    )).appendTo(Installer.modal.notificationContainer)
                }

                return
            }

            // Set initial console operations and create popup
            this.currentConsoleOperations = response.operations

            this.consolePopup = new PopupComponent({
                type: PopupType.CONSOLE,
                title: i18n('process.composer.console.title'),
                content: this.currentConsoleOperations,
                appendTo: this.template,
                closeable: true
            });

            // Get update route info
            this.token = response.token
            this.updateRoute = response.updateRoute

            // Create menu
            const menu = new DropMenuComponent([
                {
                    label: i18n('actions.console.toggle'),
                    value: () => {
                        this.consolePopup.show()
                        this.consolePopup.updateConsole(this.currentConsoleOperations)
                    },
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
            this.currentConsoleOperations = response.operations
            this.consolePopup.updateConsole(this.currentConsoleOperations)

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
