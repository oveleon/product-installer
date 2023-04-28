import StepComponent from "../Components/StepComponent";
import {i18n} from "../Language"
import {call} from "../../Utils/network"
import State from "../State";

/**
 * Import content package step.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ImportContentPackageStep extends StepComponent
{
    /**
     * @inheritDoc
     */
    protected getTemplate(): string
    {
        return `
            <h2>${i18n('import.content_package.headline')}</h2>
            <div class="import">
                Run...
            </div>
        `
    }

    /**
     * @inheritDoc
     */
    protected events(): void
    {
        // Show loader
        //this.modal.loader(true, i18n('setup.loading'))
    }
}
