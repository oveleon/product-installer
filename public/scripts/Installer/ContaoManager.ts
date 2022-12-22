import {ProductConfig, Provider, TaskConfig, TaskType} from "./Product/Product";
import ProductManager from "./Product/ProductManager";

/**
 * Composer configuration.
 */
export interface ComposerConfig extends TaskConfig {
    provider?: Provider,
    require?: {},
    composer?: [],
    update?: string[],
    remove?: string[],
    uploads?: boolean
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
    STOPPED = 'stopped',
    PENDING = 'pending',
    ALREADY_RUNNING = 'already_running',
    NOT_AVAILABLE   = 'not_available'
}

/**
 * ContaoManager class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ContaoManager
{
    /**
     * Collection of composer tasks
     *
     * @protected
     */
    protected composerTasks: ComposerConfig[] = []

    /**
     * Adds a composer tasks
     *
     * @param tasks
     */
    public addComposerTasks(tasks: ComposerConfig[]): void
    {
        this.composerTasks = [...this.composerTasks, ...tasks]
    }

    /**
     * Return the composer tasks collection.
     */
    public getComposerTasks(): ComposerConfig[]
    {
        return this.composerTasks
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
     * Check if tasks for the contao manager exists.
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
     * Create a requirement object and return it
     *
     * @param name
     * @param version
     */
    public createRequirementObject(name: string, version: string): {}
    {
        let require: {} = {};

        require[name] = version

        return require
    }
}
