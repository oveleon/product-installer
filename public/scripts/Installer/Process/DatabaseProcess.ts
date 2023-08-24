import Process from "./Process"
import {call, get} from "../../Utils/network"
import {TaskStatus} from "../ContaoManager";
import ConsoleComponent from "../Components/ConsoleComponent";

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
            <div class="actions">
                <button class="start" hidden>Datenbank aktualisieren</button>
                <button class="skip" hidden>Überspringen</button>
                <button class="details" hidden>Details</button>
            </div>
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

            // Enable start button
            const startBtn = this.element('.start')
            const skipBtn = this.element('.skip')

            startBtn.hidden = false
            skipBtn.hidden = false

            startBtn.addEventListener('click', () => this.startMigration())
            skipBtn.addEventListener('click', () => this.resolve(response))

        }).catch((e: Error) => this.reject(e))
    }

    startMigration(): void
    {
        // Disable button
        const startBtn = this.element('.start')
        startBtn.hidden = true

        const skipBtn = this.element('.skip')
        skipBtn.hidden = true

        call('/contao/api/contao_manager/database/set-migrate', this.getParameter()).then((response) => {

            debugger

            // Check errors
            if(response.error)
            {
                this.reject(response)
                return
            }

            // Set initial console operations
            this.console = new ConsoleComponent();
            this.console.hide()
            this.console.appendTo(this.template)
            this.console.set(response.operations)

            // Enable button
            const detailsBtn = this.element('.details')

            detailsBtn.hidden = false
            detailsBtn.addEventListener('click', () => this.console.toggle())

            // Update console
            this.updateConsole()
        }).catch((e: Error) => this.reject(e))
    }

    updateConsole(): void
    {
        this.resolve({})

        /**
         * ! Because of the maintenance-mode, we are not allowed to query the tasks via our own controller and have to access the API of the Contao manager directly.
         */
        // Check task status and update console
        /*get(this.updateRoute, {'Contao-Manager-Auth': this.token}).then((response) => {
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

        }).catch((e: Error) => this.reject(e))*/
    }
}
