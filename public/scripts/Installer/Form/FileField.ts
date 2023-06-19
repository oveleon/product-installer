import FormField, {FormFieldConfig} from "./FormField";
import {i18n} from "../Language"

export type FileFieldConfig = FormFieldConfig & {
    options?: {
        required: boolean,
        popupTitle: string
    }
}

declare var Backend: any;

/**
 * File picker field component class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class FileField extends FormField
{
    /**
     * Creates a text field instance.
     */
    constructor(
        protected  options: FileFieldConfig
    ){
        // Create container
        super(options)

        // Add class
        this.addClass('file', 'widget')

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
                <label>${this.label}</label>
            </h3>
            <input type="text" name="${this.options.name}" id="ctrl_${this.options.name}" class="tl_file" readonly placeholder="${i18n('form.field.files.browse')}">
            <p class="field-desc">${this.description}</p>
        `)

        this.element(`#ctrl_${this.options.name}`).addEventListener('click', (e) => {
            e.preventDefault()

            Backend.openModalSelector({
                "id":    "tl_listing",
                "title": this.options.options?.popupTitle ?? i18n('form.field.files.browse'),
                "url":    '/contao/picker?context=file&amp;extras%5BfieldType%5D=radio&amp;extras%5BfilesOnly%5D=1&amp;extras%5Bextensions%5D=jpg,jpeg,gif,png,tif,tiff,bmp,svg,svgz',
                "callback": (table, value) => {

                    const input = <HTMLInputElement> this.element(`#ctrl_${this.options.name}`)
                          input.value = value

                    console.log(table, value)
                }
            });
        })
    }

    public getValue(): string
    {
        return (this.element('input') as HTMLInputElement).value;
    }
}
