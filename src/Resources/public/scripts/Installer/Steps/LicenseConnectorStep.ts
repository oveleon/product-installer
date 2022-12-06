import {i18n} from "../Language/"
import {call} from "../../Utils/network"
import Step, {StepConfig} from "../Components/Step";
import {createInstance} from "../Utils/InstanceUtils";

/**
 * License connector configuration.
 */
export interface LicenseConnectorConfig {
    config: {
        title: string,
        description: string,
        image: string
    }
    steps: StepConfig[]
}

/**
 * License connector step class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class LicenseConnectorStep extends Step
{
    /**
     * @inheritDoc
     */
    protected getTemplate(): string
    {
        return `
            <h2>${i18n('license_connector.headline')}</h2>
            <form id="license-connector-form" autocomplete="off">
                <div class="license-conntector-container"></div>
            </form>
        `
    }

    /**
     * @inheritDoc
     */
    protected events(): void
    {
        // Show loader
        this.modal.loader(true, i18n('license_connector.load.connector'))

        // Get license connectors
        call("/contao/installer/license_connectors", {}, true).then((response) => {
            // Hide loader
            this.modal.loader(false)

            // Check errors
            if(response.error)
            {
                super.error(response)
                return
            }

            // Skip step if only one license connector is active
            if(response.license_connectors.length === 1)
            {
                this.useLicenseConnector(response.license_connectors[0])
                return
            }

            for (const connector of response.license_connectors)
            {
                this.createLicenseConnectorElement(connector)
            }

        }).catch(() => {
            // ToDo: Error
            console.log('error catch')
        })
    }

    /**
     * Set license connector to use
     *
     * @param config
     *
     * @private
     */
    private useLicenseConnector(config: LicenseConnectorConfig): void
    {
        this.modal.loader(true, i18n('license_connector.load.steps'))

        // Get steps by string
        for (const step of config.steps)
        {
            // Create instance by string
            const instance = createInstance(step.name)

            // Set step config
            instance.setConfig(step)

            // Add step to modal
            this.modal.addSteps(instance)
        }

        // Hide loader
        this.modal.loader(false)

        // Goto next step
        this.modal.next()
    }

    /**
     * Create a single license connector element
     *
     * @param config
     *
     * @private
     */
    private createLicenseConnectorElement(config: LicenseConnectorConfig): void
    {
        const image = config.config.image ? `<img src="${config.config.image}" alt="${config.config.title}"/>` : ''
        const template = <HTMLDivElement> document.createElement('div')
              template.classList.add('license-connector')

        // Add click event
        template.addEventListener('click', (e: MouseEvent) => this.useLicenseConnector(config))

        // Create content
        template.innerHTML = `
            <div class="image">${image}</div>
            <div class="content">
                <div class="title">${config.config.title}</div>
                <div class="description">${config.config.description}</div>
            </div>
        `

        // Append to container
        this.template.querySelector('.license-connector-container').append(template)
    }
}
