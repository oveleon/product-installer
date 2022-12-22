import Container from "./Container"

/**
 * Notification types.
 */
export enum NotificationTypes {
    ERROR = 'error',
    WARN = 'warn',
    INFO = 'info'
}

/**
 * NotifyOptions.
 */
export interface NotifyOptions {
    closeable?: boolean,
    timer?: {
        ms: number,
        text?: string,
        autoClose?: boolean
        onComplete?: Function
    }
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
    constructor(protected text: string, type: NotificationTypes = NotificationTypes.ERROR, options?: NotifyOptions | boolean) {
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

        // Create
        this.setText(text)

        // Apply options
        this.applyOptions()
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

        if(this.options?.timer)
        {
            let seconds = this.options.timer.ms / 1000
            let text = '';

            if(this.options.timer?.text)
            {
                text = this.options.timer.text.replace("#seconds#", seconds.toString())
            }

            this.setText(this.text + ' ' + text)

            const interval = setInterval(() => {
                --seconds

                if(this.options.timer?.text)
                {
                    text = this.options.timer.text.replace("#seconds#", seconds.toString());
                }

                this.setText(this.text + ' ' + text)

                if(seconds === 0)
                {
                    clearInterval(interval)

                    if(this.options.timer?.autoClose)
                    {
                        this.remove()
                    }

                    this.options.timer.onComplete?.call(this)
                }
            }, 1000)
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
