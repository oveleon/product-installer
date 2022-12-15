import {ProductConfig, Provider, TaskConfig, TaskType} from "./Product/Product";
import ProductManager from "./Product/ProductManager";

/**
 * Composer configuration.
 */
export interface ComposerConfig extends TaskConfig {
    provider?: Provider,
    require?: [],
    composer?: [],
    update?: [],
    remove?: [],
    uploads?: boolean,
    pkey?: string
}
/**
 * Manager package configuration.
 */
export interface PackageConfig extends TaskConfig {
    provider?: Provider,
    source?: string,
    token?: string
}

/**
 * Task response configuration.
 */
export interface TaskResponse {
    id: string,
    title: string,
    console: string,
    cancellable: boolean,
    autoclose: boolean,
    audit: boolean,
    status: TaskStatus,
    operations: [{}],
    sponsor: {}
}

/**
 * Task status.
 */
export enum TaskStatus {
    ACTIVE = 'active',
    COMPLETE = 'complete',
    ERROR = 'error',
    ABORTING = 'aborting',
    STOPPED = 'stopped'
}

/**
 * ContaoManager class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ContaoManager
{
    /**
     * Start the composer process
     *
     * @param tasks
     */
    public start(tasks: ComposerConfig[]): void
    {

    }

    /**
     * Return composer tasks.
     *
     * @param products
     */
    public getComposerTasksByProducts(products: ProductConfig[]): ComposerConfig[]
    {
        return (new ProductManager(products)).getTasksByType(TaskType.COMPOSER_UPDATE)
    }

    /**
     * Return package tasks.
     *
     * @param products
     */
    public getPackageTasksByProducts(products: ProductConfig[]): PackageConfig[]
    {
        return (new ProductManager(products)).getTasksByType(TaskType.MANAGER_PACKAGE)
    }

    /**
     * Check if tasks for the contao manager exists
     *
     * @param products
     * @param type
     */
    public hasTasks(products: ProductConfig[], type?: TaskType): boolean
    {
        const composerTasks = this.getComposerTasksByProducts(products)
        const packageTasks = this.getPackageTasksByProducts(products)

        switch (type)
        {
            case TaskType.MANAGER_PACKAGE:
                return packageTasks.length > 0

            case TaskType.COMPOSER_UPDATE:
                return composerTasks.length > 0

            default:
                return composerTasks.length > 0 || packageTasks.length > 0
        }
    }

    /**
     * Returns all private keys of every task
     *
     * @param tasks
     */
    public getPrivateKeyByTasks(tasks: ComposerConfig[]): string[]
    {
        const keys = []

        for (const task of tasks)
        {
            if(task.pkey)
                keys.push(task.pkey)
        }

        return keys
    }

    /**
     * Summarize tasks and return them as a single object.
     *
     * @param tasks
     */
    public summarizeComposerTasks(tasks: ComposerConfig[]): ComposerConfig
    {
        let collection: ComposerConfig

        for (const task of tasks)
        {
            if(typeof collection === 'undefined')
            {
                collection = task
                continue
            }

            collection.require = [...collection.require, ...task.require]
            collection.update = [...collection.update, ...task.update]
        }

        return collection
    }
}
