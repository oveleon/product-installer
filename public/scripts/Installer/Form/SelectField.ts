import FormField, {FormFieldConfig} from "./FormField";
import {i18n} from "../Language"

export interface SelectFieldConfig extends FormFieldConfig {
    value: [],
    options?: {
        required: boolean
    }
}

/**
 * Select field component class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class SelectField extends FormField
{
    /**
     * Creates a select field instance.
     */
    constructor(
        protected  options: SelectFieldConfig
    ){
        // Create container
        super(options)

        // Add class
        this.addClass('select', 'w50', 'widget')

        // Create content
        this.setContent()
    }

    /**
     * Generates the select field template.
     *
     * @private
     */
    private setContent(): void
    {
        this.content(`
            <h3>
                <label for="ctrl_${this.options.name}">${i18n('form.field.' + this.options.name + '.label')}</label>
            </h3>
            <select name="${this.options.name}" id="ctrl_${this.options.name}" class="tl_select" ${this.options.options.required ? 'required' : ''}></select>
            <p>${i18n('form.field.' + this.options.name + '.desc')}</p>
        `)

        const select = this.element('select')

        for (const key in this.options.value)
        {
            if(!this.options.value.hasOwnProperty(key))
            {
                continue
            }

            const opt = document.createElement('option')

            opt.value = key
            opt.innerHTML = this.options.value[key]

            select.appendChild(opt)
        }
    }

    public getValue(): string
    {
        return (this.element('select') as HTMLSelectElement).value;
    }
}
