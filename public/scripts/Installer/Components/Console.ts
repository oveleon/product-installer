import Container from "./Container"
import Operation, {OperationConfig} from "./Operation";

/**
 * Console class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class Console extends Container
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
    private readonly operations: Operation[] = []

    /**
     * Creates a console instance.
     */
    constructor()
    {
        Console.consoleId++;

        // Create container
        super('console' + Console.consoleId)

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
            const op = new Operation(operation);
            op.appendTo(this.template)

            this.operations.push(op)
        }
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
