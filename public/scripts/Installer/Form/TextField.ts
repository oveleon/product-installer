import FormField, {FormFieldConfig} from "./FormField";
import {i18n} from "../Language"

export interface TextFieldConfig extends FormFieldConfig {
    options?: {
        required: boolean
    }
}

/**
 * Text field component class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class TextField extends FormField
{
    /**
     * Creates a text field instance.
     */
    constructor(
        protected  options: TextFieldConfig
    ){
        // Create container
        super(options)

        // Add class
        this.addClass('text', 'widget')

        // Create content
        this.setContent()
    }

    /**
     * Generates the text field template.
     *
     * @private
     */
    private setContent(): void
    {
        this.content(`
            <h3>
                <label for="ctrl_${this.options.name}">${i18n('form.field.' + this.options.name + '.label')}</label>
            </h3>
            <input type="text" name="${this.options.name}" id="ctrl_${this.options.name}" class="tl_text" value="${this.options.value}" ${this.options.options.required ? 'required' : ''}>
            <p>${i18n('form.field.' + this.options.name + '.desc')}</p>
        `)
    }

    public getValue(): string
    {
        return (this.element('input') as HTMLInputElement).value;
    }
}
