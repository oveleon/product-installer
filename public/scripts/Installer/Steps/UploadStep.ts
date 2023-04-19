import State from "../State";
import {call} from "../../Utils/network"
import {i18n} from "../Language"
import StepComponent from "../Components/StepComponent";

/**
 * Upload step class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class UploadStep extends StepComponent
{
    /**
     * @inheritDoc
     */
    getTemplate(): string
    {
        return `
            <h2>${this.getAttribute('title', i18n('upload.headline'))}</h2>
            <p>${this.getAttribute('description', i18n('upload.description'))}</p>
            <form id="upload-form" class="inherit" autocomplete="off">
                <div class="widget text">
                    <label for="upload">${i18n('upload.form.label.upload')}</label>
                    <input type="file" name="upload" id="upload" autocomplete="off" required/>
                </div>
            </form>
            <div class="actions">
                <button data-prev>${i18n('actions.back')}</button>
                <button type="submit" form="upload-form" class="check primary">${i18n('upload.actions.next')}</button>
            </div>
        `
    }

    /**
     * @inheritDoc
     */
    protected submit(form: HTMLFormElement, data: FormData)
    {
        // Show loader
        this.modal.loader(true)

    }
}
