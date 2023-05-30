import ContainerComponent from "../Components/ContainerComponent";

/**
 * Prompt types
 */
export enum PromptType {
    FORM    = 'FORM',
    CONFIRM = 'CONFIRM'
}

/**
 * Abstract prompt class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default abstract class Prompt extends ContainerComponent
{
    /**
     * Dynamic auto-increment id.
     */
    static promptId: number = 0

    /**
     * Resolve function.
     *
     * @protected
     */
    protected resolveFn: Function

    protected constructor(id: string) {
        // Auto-increment id
        Prompt.promptId++

        super(id + Prompt.promptId);

        // Add class
        this.addClass('prompt')
    }

    protected resolve(value: any): void
    {
        this.resolveFn.call(this, value)

        this.template.remove()
    }

    public onResolve(fn: Function): void
    {
        this.resolveFn = fn
    }
}
