import ContainerComponent from "../Components/ContainerComponent";
import {i18n} from "../Language"
import PopupComponent, {PopupType} from "../Components/PopupComponent";

export enum FormFieldType {
    TEXT = 'text',
    SELECT = 'select',
    CHECKBOX = 'checkbox'
}

export type FormFieldConfig = {
    name: string
    value: any
    type: string,
    options?: {
        label: string
        description: string
        class: string
        info: string
    }
}

/**
 * Form field component class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default abstract class FormField extends ContainerComponent
{
    /**
     * Dynamic auto-increment id.
     */
    static fieldId: number = 0

    /**
     * Creates a drop menu instance.
     */
    constructor(
        protected config: FormFieldConfig
    ){
        // Auto-increment id
        FormField.fieldId++

        // Create container
        super('field' + FormField.fieldId)

        if(this.config?.options?.class)
        {
            // Handle whitespaces
            if(/\s/.test(this.config.options.class))
            {
                for (const token of this.config.options.class.split(" "))
                {
                    this.addClass(token)
                }
            }else{
                this.addClass(this.config.options.class)
            }
        }
    }

    get name(): string
    {
        return this.config.name
    }

    get label(): string
    {
        return this.config?.options?.label ? this.config.options.label : i18n('form.field.' + this.config.name + '.label')
    }

    get description(): string
    {
        return this.config?.options?.description ? this.config.options.description : i18n('form.field.' + this.config.name + '.desc')
    }

    content(html: string): void
    {
        super.content(html);

        if(this.config?.options?.info)
        {
            let infoElement = this.element('legend')

            if (!infoElement)
                infoElement = this.element('label')

            if (infoElement) {
                const popup = new PopupComponent({
                    type: PopupType.HTML,
                    appendTo: () => this.template.closest('.inside'),
                    title: 'Feldbeschreibung',
                    content: this.config.options.info,
                    closeable: true
                })

                const helper = document.createElement('span')

                helper.innerHTML = '?'
                helper.className = 'info-helper'
                helper.addEventListener('click', () => {
                    popup.show()
                })

                infoElement.appendChild(helper)
            }
        }
    }

    abstract getValue(): string|string[]|object|object[];
}
