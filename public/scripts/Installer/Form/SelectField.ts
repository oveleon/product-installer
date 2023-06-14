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
            <p class="field-desc">${this.description}</p>
        `)

        const selectOptions = {
            options: this.config.value,
            items: this.config.options?.default ?? [],

            create: false,
            allowEmptyOption: true,

            maxItems: this.config?.options?.multiple ? null : 1,
            placeholder: this.config?.options?.placeholder ? this.config.options.placeholder : 'Bitte wählen...',

            render: {
                option: function(data, escape) {
                    let info: string = ''

                    if(data?.info)
                        info = `<span class="info">${data.info}</span>`

                    return `
                        <div class="${data?.class ? data.class : ''}">
                            <span class="text" ${data?.level ? 'style="--level:' + data?.level + ';"' : ''}>${escape(data.text)}</span>
                            ${info}
                        </div>
                    `
                }
            }
        }

        if(this.config.options?.optgroupField)
            selectOptions['optgroupField'] = this.config.options.optgroupField

        if(this.config.options?.optgroups)
            selectOptions['optgroups'] = this.config.options.optgroups

        if(this.config.options?.sortField)
            selectOptions['sortField'] = this.config.options?.sortField ?? [{field:'$order'},{field:'$score'}]
        else
            selectOptions['sortField'] = [{field:'$order'},{field:'$score'}]

        this.select = new TomSelect(<HTMLInputElement> this.element('input'), selectOptions)
    }

    public getValue(): string|string[]
    {
        return this.select.getValue()
    }
}
