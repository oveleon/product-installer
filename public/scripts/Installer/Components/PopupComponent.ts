import ContainerComponent from "./ContainerComponent"
import {i18n} from "../Language"
import {unserialize} from 'serialize-like-php'
import ConsoleComponent from "./ConsoleComponent";
import {OperationConfig} from "./ConsoleOperationComponent";

/**
 * Popup Types.
 */
export enum PopupType {
    HTML    = 'HTML',
    TABLE   = 'TABLE',
    AJAX    = 'AJAX',
    IFRAME  = 'IFRAME',
    CONSOLE = 'CONSOLE'
}

/**
 * Popup config.
 */
export type PopupConfig = {
    type: PopupType,
    title: string,
    description?: string,
    content: string|any,
    appendTo: HTMLElement|Function
    closeable?: boolean
    resizeable?: boolean
}

/**
 * Popup component class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class PopupComponent extends ContainerComponent
{
    /**
     * Dynamic auto-increment id.
     */
    static popupId: number = 0

    /**
     * Console.
     *
     * @private
     */
    private console: ConsoleComponent

    /**
     * Resize control vars.
     *
     * @private
     */
    private isResizing: boolean = false
    private lastDownX: number = 0

    /**
     * Creates a popup instance.
     */
    constructor(
        private options: PopupConfig
    ){
        // Auto-increment id
        PopupComponent.popupId++

        // Create container
        super('popup' + PopupComponent.popupId)

        // Add class
        this.addClass('popup')
    }

    /**
     * Creates and shows the popup.
     */
    show(): void
    {
        // Create content
        this.setContent()
    }

    /**
     * Hides the popup.
     */
    hide(): void
    {
        this.template.remove()
    }

    /**
     * Updates the console for Console-Popups
     */
    updateConsole(content: OperationConfig[])
    {
        this.console?.update(content)
    }

    /**
     * Generates the popup template.
     *
     * @private
     */
    private setContent(): void
    {
        let content: string | null;
        let actions: string = '';
        let description: string = '';

        if(this.options?.closeable)
        {
            actions = `
                <div class="actions">
                    <button class="close-popup">${i18n('actions.close')}</button>
                </div>
            `
        }

        if(this.options?.description)
        {
            description = `<p class="desc">${this.options.description}</p>`
        }

        switch (this.options.type)
        {
            case PopupType.IFRAME:
                content = `
                    <div class="inside">
                        <div class="iframe">
                            <h2>${this.options.title}</h2>
                            ${description}
                            <iframe src="${this.options.content}" width="100%" height="100%"></iframe>
                            ${actions}
                        </div>
                    </div>
                `
                break

            case PopupType.TABLE:
                if(typeof this.options.content !== "object")
                {
                    break
                }

                let table = '';

                // Check multiple tables
                if(this.isNumeric(Object.keys(this.options.content)[0]))
                {
                    for(const index in Object.keys(this.options.content))
                    {
                        if(!this.options.content.hasOwnProperty(index))
                        {
                            continue
                        }

                        table += this.arrayToTable(this.options.content[index])
                    }
                }
                else
                {
                    table = this.arrayToTable(this.options.content)
                }

                content = `
                    <div class="inside">
                        <div class="table scrollable">
                            <h2>${this.options.title}</h2>
                            ${description}
                            ${table}
                            ${actions}
                        </div>
                    </div>
                `
                break

            case PopupType.CONSOLE:

                content = null

                this.content(`
                    <div class="inside">
                        <div class="console-popup scrollable">
                            <h2>${this.options.title}</h2>
                            ${description}
                            <div class="console-container"></div>                            
                            ${actions}
                        </div>
                    </div>
                `)

                this.console = new ConsoleComponent();
                this.console.appendTo(this.element('.console-container'))
                this.console.set(this.options.content)

                break

            default:
                content = `
                    <div class="inside">
                        <div class="html scrollable">
                            <h2>${this.options.title}</h2>
                            ${description}
                            ${this.options.content ?? ''}
                            ${actions}
                        </div>
                    </div>
                `
        }

        // Set content
        if(content)
            this.content(content)

        // Bind events
        if(this.options?.closeable)
        {
            this.element('.close-popup')?.addEventListener('click', () => {
                this.hide()
            })
        }

        if(this.options?.resizeable || true)
        {

            const inside: HTMLElement = this.element('.inside')
            const handle: HTMLSpanElement = document.createElement('span')

            handle.classList.add('handle')
            inside.append(handle)

            // Set initial width
            if(window.innerWidth >= 600)
                inside.style.width = '460px'

            handle?.addEventListener('mousedown', (e: MouseEvent) => {
                this.isResizing = true
                this.lastDownX = e.clientX
            })

            document?.addEventListener('mouseup', (e: MouseEvent) => {
                this.isResizing = false
            })

            this.template?.addEventListener('mousemove', (e: MouseEvent) => {
                if(!this.isResizing)
                    return

                const bounds = this.template.getBoundingClientRect();
                const x = e.clientX - bounds.left;
                const width = this.template.offsetWidth - x;

                if(width < 460 || width >= this.template.offsetWidth)
                    return

                inside.style.width = width + 'px'
            })
        }

        let target = this.options.appendTo

        if(target instanceof Function)
        {
            target = <HTMLElement> target.call(this)
        }

        // Append popup
        this.appendTo(target)
    }

    private arrayToTable(data): string {
        let table = document.createElement('table');

        for (const label in data)
        {
            if(!data.hasOwnProperty(label))
            {
                continue
            }

            let value = data[label]

            try{
                const valueObject = unserialize(value)
                const valueArray = []

                let separator = ' '

                for (const [key, part] of Object.entries(valueObject)) {

                    if(part === "")
                    {
                        continue
                    }

                    if(typeof part !== "object")
                    {
                        valueArray.push(part)
                        separator = ', '
                    }
                    else
                    {
                        valueArray.push(this.arrayToTable(part))
                        separator = ' '
                    }
                }

                value = valueArray.join(separator)
            }catch (e){}

            let row = document.createElement('tr');
            let labelCell = document.createElement('td');
            let valueCell = document.createElement('td');

            labelCell.innerHTML = label
            valueCell.innerHTML = value

            row.appendChild(labelCell);
            row.appendChild(valueCell);
            table.appendChild(row);
        }

        return table.outerHTML
    }

    private isNumeric(n): boolean {
        return !isNaN(parseFloat(n)) && isFinite(n);
    }
}
