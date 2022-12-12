import {TaskConfig} from "./Product/Product";

/**
 * Composer configuration.
 */
export interface ComposerConfig extends TaskConfig {
    require?: [],
    update?: [],
    remove?: [],
    uploads?: boolean,
    pkey?: string
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
    private token: string

    /**
     * Start the composer process
     *
     * @param tasks
     */
    public start(tasks: ComposerConfig[]): void
    {

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
