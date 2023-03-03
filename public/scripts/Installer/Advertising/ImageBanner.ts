import AdvertisingBanner, {AdvertisingConfig} from "./AdvertisingBanner";

/**
 * Advertising image banner class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ImageBanner extends AdvertisingBanner
{
    constructor(config: AdvertisingConfig)
    {
        super('image_banner', config);
    }

    getTemplate() {
        let button = '';

        if(this.config.url && this.config.linkText)
        {
            button = `<a class="button center" href="${this.config.url}" target="_blank">${this.config.linkText}</a>`
        }

        return `
            <img src="${this.config.image}"/>
            <div class="overlay">
                ${button}
            </div>
        `;
    }
}
