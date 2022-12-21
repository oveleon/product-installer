import Process, {ProcessErrorResponse} from "./Process"
import {i18n} from "../Language"
import State from "../State";
import ContaoManager, {ComposerConfig} from "../ContaoManager";
import {TaskType} from "../Product/Product";
import ProcessManager from "./ProcessManager";
import ApiProcess from "./ApiProcess";
import ComposerProcess from "./ComposerProcess";

/**
 * Manager processes.
 */
enum ManagerProcess {
    DOWNLOAD_PROCESS = 'downloadProcess',
    PACKAGE_PROCESS = 'packageProcess',
    COMPOSER_PROCESS = 'composerProcess'
}

/**
 * Contao manager process class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ContaoManagerProcess extends Process
{
    /**
     * Process manager instance.
     *
     * @private
     */
    private processManager: ProcessManager

    /**
     * Contao manager instance.
     *
     * @private
     */
    private contaoManager: ContaoManager

    /**
     * @inheritDoc
     */
    protected getTemplate(): string
    {
        return `
            <div data-loader></div>
            <div class="content">
                <div class="title">${this.getAttribute('title', i18n('contao_manager.process.title'))}</div>
                <p>${this.getAttribute('description', i18n('contao_manager.process.description'))}</p>
                <div class="manager-tasks subtasks"></div>
            </div>
        `;
    }

    /**
     * @inheritDoc
     */
    protected mount(): void
    {
        // Skip mount
        if(!State.get('useManager') || State.get('installManually'))
        {
            return;
        }

        // Create contao manager class to handle tasks
        this.contaoManager = new ContaoManager()

        // Create sub process manager
        this.processManager = new ProcessManager()

        // Create sub processes
        this.createProcesses()

        // Register on reject method
        this.processManager.onReject((err: Error | ProcessErrorResponse) => {
            this.reject(err)
        })

        // Register on finish to exit sub processes
        this.processManager.onFinish((response) => {
            this.resolve(response)
        })
    }

    /**
     * @inheritDoc
     */
    protected process(): void
    {
        // Skip process
        if(!State.get('useManager') || State.get('installManually'))
        {
            this.resolve({})
            return
        }

        this.processManager.start()
    }

    private createProcesses(): void
    {
        // Get products from state
        const products = State.get('config').products

        // Check if tasks of type manager package exists
        if(this.contaoManager.hasTasks(products, TaskType.MANAGER_PACKAGE))
        {
            // Add download process
            this.processManager.addProcess(new ApiProcess(this.element('.manager-tasks'), {
                name: ManagerProcess.DOWNLOAD_PROCESS,
                routes: {
                    'api': '/contao/api/content/download'
                },
                attributes: {
                    title: i18n('process.contao_manager.download.title'),
                    description: i18n('process.contao_manager.download.description')
                },
                onResolve: (response) => {
                    State.set(ManagerProcess.DOWNLOAD_PROCESS, response.map((taskWithDestination) => taskWithDestination.destination))
                },
                parameter: this.contaoManager.getPackageTasksByProducts(products)
            }))

            // Add package process to install downloaded files
            this.processManager.addProcess(new ApiProcess(this.element('.manager-tasks'), {
                name: ManagerProcess.PACKAGE_PROCESS,
                routes: {
                    'api': '/contao/api/contao_manager/package/install'
                },
                attributes: {
                    title: i18n('process.contao_manager.package.title'),
                    description: i18n('process.contao_manager.package.description')
                },
                onResolve: (response) => {
                    // Create a tasks from response
                    const tasks: ComposerConfig[] = []

                    for (let key in response.collection)
                    {
                        tasks.push({
                            type: TaskType.MANAGER_PACKAGE,
                            uploads: true,
                            require: this.contaoManager.createRequirementObject(response.collection[key].package.name, response.collection[key].package.version),
                            update: [response.collection[key].package.name]
                        })
                    }

                    // Add tasks
                    this.contaoManager.addComposerTasks(tasks)
                },
                parameter: () => {
                    return State.get(ManagerProcess.DOWNLOAD_PROCESS)
                }
            }))
        }

        // Check if tasks of type composer update exists
        if(this.contaoManager.hasTasks(products, TaskType.COMPOSER_UPDATE))
        {
            // Get all tasks of type composer
            const composerTasks = this.contaoManager.getComposerTasksByProducts(products)

            // Add them to the task collection
            this.contaoManager.addComposerTasks(composerTasks)
        }

        // Add a Composer update process that runs only when tasks exist
        this.processManager.addProcess(new ComposerProcess(this.element('.manager-tasks'), {
            name: ManagerProcess.COMPOSER_PROCESS,
            routes: {
                'start': '/contao/api/contao_manager/task/set',
                'update': '/contao/api/contao_manager/task/get'
            },
            attributes: {
                title: i18n('process.contao_manager.composer.title'),
                description: i18n('process.contao_manager.composer.description')
            },
            parameter: () => this.contaoManager.getComposerTasks()
        }))

        // Todo: Check database and migrate
    }
}
