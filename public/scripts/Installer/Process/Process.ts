import Container from "../Components/Container";
import Loader from "../Components/Loader";
import ProcessManager from "./ProcessManager";

/**
 * Error response for processes.
 */
export interface ProcessErrorResponse {
    error: number | boolean,
    messages?: string[]
}

/**
 * Process configuration.
 */
export interface ProcessConfig {
    name: string,
    routes: {}
    attributes?: any
    parameter?: {} | Function
    onResolve?: Function
    onReject?: Function
}

/**
 * Abstract process class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default abstract class Process extends Container
{
    static processId: number = 0

    protected loader: Loader
    protected errorContainer: HTMLDivElement
    protected manager: ProcessManager

    /**
     * Creates a new process instance.
     */
    constructor(
        protected parentContainer: HTMLElement,
        public config: ProcessConfig
    ){
        // Create container
        super('process' + Process.processId++)

        // Create process step template
        this.addClass('process-step', 'not-active')
        this.content(this.getTemplate())
        this.appendTo(this.parentContainer)

        // Add error message container
        this.errorContainer = <HTMLDivElement> document.createElement('div')
        this.errorContainer.classList.add('errors')
        this.template.append(this.errorContainer)

        // Add loader
        const loaderContainer = <HTMLDivElement> this.element('[data-loader]')

        if(loaderContainer)
        {
            this.loader = new Loader()
            this.loader.show()
            this.loader.pause()
            this.loader.appendTo(loaderContainer)
        }

        this.mount()
    }

    /**
     * Bind a manager instance to a process step.
     *
     * @param manager
     */
    public addManager(manager: ProcessManager): void
    {
        this.manager = manager
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
     * Returns the parameters of the process.
     */
    public getParameter(): {}
    {
        if(typeof this.config.parameter === 'function')
        {
            return this.config.parameter.call(this)
        }

        return this.config.parameter
    }

    /**
     * Reset process.
     */
    public reset(): void
    {
        this.addClass('not-active')

        this.loader?.pause()
        this.loader?.removeClass('done', 'fail', 'pause')

        this.element('div.errors')?.remove()
    }

    /**
     * Starts a single process.
     */
    public start(): void
    {
        this.loader?.play()
        this.removeClass('not-active')

        // Start process
        this.process()
    }

    /**
     * Resolve process.
     *
     * @protected
     */
    protected resolve(response: any): void
    {
        this.loader?.pause()
        this.loader?.addClass('done')

        this.config?.onResolve?.call(this, response)
        this.manager.callResolve(this, response)

        // Start next process
        this.manager.next()
    }

    /**
     * Reject process.
     *
     * @param response
     *
     * @protected
     */
    protected reject(response: Error | ProcessErrorResponse): void
    {
        this.loader?.pause()
        this.loader?.addClass('fail')

        this.config?.onReject?.call(this, response)
        this.manager.callReject(this, response)
        this.error(response)
    }

    /**
     * Shows occurred errors in the process.
     *
     * @protected
     */
    protected error(data: any): void
    {
        // Check for messages of intercepted errors
        if(data?.messages)
        {
            for (const text of data.messages)
            {
                this.addErrorParagraph(text)
            }
        }

        // Check whether a fatal error has occurred.
        // For example, no connection could be established to the server
        if(data?.message)
        {
            this.addErrorParagraph(data.message)
        }
    }

    /**
     * Adds a paragraph to the error container.
     *
     * @param content
     *
     * @protected
     */
    protected addErrorParagraph(content: string): void
    {
        const msg = <HTMLParagraphElement> document.createElement('p')
        msg.innerText = content
        this.errorContainer.append(msg)
    }

    /**
     * Pause process.
     *
     * @protected
     */
    protected pause(): void
    {
        this.loader?.pause()
        this.loader?.addClass('pause')
    }

    /**
     * Allows manipulation for process specific properties.
     *
     * @protected
     */
    protected mount(): void {}

    /**
     * Start the process.
     *
     * @protected
     */
    protected abstract process(): void

    /**
     * Template for process step.
     *
     * @protected
     */
    protected abstract getTemplate(): string
}
