import Container from "./Container"
import Modal from "./Modal";

/**
 * Step error response.
 */
export interface StepErrorResponse {
    error: number | boolean,
    fields?: []
}

/**
 * Base step configurations.
 */
export interface StepConfig {
    name: string,
    routes: string,
    attributes?: any
}

/**
 * Abstract step class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default abstract class Step extends Container
{
    static stepId: number = 0

    protected lockedForm: boolean = false
    protected config: StepConfig
    protected modal: Modal

    /**
     * Creates a new step instance and hides it immediately.
     */
    constructor() {
        // Create container
        super('step' + Step.stepId++)

        // Steps are hidden by default
        this.hide()
    }

    /**
     * Add a modal instance.
     *
     * @param modal
     */
    public addModal(modal: Modal): void
    {
        this.modal = modal
    }

    /**
     * Set step configuration.
     *
     * @param config
     */
    public setConfig(config: StepConfig): void
    {
        this.config = config
    }

    /**
     * Returns a route by name.
     *
     * @param routeName
     */
    public getRoute(routeName: string): string
    {
        if(!this.config?.routes[routeName])
        {
            throw new Error(`No route could be found for the name ${routeName}`)
        }

        return this.config.routes[routeName]
    }

    /**
     * Returns an attribute by name.
     *
     * @param attr
     */
    public getAttribute(attr: string): any
    {
        if(!this.config?.attributes[attr])
        {
            return ''
        }

        return this.config.attributes[attr]
    }

    /**
     * @inheritDoc
     */
    public show(): void
    {
        // Update content before show
        super.content(this.getTemplate())

        // Bind default events
        this.defaultEvents()

        // Bind custom events
        this.events()

        // Show step
        super.show()
    }

    /**
     * Register default events.
     */
    private defaultEvents(): void
    {
        // Default button events
        this.template.querySelector('[data-close]')?.addEventListener('click', () => this.modal.hide())
        this.template.querySelector('[data-prev]')?.addEventListener('click', () => this.modal.prev())
        this.template.querySelector('[data-next]')?.addEventListener('click', () => this.modal.next())

        // Default form submit event
        this.template.querySelector('form')?.addEventListener('submit', (e) => this.formSubmit(e))
    }

    /**
     * Handle errors.
     *
     * @param response
     */
    protected error(response: StepErrorResponse): void
    {
        // Unlock form
        this.lockedForm = false

        // Check if there are field errors
        if(response?.fields)
        {
            const form = <HTMLFormElement> this.template.querySelector('form')

            for(const f in response.fields)
            {
                // Add error css class
                form[f].parentElement.classList.add('error')

                // Check if the field already has an error text
                if(form[f].nextElementSibling)
                {
                    // Change error text
                    form[f].nextElementSibling.innerHTML = response.fields[f]
                }else{
                    // Add error text
                    const errorText = document.createElement('p')
                    errorText.innerHTML = response.fields[f]

                    form[f].after(errorText)
                }

                // Add event
                form[f].addEventListener('input', () => {
                    form[f].parentElement.classList.remove('error')
                }, {once: true})
            }
        }
    }

    /**
     * Default form submit event to validate and prevent double clicks.
     *
     * @protected
     */
    protected formSubmit(event: SubmitEvent): void
    {
        event.preventDefault()

        const form = <HTMLFormElement> event.target
        const data = new FormData(form)

        if(!form.checkValidity())
        {
            form.reportValidity()
            return;
        }

        if(!this.lockedForm)
        {
            this.lockedForm = true;
            this.submit(form, data, event)
        }
    }

    /**
     * Set events.
     *
     * @protected
     */
    protected events(): void {}

    /**
     * Handle form submits.
     *
     * @protected
     */
    protected submit(form: HTMLFormElement, data: FormData, event: SubmitEvent): void {}

    /**
     * Returns the template structure.
     *
     * @protected
     */
    protected abstract getTemplate(): string
}