import FormField, {FormFieldConfig} from "./FormField";
import {i18n} from "../Language"

export interface CheckboxFieldConfig extends FormFieldConfig {
    value: [],
    options?: {
        required: boolean,
        checked: boolean,
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
        protected options: CheckboxFieldConfig
    ){
        // Create container
        super(options)

        // Add class
        this.addClass('checkbox', 'multiple', 'widget')

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
            <fieldset id="ctrl_${this.options.name}" class="tl_checkbox_container">
                <legend>${i18n('form.field.' + this.options.name + '.label')}</legend>
                
                <input type="hidden" name="${this.options.name}" value="">
                
                <div class="check-all" ${this.options.options.checkAll ? '' : 'style="display:none;"'}>
                    <input type="checkbox" id="check_all_${this.options.name}" class="tl_checkbox" onclick="Backend.toggleCheckboxGroup(this,'ctrl_${this.options.name}')" ${this.options.options.checked ? 'checked' : ''}> 
                    <label for="check_all_${this.options.name}"><em>Alle auswählen</em></label><br>
                </div>  
            </fieldset>
            
            <p>${i18n('form.field.' + this.options.name + '.desc')}</p>
        `)

        const fieldset = this.element('fieldset')

        for (const key in this.options.value)
        {
            if(!this.options.value.hasOwnProperty(key))
            {
                continue
            }

            const label: HTMLLabelElement = document.createElement('label')
            const input: HTMLInputElement = document.createElement('input')

            label.htmlFor = this.options.name + '_' + key
            label.innerHTML = this.options.value[key]

            input.type = 'checkbox'
            input.className = 'tl_checkbox'
            input.name = this.options.name + '[]'
            input.id = label.htmlFor
            input.value = key

            if(this.options.options.checked)
            {
                input.checked = true
            }

            if(this.options.options.required)
            {
                input.required = true
            }

            fieldset.appendChild(input)
            fieldset.appendChild(label)
        }
    }

    public getValue(): string[]
    {
        const values: string[] = []
        const inputs = this.elements('input[name="tables[]"]')

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
