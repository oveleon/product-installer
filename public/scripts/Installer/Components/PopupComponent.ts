import ContainerComponent from "./ContainerComponent"
import {i18n} from "../Language"

/**
 * Popup Types.
 */
export enum PopupType {
    HTML,
    TABLE,
    AJAX,
    IFRAME
}

/**
 * Popup config.
 */
export interface PopupConfig {
    type: PopupType,
    title: string,
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
        let actions: string;

        if(this.options?.closeable)
        {
            actions = `
                <div class="actions">
                    <button class="close-popup">${i18n('actions.close')}</button>
                </div>
            `
        }

        switch (this.options.type)
        {
            case PopupType.IFRAME:
                content = `
                    <div class="iframe">
                        <h2>${this.options.title}</h2>
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

                content = `
                    <div class="table scrollable">
                        <h2>${this.options.title}</h2>
                        ${this.arrayToTable(this.options.content)}
                        ${actions}
                    </div>
                `
                break

            default:
                content = `
                    <div class="html scrollable">
                        <h2>${this.options.title}</h2>
                        ${this.options.content}
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
}
