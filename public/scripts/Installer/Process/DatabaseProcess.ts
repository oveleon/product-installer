import Process from "./Process"
import {call, get} from "../../Utils/network"
import {TaskStatus} from "../ContaoManager";
import {i18n} from "../Language"
import ConsoleComponent from "../Components/ConsoleComponent";
import DropMenuComponent from "../Components/DropMenuComponent";

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
                call('/contao/api/contao_manager/database/create-migrate', {}).then((response) => {

                    // Check errors
                    if(response.error)
                    {
                        this.reject(response)
                        return
                    }

                    this.showMigrationActions(response);
                }).catch((e: Error) => this.reject(e))
            }
            else
            {
                this.showMigrationActions(response);
            }

        }).catch((e: Error) => this.reject(e))
    }

    showMigrationActions(response): void
    {
        this.loader.pause()

        // Set initial console operations
        this.console = new ConsoleComponent();
        this.console.hide()
        this.console.appendTo(this.template)
        this.console.set(response.operations)
        this.console.setDescription(i18n('process.database.deletionHint'), 'warn')

        // Create menu
        const menu = new DropMenuComponent([
            {
                label: i18n('actions.console.toggle'),
                value: () => { this.console.toggle() },
                highlight: true
            },
            {
                label: i18n('actions.database.skip'),
                highlight: true,
                value: () => {
                    menu.disableOptions(i18n('actions.database.skip'))
                    menu.disableOptions(i18n('actions.database.migrate'))

                    this.console.hide()
                    this.resolve(response)
                },
            },
            {
                label: i18n('actions.database.migrate'),
                separator: true,
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
            this.console.update(response.operations)

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
