import FormField, {FormFieldConfig} from "./FormField";
import {i18n} from "../Language"

export type FieldsetFieldConfig = FormFieldConfig & {
    options: {
        start: boolean
    }
}

/**
 * Fieldset field component class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class FieldsetField extends FormField
{
    /**
     * Creates a fieldset field instance.
     */
    constructor(
        protected  options: FieldsetFieldConfig
    ){
        // Create container
        super(options)

        // Add class
        this.addClass('fieldset')

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
        if(this.options?.options?.start)
        {
            this.content(`
                <h3><label>${this.label}</label></h3>
            `)

            this.addClass('start');
        }
        else
        {
            this.addClass('end');
        }
    }

    public getValue(): string
    {
        return null
    }
}
