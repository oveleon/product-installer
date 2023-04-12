import Product, {ProductConfig, TaskConfig, TaskType} from "./Product";

/**
 * ProductManager class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ProductManager
{
    public readonly products: Product[] = []

    /**
     * Creates a new product manager instance.
     */
    constructor(products?: ProductConfig[])
    {
        for(const config of products)
        {
            this.products.push(new Product(config))
        }
    }

    /**
     * Returns product tasks of given types.
     * It is taken into account whether the products should be skipped.
     *
     * @param types
     */
    public getTasksByType(...types: TaskType[]): TaskConfig[]
    {
        let tasks = []

       for (const product of this.products)
        {
            if(product.get('skip') === true)
            {
                continue
            }

            for (const task of product.getTasks())
            {
                if(types.includes(task.type))
                {
                    tasks.push(task)
                }
            }
        }

        return tasks
    }
}
