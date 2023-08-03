import Process from "./Process"
import {call} from "../../Utils/network"
import {i18n} from "../Language"
import State from "../State";
import {TaskType} from "../Product/Product";
import ProductManager from "../Product/ProductManager";

/**
 * Download process class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class DownloadProcess extends Process
{
    /**
     * @inheritDoc
     */
    protected getTemplate(): string {
        return `
            <div data-loader></div>
            <div class="content">
                <div class="title">${this.getAttribute('title', i18n('process.download.title'))}</div>
                <p>${this.getAttribute('description', i18n('process.download.description'))}</p>
            </div>
        `;
    }

    /**
     * @inheritDoc
     */
    protected process(): void
    {
        const config = State.get('config')

        // Get products
        const products = config.products

        // Create product manager class to handle tasks
        const productManager = new ProductManager(products)

        // Get download tasks
        const downloadTasks = productManager.getTasksByType(
            TaskType.MANAGER_PACKAGE,
            TaskType.REPOSITORY_IMPORT,
            TaskType.CONTENT_PACKAGE
        )

        if(!downloadTasks.length)
        {
            this.resolve({})
        }

        call('/contao/api/content/download', {
            tasks: downloadTasks,
            license: State.get('license'),
        }).then((response) => {
            // Check errors
            if(response.error)
            {
                this.reject(response)
                return
            }

            /**
             * Todo:
             * Special attention should be paid to tasks of type CONTENT_PACKAGE.
             * These could contain further tasks, which must also be attached to the product.
             *
             * Possible implementation sites:
             * - This file
             * - DownloadController
             */

            // Replacing the new tasks objects
            for(const productIndex in products)
            {
                if (!products.hasOwnProperty(productIndex)) {
                    continue
                }

                const product = products[productIndex]

                // Run through the product tasks
                for(const productTaskIndex in product.tasks)
                {
                    if (!product.tasks.hasOwnProperty(productTaskIndex)) {
                        continue
                    }

                    const productTask = product.tasks[productTaskIndex]

                    // Run through the response tasks
                    for(const task of response)
                    {
                        if(task.hash === productTask.hash)
                        {
                            products[productIndex].tasks[productTaskIndex] = task
                        }
                    }
                }
            }

            // Overwrite products
            config.products = products

            // Save config
            State.set('config', config)

            this.resolve(response)
        }).catch((e: Error) => this.reject(e))
    }
}
