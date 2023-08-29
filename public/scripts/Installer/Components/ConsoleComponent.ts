import ContainerComponent from "./ContainerComponent"
import ConsoleOperationComponent, {OperationConfig} from "./ConsoleOperationComponent";

/**
 * Console class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ConsoleComponent extends ContainerComponent
{
    /**
     * Dynamic auto-increment id.
     */
    static consoleId: number = 0

    /**
     * The container for the text.
     *
     * @private
     */
    private readonly operations: ConsoleOperationComponent[] = []

    /**
     * Creates a console instance.
     */
    constructor()
    {
        ConsoleComponent.consoleId++;

        // Create container
        super('console' + ConsoleComponent.consoleId)

        this.addClass('console')
    }

    /**
     * Set operations configurations to handle.
     *
     * @param operations
     */
    public set(operations: OperationConfig[]): void
    {
        for(const operation of operations)
        {
            const op = new ConsoleOperationComponent(operation);
            op.appendTo(this.template)

            this.operations.push(op)
        }
    }

    public setDescription(desc: string, cssClass?: string): void
    {
        if(!this.template?.querySelector('.description'))
        {
            const descContainer = document.createElement('div')
                  descContainer.classList.add('description')

            this.template.append(descContainer)
        }

        const descElement = this.element('.description')
              descElement.innerHTML = desc

        if(cssClass)
            descElement.classList.add(cssClass)
    }

    /**
     * Update operations configurations.
     *
     * @param operations
     */
    public update(operations: OperationConfig[]): void
    {
        operations.forEach((operation, key) => {
            this.operations[key].update(operation)
        })
    }
}
