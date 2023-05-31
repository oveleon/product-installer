import ContainerComponent from "../Components/ContainerComponent";

export enum FormFieldType {
    TEXT = 'text',
    SELECT = 'select',
    CHECKBOX = 'checkbox'
}

export interface FormFieldConfig {
    name: string,
    value: any,
    type: string
}

/**
 * Form field component class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default abstract class FormField extends ContainerComponent
{
    /**
     * Dynamic auto-increment id.
     */
    static fieldId: number = 0

    /**
     * Creates a drop menu instance.
     */
    constructor(
        protected options: FormFieldConfig
    ){
        // Auto-increment id
        FormField.fieldId++

        // Create container
        super('field' + FormField.fieldId)
    }

    get name(): string
    {
        return this.options.name
    }

    abstract getValue(): string|string[];
}
