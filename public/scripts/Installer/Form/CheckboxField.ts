import FormField, {FormFieldConfig} from "./FormField";
import {i18n} from "../Language"

export type CheckboxFieldConfig = FormFieldConfig & {
    value: [{
        name: string
        value: string
        text: string
        options?: {
            checked: boolean
            disabled: boolean
            description: string
        }
    }],
    options?: {
        required: boolean
        multiple: boolean,
        checkAll: boolean
    }
}

/**
 * Checkbox field component class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class CheckboxField extends FormField
{
    /**
     * Creates a checkbox field instance.
     */
    constructor(
        protected config: CheckboxFieldConfig
    ){
        // Create container
        super(config)

        // Add class
        this.addClass('checkbox', 'widget')

        if(this.config?.options?.multiple)
        {
            this.addClass('multiple')
        }

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
        let fieldset: HTMLDivElement

        if(this.config.options.multiple)
        {
            this.content(`
                <fieldset id="ctrl_${this.config.name}" class="tl_checkbox_container">
                    <legend>${(this.config.options.label ? this.config.options.label : i18n('form.field.' + this.config.name + '.label'))}</legend>
                </fieldset>
                
                <p>${(this.config.options.description ? this.config.options.description : i18n('form.field.' + this.config.name + '.desc'))}</p>
            `)

            fieldset = <HTMLDivElement> this.element('fieldset')

            // Create check all element
            if(this.config.options.checkAll)
            {
                const container = document.createElement('div')

                container.innerHTML = `
                    <div class="check-all" ${this.config.options.multiple ? '' : 'style="display:none;"'}>
                        <input type="checkbox" id="check_all_${this.config.name}" class="tl_checkbox check_all"> 
                        <label for="check_all_${this.config.name}">Alle auswählen</label><br>
                    </div>
                `

                container.querySelector('input').addEventListener('change', (e) => {
                    const trigger = <HTMLInputElement> e.currentTarget
                    const inputs = this.elements('.input-' + this.name)

                    for(const input of inputs)
                    {
                        const field = input as HTMLInputElement

                        if(!field.disabled)
                        {
                            field.checked = trigger.checked
                        }
                    }
                })

                fieldset.appendChild(container)
            }
        }
        else
        {
            fieldset = <HTMLDivElement> this.template
        }

        for (const key in this.config.value)
        {
            if(!this.config.value.hasOwnProperty(key))
            {
                continue
            }

            const option = this.config.value[key]
            const label: HTMLLabelElement = document.createElement('label')
            const input: HTMLInputElement = document.createElement('input')

            label.htmlFor   = option.name
            input.name      = option.name
            input.type      = 'checkbox'
            input.id        = label.htmlFor
            input.className = 'tl_checkbox input-' + this.name

            label.innerHTML = option.text + (option.options?.description ? '<span>' + option.options?.description + '</span>' : '')
            input.value     = option.value

            if(option.options?.checked)
                input.checked = true

            if(option.options?.disabled)
                input.disabled = true

            if(this.config?.options?.required)
                input.required = true

            fieldset.appendChild(input)
            fieldset.appendChild(label)
        }
    }

    public getValue(): object|object[]
    {
        let values: {} = {}
        const inputs = this.elements('.input-' + this.name)

        for(const input of inputs)
        {
            const field = input as HTMLInputElement

            values[field.name] = field.checked ? field.value : ""

            if(!this.config.options.multiple)
            {
                return values[field.name]
            }
        }

        return values
    }
}
