import Process, {ProcessErrorResponse} from "./Process"
import {call} from "../../Utils/network"

/**
 * Composer update process class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ComposerUpdateProcess extends Process
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
        `;
    }

    /**
     * @inheritDoc
     */
    protected process(): void
    {
        // ToDo: Create composer update process
        this.resolve()

        // Check license
        /*call(this.getRoute('process')).then((response) => {

            this.resolve()
        }).catch((e: Error) => this.reject(e))*/
    }

    /**
     * @inheritDoc
     */
    protected reject(data: Error | ProcessErrorResponse): void
    {
        super.reject(data);

        // Exit manager and following processes
        this.manager.exit()
    }
}
