import ContainerComponent from "./ContainerComponent"
import ModalComponent from "./ModalComponent";
import {i18n} from "../Language"
import NotificationComponent, {NotificationTypes} from "./NotificationComponent";

/**
 * Step error response.
 */
export interface StepErrorResponse extends Error {
    error?: number | boolean,
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
export default abstract class StepComponent extends ContainerComponent
{
    /**
     * Dynamic auto-increment id.
     */
    static stepId: number = 0

    /**
     * Defines if the form is locked
     *
     * @protected
     */
    protected lockedForm: boolean = false

    /**
     * Defines if the step would be skipped.
     *
     * @protected
     */
    public skip: boolean = false

    /**
     * Defines if the step is locked.
     *
     * @protected
     */
    public locked: boolean = false

    /**
     * Contains the current step configuration.
     *
     * @protected
     */
    protected config: StepConfig

    /**
     * Defines the associated modal instance.
     *
     * @protected
     */
    protected modal: ModalComponent

    /**
     * Creates a new step instance and hides it immediately.
     */
    constructor() {
        // Create container
        super('step' + StepComponent.stepId++)

        // Steps are hidden by default
        this.hide()
    }

    /**
     * Add a modal instance.
     *
     * @param modal
     */
    public addModal(modal: ModalComponent): void
    {
        this.modal = modal

        this.mount()
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
     * @param fallback
     */
    public getAttribute(attr: string, fallback?: string): any
    {
        if(!this.config?.attributes[attr])
        {
            return fallback ? fallback : ''
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

        // Show step before bind events (e.g. to be able to use this.modal.next() within the event method)
        super.show()

        // Bind default events
        this.defaultEvents()

        // Bind custom events
        this.events()
    }

    /**
     * Removes a step.
     */
    public remove(): void
    {
        this.unmount()
        this.modal.removeStep(this)
        this.template.remove()
    }

    /**
     * Register default events.
     */
    private defaultEvents(): void
    {
        // Default button events
        this.element('[data-close]')?.addEventListener('click', () => this.modal.hide())
        this.element('[data-prev]')?.addEventListener('click', () => this.modal.prev())
        this.element('[data-next]')?.addEventListener('click', () => this.modal.next())

        // Default form submit event
        this.element('form')?.addEventListener('submit', (e) => this.formSubmit(e))
    }

    /**
     * Handle errors.
     *
     * @param response
     */
    protected error(response: StepErrorResponse): void
    {
        // Hide loader
        this.modal.loader(false)

        // Unlock form
        this.lockedForm = false

        // Check if there are field errors
        if(response?.fields)
        {
            const form = <HTMLFormElement> this.element('form')

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

        // Check if there are a message to show
        if(response.message)
        {
            (new NotificationComponent(i18n('error.default'), response.message, NotificationTypes.ERROR, true))
                .appendTo(this.modal.notificationContainer)
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
     * Called when step is mounted.
     *
     * @protected
     */
    protected mount(): void {}

    /**
     * Called when step is unmounted.
     *
     * @protected
     */
    protected unmount(): void {}

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
