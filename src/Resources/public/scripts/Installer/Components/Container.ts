/**
 * Container class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class Container
{
    /**
     * The container template.
     */
    public template: HTMLDivElement

    /**
     * Creates a new container instance.
     *
     * @param id
     */
    constructor(
        public id: string
    ){
        this.create()
    }

    /**
     * Creates a new container template.
     *
     * @private
     */
    private create(): void
    {
        this.template = <HTMLDivElement> document.createElement('div')
        this.template.id = this.id
    }

    /**
     * Append container template to another HTMLElement.
     *
     * @param target
     */
    public appendTo(target: string | HTMLElement): void
    {
        if(target instanceof HTMLElement)
        {
            target.append(this.template)
            return;
        }

        document.querySelector(target).append(this.template)
    }

    /**
     * Set the html content to the container template.
     *
     * @param html
     */
    public content(html: string): void
    {
        this.template.innerHTML = html
    }

    /**
     * Hides the container template.
     */
    public hide(): void
    {
        this.template.hidden = true
    }

    /**
     * Shows the container template.
     */
    public show(): void
    {
        this.template.hidden = false
    }

    /**
     * Adds css classes to the container template.
     *
     * @param className
     */
    public addClass(...className: string[]): void
    {
        this.template.classList.add(...className)
    }

    /**
     * Removes css classes from the container template.
     *
     * @param className
     */
    public removeClass(...className: string[]): void
    {
        this.template.classList.remove(...className)
    }
}
