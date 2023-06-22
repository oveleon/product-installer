import ContainerComponent from "./ContainerComponent"
import {i18n} from "../Language"

/**
 * Popup Types.
 */
export enum PopupType {
    HTML   = 'HTML',
    TABLE  = 'TABLE',
    AJAX   = 'AJAX',
    IFRAME = 'IFRAME'
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
     * Generates the popup template.
     *
     * @private
     */
    private setContent(): void
    {
        let content: string;
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
                    <div class="iframe">
                        <h2>${this.options.title}</h2>
                        ${description}
                        <iframe src="${this.options.content}" width="100%" height="100%"></iframe>
                        ${actions}
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
                    <div class="table scrollable">
                        <h2>${this.options.title}</h2>
                        ${description}
                        ${table}
                        ${actions}
                    </div>
                `
                break

            default:
                content = `
                    <div class="html scrollable">
                        <h2>${this.options.title}</h2>
                        ${description}
                        ${this.options.content ?? ''}
                        ${actions}
                    </div>
                `
        }

        // Set content
        this.content(content)

        // Bind events
        this.element('.close-popup').addEventListener('click', () => {
            this.hide()
        })

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

            const value = data[label]

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
