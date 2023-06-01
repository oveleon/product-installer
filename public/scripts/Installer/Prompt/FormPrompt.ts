import Prompt from "./Prompt";
import FormField, {FormFieldConfig, FormFieldType} from "../Form/FormField";
import TextField from "../Form/TextField";
import SelectField from "../Form/SelectField";
import CheckboxField from "../Form/CheckboxField";
import {i18n} from "../Language"

/**
 * Prompt configurations.
 */
export interface FormPromptConfig {
    fields: FormFieldConfig[]
}

/**
 * Form prompt class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class FormPrompt extends Prompt
{
    private fields: FormField[] = [];

    constructor(
        public config: FormPromptConfig
    ){
        super('form_prompt');

        // Set content
        this.setContent()
    }

    setContent(): void
    {
        this.content(`
            <form class="fields"></form>
            <div class="actions">
                <button type="button">${i18n('actions.next')}</button>            
            </div>
        `)

        const fieldContainer = this.element('.fields')
        let field: FormField

        for(const fieldOptions of this.config.fields)
        {
            switch (fieldOptions.type)
            {
                case FormFieldType.TEXT:
                    field = new TextField(fieldOptions)
                    break

                case FormFieldType.SELECT:
                    field = new SelectField(fieldOptions)
                    break

                case FormFieldType.CHECKBOX:
                    field = new CheckboxField(fieldOptions)
                    break

                default:
                    continue
            }

            this.fields.push(field);
            field.appendTo(fieldContainer)
        }

        this.element('button').addEventListener('click', () => {

            const collection: {} = {};

            for(const field of this.fields)
            {
                collection[field.name] = field.getValue();
            }

            this.resolve(collection)
        });
    }
}
