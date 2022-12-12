/**
 * Task types.
 */
export enum TaskType {
    GITHUB_IMPORT    = 'github:import',
    COMPOSER_UPDATE  = 'composer:update'
}

/**
 * Global task configuration.
 */
export interface TaskConfig {
    type: TaskType
}

/**
 * Product configuration.
 */
export interface ProductConfig {
    name: string,
    version: string,
    image: string,
    description: string,
    registrable: boolean,
    repository: string,
    tasks: TaskConfig[]
}

/**
 * Product class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class Product
{
    /**
     * Creates a new product instance.
     */
    constructor(
        public readonly productConfig: ProductConfig
    ){}

    /**
     * Returns a config attribute by name.
     *
     * @param attr
     * @param fallback
     */
    public get(attr: string, fallback?: any): any
    {
        if(!this.productConfig[attr])
        {
            return fallback ? fallback : ''
        }

        return this.productConfig[attr]
    }

    /**
     * Return the product tasks
     */
    public getTasks(): TaskConfig[]
    {
        return this.get('tasks', [])
    }
}
