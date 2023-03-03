import Container from "../Components/Container";

/**
 * Advertising configurations.
 */
export interface AdvertisingConfig {
    title: string,
    type: string,
    url: string,
    image: string,
    linkText: string
}

export default abstract class AdvertisingBanner extends Container
{
    constructor(id: string, protected config: AdvertisingConfig)
    {
        super(id)
        this.content(this.getTemplate())
    }

    abstract getTemplate(): string;
}
