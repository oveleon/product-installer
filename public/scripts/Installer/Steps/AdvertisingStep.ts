import Step from "../Components/Step";
import {call} from "../../Utils/network"
import {i18n} from "../Language"
import State from "../State";
import ImageBanner from "../Advertising/ImageBanner";

/**
 * A step to display advertisements, which can be created by the License Connector.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class AdvertisingStep extends Step
{
    /**
     * @inheritDoc
     */
    protected getTemplate(): string
    {
        return `
            <div class="advertising"></div>
            <form id="advertising-form">
                <div class="widget checkbox center">
                    <input type="checkbox" name="ad_doNotShowAgain" id="ad_doNotShowAgain" value="1" />
                    <label for="ad_doNotShowAgain">${i18n('advertising.doNotShowAgain')}</label>
                </div>
            </form>
            <div class="actions">
                <button data-prev>${i18n('actions.back')}</button>
                <button type="submit" form="advertising-form" class="primary">${i18n('actions.next')}</button>
            </div>
        `
    }

    /**
     * @inheritDoc
     */
    protected submit(form: HTMLFormElement, data: FormData)
    {
        const doNotShowAgain = !!data.get('ad_doNotShowAgain')

        // Set state
        State.set('skipAdvertising', doNotShowAgain, true);

        this.modal.next();

        // Unlock the form when advertising is allowed to be displayed again
        if(!doNotShowAgain)
        {
            this.lockedForm = false;
        }
    }

    /**
     * @inheritDoc
     */
    protected events(): void
    {
        if(State.get('skipAdvertising'))
        {
            this.modal.next();
        }

        // Show loader
        this.modal.loader(true)

        // Get advertising or skip if no one exists
        call("/contao/api/license_connector/advertising", {
            connector: State.get('connector')
        }, true).then((response) => {
            // Hide loader
            this.modal.loader(false)

            // Check errors
            if(response.error)
            {
                super.error(response)
                return
            }

            // Skip step when going back
            this.skip = true

            switch (response.type)
            {
                case 'image_banner':
                    (new ImageBanner(response))
                        .appendTo(this.element('.advertising'))

                    break;

                case 'skip':
                default:
                    // Show next step if no type is valid
                    this.modal.next()
            }

        }).catch((e: Error) => super.error(e))
    }
}
