import FormField, {FormFieldConfig} from "./FormField"
import {createPopper} from "@popperjs/core"
import TomSelect from "tom-select"

import "tom-select/dist/css/tom-select.bootstrap5.css"

export type SelectFieldConfig = FormFieldConfig & {
    value: []
    options?: {
        required: boolean
        multiple: boolean
        placeholder: string
        default: string[]
        optgroupField: string
        optgroups: []
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

        this.select = new TomSelect(<HTMLInputElement> this.element('input'), this.getOptions())
    }

    private getOptions(): {}
    {
        const selectOptions = {
            options: this.config.value,
            items: this.config.options?.default ?? [],

            create: false,
            maxOptions: null,
            allowEmptyOption: true,

            maxItems: this.config?.options?.multiple ? null : 1,
            placeholder: this.config?.options?.placeholder ? this.config.options.placeholder : 'Bitte wählen...',

            onInitialize: function(){
                this.popper = createPopper(this.control,this.dropdown);
            },
            onDropdownOpen: function(){
                this.popper.update();
            },
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

        // Set plugins
        const plugins: string[] = [];

        if(this.config?.options?.multiple)
        {
            plugins.push('checkbox_options')
            plugins.push('clear_button')
        }

        // Set option group field
        if(this.config.options?.optgroupField)
            selectOptions['optgroupField'] = this.config.options.optgroupField
        else
            selectOptions['optgroupField'] = 'group'

        // Set option groups
        if(this.config.options?.optgroups)
            selectOptions['optgroups'] = this.config.options.optgroups

        // Apply sorting
        if(this.config.options?.sortField)
            selectOptions['sortField'] = this.config.options?.sortField ?? [{field:'$order'},{field:'$score'}]
        else
            selectOptions['sortField'] = [{field:'$order'},{field:'$score'}]

        // Add plugins
        selectOptions['plugins'] = plugins

        return selectOptions
    }

    public getValue(): string|string[]
    {
        return this.select.getValue()
    }
}
