import Container from "./Container"

/**
 * Loader modes.
 */
export enum LoaderMode {
    INLINE= 'inlined',
    COVER = 'cover'
}

/**
 * Loader class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class Loader extends Container
{
    /**
     * Dynamic auto-increment id.
     */
    static loaderId: number = 0

    /**
     * The container for the spinner.
     *
     * @private
     */
    private readonly spinnerContainer: HTMLDivElement

    /**
     * The container for the text.
     *
     * @private
     */
    private readonly textContainer: HTMLParagraphElement

    /**
     * Creates a new loader instance.
     */
    constructor() {
        // Auto-increment id
        Loader.loaderId++

        // Create container
        super('loader' + Loader.loaderId)

        // Add template class
        this.addClass('loader')

        // Create content
        this.spinnerContainer = <HTMLDivElement> document.createElement('div')
        this.spinnerContainer.classList.add('spinner')
        this.spinnerContainer.innerHTML = `
          <div></div>
          <div></div>
          <div></div>
          <div></div>
          <div></div>
          <div></div>
        `
        this.textContainer = <HTMLParagraphElement> document.createElement('p')
        this.textContainer.classList.add('text')

        this.template.append(this.spinnerContainer)
        this.template.append(this.textContainer)

        // Loader defaults
        this.hide()
        this.play()
        this.setMode(LoaderMode.INLINE)
    }

    /**
     * Sets a specific loader mode.
     *
     * @param type
     */
    public setMode(type: LoaderMode)
    {
        this.removeClass(
            LoaderMode.INLINE,
            LoaderMode.COVER
        )

        this.addClass(type)
    }

    /**
     * Sets a loader text.
     *
     * @param text
     */
    public setText(text: string): void
    {
        this.textContainer.innerHTML = text
    }

    /**
     * Hides the loader.
     */
    public hide(): void
    {
        this.setText('')
        super.hide()
    }

    /**
     * Starts the loader animation.
     */
    public play(): void
    {
        this.addClass('play')
    }

    /**
     * Pauses the loader animation.
     */
    public pause(): void
    {
        this.removeClass('play')
    }
}
