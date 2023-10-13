import State from "../State";
import {i18n} from "../Language"
import StepComponent from "../Components/StepComponent";
import { Dropzone } from "dropzone";
import NotificationComponent, {NotificationTypes} from "../Components/NotificationComponent";

/**
 * Upload step class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class UploadStep extends StepComponent
{
    /**
     * Dropzone.
     *
     * @private
     */
    private dropzone: Dropzone = null

    /**
     * @inheritDoc
     */
    getTemplate(): string
    {
        return `
            <h2>${this.getAttribute('title', i18n('upload.headline'))}</h2>
            <p>${this.getAttribute('description', i18n('upload.description'))}</p>
            <div class="file-upload"></div>
            <div class="actions">
                <button data-prev>${i18n('actions.back')}</button>
            </div>
        `
    }

    /**
     * @inheritDoc
     */
    protected events(): void
    {
        const backButton: HTMLButtonElement = <HTMLButtonElement> this.element('[data-prev]')

        this.dropzone = new Dropzone(".file-upload", {
            url: "/api/upload/product/upload",
            maxFiles: 1,
            disablePreviews: true,
            acceptedFiles: '.content',
            addedfile: () => {
                backButton.disabled = true

                this.modal.loader(true, i18n('upload.loading'))
            },
            success: (file, response) => {
                // Check errors
                if(response.error)
                {
                    super.error(response)
                    return
                }

                // Get config
                let config = State.get('config') ?? {}

                // Overwrite products
                config['products'] = response.products
                config['installable'] = response.installable

                // Save config
                State.set('config', config)

                // Hide loader and show next step
                this.modal.loader(false)
                this.modal.next()
            },
            error: (file, message) => {
                (new NotificationComponent(i18n('error.default'), message, NotificationTypes.ERROR, true))
                    .appendTo(this.modal.notificationContainer)

                backButton.disabled = false
                this.modal.loader(false)
            }
        });
    }

    /**
     * @inheritDoc
     */
    protected unmount() {
        this.dropzone?.destroy()
    }
}
