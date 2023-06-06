import FormField, {FormFieldConfig} from "./FormField";
import {i18n} from "../Language"

export type CheckboxFieldConfig = FormFieldConfig & {
    value: [{
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
        multiple: boolean
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
        protected options: CheckboxFieldConfig
    ){
        // Create container
        super(options)

        // Add class
        this.addClass('checkbox', 'widget')

        if(this.options?.options?.multiple)
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

        if(this.options.options.multiple)
        {
            this.content(`
                <fieldset id="ctrl_${this.options.name}" class="tl_checkbox_container">
                    <legend>${(this.options.options.label ? this.options.options.label : i18n('form.field.' + this.options.name + '.label'))}</legend>
                    
                    <input type="hidden" name="${this.options.name}" value="">
                    
                    <div class="check-all" ${this.options.options.multiple ? '' : 'style="display:none;"'}>
                        <input type="checkbox" id="check_all_${this.options.name}" class="tl_checkbox" onclick="Backend.toggleCheckboxGroup(this,'ctrl_${this.options.name}')"> 
                        <label for="check_all_${this.options.name}">Alle auswählen</label><br>
                    </div>  
                </fieldset>
                
                <p>${(this.options.options.description ? this.options.options.description : i18n('form.field.' + this.options.name + '.desc'))}</p>
            `)

            fieldset = <HTMLDivElement> this.element('fieldset')
        }
        else
        {
            fieldset = <HTMLDivElement> this.template
        }

        for (const key in this.options.value)
        {
            if(!this.options.value.hasOwnProperty(key))
            {
                continue
            }

            const option = this.options.value[key]
            const label: HTMLLabelElement = document.createElement('label')
            const input: HTMLInputElement = document.createElement('input')

            label.htmlFor   = this.options.name + '_' + key
            input.name      = this.getName()
            input.type      = 'checkbox'
            input.id        = label.htmlFor
            input.className = 'tl_checkbox'

            label.innerHTML = option.text + (option.options?.description ? '<span>' + option.options?.description + '</span>' : '')
            input.value     = option.value

            if(option.options?.checked)
                input.checked = true

            if(option.options?.disabled)
                input.disabled = true

            if(this.options?.options?.required)
                input.required = true

            fieldset.appendChild(input)
            fieldset.appendChild(label)
        }
    }

    private getName(): string
    {
        if(this.options.options.multiple)
        {
            return this.options.name + '[]'
        }

        return this.options.name
    }

    public getValue(): string[]
    {
        const values: string[] = []
        const inputs = this.elements('input[name="' + this.getName() + '"]')

        for(const input of inputs)
        {
            const field = input as HTMLInputElement

            if(field.checked)
            {
                values.push(field.value)
            }
        }

        return values
    }
}
