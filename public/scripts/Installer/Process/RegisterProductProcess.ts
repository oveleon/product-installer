import Process from "./Process"
import {call} from "../../Utils/network"
import {i18n} from "../Language"
import State from "../State";

/**
 * Register products process class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class RegisterProductProcess extends Process
{
    /**
     * @inheritDoc
     */
    protected getTemplate(): string {
        return `
            <div data-loader></div>
            <div class="content">
                <div class="title">${this.getAttribute('title', i18n('process.register.title'))}</div>
                <p>${this.getAttribute('description', i18n('process.register.description'))}</p>
            </div>
        `;
    }

    /**
     * @inheritDoc
     */
    protected process(): void
    {
        call('/contao/api/license_connector/register', State.get('config')).then((response) => {
            // Check errors
            if(response.error)
            {
                this.reject(response)
                return
            }

            this.resolve(response)
        }).catch((e: Error) => this.reject(e))
    }
}
