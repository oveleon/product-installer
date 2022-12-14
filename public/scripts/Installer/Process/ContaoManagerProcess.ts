import Process, {ProcessErrorResponse} from "./Process"
import {i18n} from "../Language"
import State from "../State";
import ContaoManager from "../ContaoManager";
import {TaskType} from "../Product/Product";
import ProcessManager from "./ProcessManager";
import ApiProcess from "./ApiProcess";

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
     * @inheritDoc
     */
    protected getTemplate(): string {
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
        // Skip
        if(!State.get('useManager') || State.get('installManually'))
        {
            return;
        }

        // Get products from state
        const products = State.get('config').products

        // Create contao manager class to handle tasks
        const contaoManager = new ContaoManager()

        // Create sub process manager
        this.processManager = new ProcessManager()

        // Check if tasks of type manager package exists
        if(contaoManager.hasTasks(products, TaskType.MANAGER_PACKAGE))
        {
            this.processManager.addProcess(new ApiProcess(this.element('.manager-tasks'), {
                name: 'packageProcess',
                routes: {
                    'api': '/contao/api/contao_manager/package/install'
                },
                attributes: {
                    title: 'Private Pakete hinterlegen',
                    description: 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa.'
                },
                parameter: contaoManager.getPackageTasksByProducts(products)
            }))
        }

        // Check if tasks of type composer update exists
        if(contaoManager.hasTasks(products, TaskType.COMPOSER_UPDATE))
        {
            const composerTasks = contaoManager.getComposerTasksByProducts(products)
            const task = contaoManager.summarizeComposerTasks(composerTasks)

            this.processManager.addProcess(new ApiProcess(this.element('.manager-tasks'), {
                name: 'composerProcess',
                routes: {
                    'api': '/contao/api/contao_manager/update/task'
                },
                attributes: {
                    title: 'Abhängigkeiten installieren (composer update)',
                    description: 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa.'
                },
                parameter: task
            }))
        }

        // Register on reject method
        this.processManager.onReject((err: Error | ProcessErrorResponse) => {
            this.reject(err)
        })

        // Register on finish to exit sub processes
        this.processManager.onFinish(() => {
            this.resolve()
        })
    }

    /**
     * @inheritDoc
     */
    protected process(): void
    {
        // Skip
        if(!State.get('useManager') || State.get('installManually'))
        {
            this.resolve()
            return
        }

        this.processManager.start()

        // Todo:
        // 1. Install packages
        // 2. Install requirements
        // 3. Check database and migrate
    }
}
