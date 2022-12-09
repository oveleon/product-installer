import Process, {ProcessErrorResponse} from "./Process"
import {call} from "../../Utils/network"

/**
 * Default process class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ApiProcess extends Process
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
        // Check license
        call(this.getRoute('api')).then((response) => {
            // Check errors
            if(response.error)
            {
                this.reject(response)
                return
            }

            this.resolve()
        }).catch((e: Error) => this.reject(e))
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
