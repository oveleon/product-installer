import FormField, {FormFieldConfig} from "./FormField"
import TomSelect from "tom-select"

import "tom-select/dist/css/tom-select.bootstrap5.css"

export type SelectFieldConfig = FormFieldConfig & {
    value: []
    options?: {
        required: boolean
        multiple: boolean
        placeholder: string,
        default: string[]
    }
}

/**
 * Select field component class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class SelectField extends FormField
{
    select: TomSelect

    /**
     * Creates a select field instance.
     */
    constructor(
        protected  config: SelectFieldConfig
    ){
        // Create container
        super(config)

        // Add class
        this.addClass('select', 'widget')

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
                <label for="ctrl_${this.config.name}">${this.label}</label>
            </h3>
            <input />
            <p>${this.description}</p>
        `)

        this.select = new TomSelect(<HTMLInputElement> this.element('input'), {
            options: this.config.value,
            items: this.config.options?.default ?? [],

            create: false,
            allowEmptyOption: true,

            maxItems: this.config?.options?.multiple ? null : 1,
            placeholder: this.config?.options?.placeholder ? this.config.options.placeholder : 'Bitte wählen...'
        })
    }

    public getValue(): string|string[]
    {
        return this.select.getValue()
    }
}
