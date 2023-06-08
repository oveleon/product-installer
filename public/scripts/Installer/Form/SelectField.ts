import FormField, {FormFieldConfig} from "./FormField"
import TomSelect from "tom-select"

import "tom-select/dist/css/tom-select.bootstrap5.css"

export type SelectFieldConfig = FormFieldConfig & {
    value: []
    options?: {
        required: boolean
        multiple: boolean
        placeholder: string,
        default: string[],
        optgroupField: string,
        optgroups: [],
        sortField: []
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

        console.log(this.config.value)

        const selectOptions = {
            options: this.config.value,
            items: this.config.options?.default ?? [],

            create: false,
            allowEmptyOption: true,

            maxItems: this.config?.options?.multiple ? null : 1,
            placeholder: this.config?.options?.placeholder ? this.config.options.placeholder : 'Bitte wählen...',

            render: {
                option: function(data, escape) {
                    return `<div class="${data?.level ? 'level_' + data.level : ''}">${escape(data.text)}</div>`
                }
            }
        }

        if(this.config.options?.optgroupField)
            selectOptions['optgroupField'] = this.config.options.optgroupField

        if(this.config.options?.optgroups)
            selectOptions['optgroups'] = this.config.options.optgroups

        if(this.config.options?.sortField)
            selectOptions['sortField'] = this.config.options.sortField

        this.select = new TomSelect(<HTMLInputElement> this.element('input'), selectOptions)
    }

    public getValue(): string|string[]
    {
        return this.select.getValue()
    }
}
