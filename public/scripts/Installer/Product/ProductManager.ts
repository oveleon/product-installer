import Product, {ProductConfig, TaskConfig, TaskType} from "./Product";
import {ComposerConfig} from "../ContaoManager";

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
     *
     * @param types
     */
    public getTasksByType(...types: TaskType[]): TaskConfig[]
    {
        let tasks = []

       for (const product of this.products)
        {
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
