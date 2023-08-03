/**
 * Providers.
 */
export enum Provider {
    GITHUB  = 'github',
    GITLAB  = 'gitlab',
    SERVER  = 'server',
    SHOP    = 'shop',
}

/**
 * Task types.
 */
export enum TaskType {
    REPOSITORY_IMPORT = 'repo_import',
    CONTENT_PACKAGE = 'content_package',
    MANAGER_PACKAGE = 'manager_package',
    COMPOSER_UPDATE = 'composer_update'
}

/**
 * Global task configuration.
 */
export interface TaskConfig {
    hash: string,
    type: TaskType
}

/**
 * Product configuration.
 */
export interface ProductConfig {
    name: string,
    type: string,
    hash: string,
    version: string,
    image: string,
    skip?: boolean,
    description: string,
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

    /**
     * Returns product tasks of given types.
     *
     * @param types
     */
    public getTasksByType(...types: TaskType[]): TaskConfig[]
    {
        let tasks = []

        for (const task of this.getTasks())
        {
            if(types.includes(task.type))
            {
                tasks.push(task)
            }
        }

        return tasks
    }
}
