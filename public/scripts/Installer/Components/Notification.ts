import Container from "./Container"

/**
 * Notification types.
 */
export enum NotificationTypes {
    ERROR = 'error',
    INFO = 'info'
}


/**
 * NotifyOptions.
 */
export interface NotifyOptions {
    closeable?: boolean
}

/**
 * Notification class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class Notification extends Container
{
    /**
     * Dynamic auto-increment id.
     */
    static notificationId: number = 0

    /**
     * Notification options.
     */
    private options: NotifyOptions | null = null

    /**
     * The container for the text.
     *
     * @private
     */
    private readonly textContainer: HTMLParagraphElement

    /**
     * Creates a new notification instance.
     *
     * @param text
     * @param type
     * @param options Use shorthand `true` for a closeable notification
     */
    constructor(text: string, type: NotificationTypes = NotificationTypes.ERROR, options?: NotifyOptions | boolean) {
        // Auto-increment id
        Notification.notificationId++

        // Create container
        super('notification' + Notification.notificationId)

        // Add template class and type
        this.addClass('notification', type)

        // Create text container
        this.textContainer = <HTMLParagraphElement> document.createElement('p')
        this.textContainer.classList.add('text')
        this.template.append(this.textContainer)

        // Set options
        if(typeof options === "boolean")
        {
            this.options = { closeable: true }
        }
        else if(options)
        {
            this.options = options
        }

        // Apply options
        this.applyOptions()

        // Create
        this.setText(text)
    }

    private applyOptions(): void
    {
        if(this.options?.closeable)
        {
            // Create content
            const closeBtn = <HTMLButtonElement> document.createElement('button')

            closeBtn.classList.add('close')
            closeBtn.addEventListener('click', () => {
                this.remove()
            })

            this.template.append(closeBtn)
        }
    }

    /**
     * Sets a notification text.
     *
     * @param text
     */
    public setText(text: string): void
    {
        this.textContainer.innerHTML = text
    }

    /**
     * Remove the notification.
     */
    public remove(): void
    {
        this.template.remove()
    }
}
