import Process from "./Process"
import {call} from "../../Utils/network"
import {TaskStatus} from "../ContaoManager";
import {i18n} from "../Language"
import DropMenuComponent from "../Components/DropMenuComponent";
import {OperationConfig} from "../Components/ConsoleOperationComponent";
import PopupComponent, {PopupType} from "../Components/PopupComponent";

/**
 * Composer process class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class DatabaseProcess extends Process
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
        call('/contao/api/contao_manager/database/check', this.getParameter()).then((response) => {

            // Check errors
            if(response.error)
            {
                this.reject(response)
                return
            }

            switch (response.status?.type)
            {
                case 'empty':
                    this.resolve(response)
                    break

                case 'error':
                case 'problem':
                    this.reject(response)
                    break
            }

            // Get update route info
            this.token = response.token
            this.updateRoute = response.updateRoute

            if(parseInt(response.status?.total) > 0)
                this.checkMigration();
            else
                this.resolve(response)

        }).catch((e: Error) => this.reject(e))
    }

    /**
     * Check if a migration still exists or need to be created.
     */
    checkMigration(): void
    {
        call('/contao/api/contao_manager/database/migrate-status', {}).then((response) => {

            // Check errors
            if(response.error)
            {
                this.reject(response)
                return
            }

            // If no content passed, we need to create the migration
            if(response.originalStatus === 204)
            {
                this.createMigration()
            }
            else
            {
                this.showMigrationActions(response);
            }
        }).catch((e: Error) => this.reject(e))
    }

    createMigration(): void
    {
        call('/contao/api/contao_manager/database/create-migrate', {}).then((response) => {

            // Check errors
            if(response.error)
            {
                this.reject(response)
                return
            }

            this.checkMigration()
        }).catch((e: Error) => this.reject(e))
    }

    showMigrationActions(response): void
    {
        this.loader.pause()

        // Set initial console operations and create popup
        this.currentConsoleOperations = response.operations

        this.consolePopup = new PopupComponent({
            type: PopupType.CONSOLE,
            title: i18n('process.database.console.title'),
            description: i18n('process.database.deletionHint'),
            content: this.currentConsoleOperations,
            appendTo: this.template,
            closeable: true
        });

        // Create menu
        const menu = new DropMenuComponent([
            {
                label: i18n('actions.console.toggle'),
                value: () => {
                    this.consolePopup.show()
                    this.consolePopup.updateConsole(this.currentConsoleOperations)
                }
            },
            {
                label: i18n('actions.database.skip'),
                separator: true,
                value: () => {
                    menu.disableOptions(i18n('actions.database.skip'))
                    menu.disableOptions(i18n('actions.database.migrate'))

                    this.resolve(response)
                },
            },
            {
                label: i18n('actions.database.migrate'),
                highlight: true,
                value: () => {
                    menu.disableOptions(i18n('actions.database.skip'))
                    menu.disableOptions(i18n('actions.database.migrate'))

                    this.startMigration(response)
                },
            }
        ])

        menu.appendTo(this.element('.actions'))
    }

    startMigration(response): void
    {
        this.loader.play()

        call('/contao/api/contao_manager/database/start-migrate', {
            hash: response.hash,
            type: response.type
        }).then((response) => {

            // Check errors
            if(response.error)
            {
                this.reject(response)
                return
            }

            // Update console
            this.updateConsole()
        }).catch((e: Error) => this.reject(e))
    }

    updateConsole(): void
    {
        // Check task status and update console
        call('/contao/api/contao_manager/database/migrate-status', {}).then((response) => {

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
                    // Check task status and update console
                    call('/contao/api/contao_manager/database/delete-migrate', {})
                    .then((response) => {
                        this.resolve(response)
                    })

                    break
                default:
                    setTimeout(() => this.updateConsole(), 5000)
            }

        }).catch((e: Error) => this.reject(e))
    }
}
