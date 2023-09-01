import FormField, {FormFieldConfig} from "./FormField";
import {i18n} from "../Language"

export type FileFieldConfig = FormFieldConfig & {
    options?: {
        required: boolean
        multiple: boolean
        allowedExtensions: string
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

            let type = 'radio'
            let extensions = this.options.options?.allowedExtensions ?? 'jpg,jpeg,gif,png,tif,tiff,bmp,svg,svgz'

            if(this.options.options?.multiple)
                type = 'checkbox'

            const files = new URL('/contao/picker', window.location.origin);

            // No extensions = folder
            if(extensions)
                files.searchParams.append('extras[filesOnly]', '1')

            files.searchParams.append('context', 'file')
            files.searchParams.append('extras[fieldType]', type)
            files.searchParams.append('extras[extensions]', extensions)

            //files.searchParams.append('value', this.getValue())

            Backend.openModalSelector({
                "id":    "tl_listing",
                "title": this.options.options?.popupTitle ?? i18n('form.field.files.browse'),
                "url":   files.href,
                "callback": (table, value) => {

                    const input = <HTMLInputElement> this.element(`#ctrl_${this.options.name}`)
                          input.value = value
                }
            });
        })
    }

    public getValue(): string
    {
        return (this.element('input') as HTMLInputElement).value;
    }
}
