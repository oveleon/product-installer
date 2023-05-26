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
    protected resolveFn: Function

    public onResolve(fn: Function): void
    {
        this.resolveFn = fn
    }

    protected resolve(): void
    {
        this.resolveFn.call(this)
    }

    abstract getTemplate(): string;
}
