import {i18n} from "../Language/"
import {call} from "../../Utils/network"
import StepComponent, {StepConfig} from "../Components/StepComponent";
import {createInstance} from "../Utils/InstanceUtils";
import State from "../State";

/**
 * License connector configuration.
 */
export interface LicenseConnectorConfig {
    config: {
        name: string,
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
export default class LicenseConnectorStep extends StepComponent
{
    /**
     * Defines the connector name to use, if set
     *
     * @private
     */
    private connector: string | null

    /**
     * Defines the step to be redirected, if set
     *
     * @private
     */
    private redirect: number | null

    /**
     * @inheritDoc
     */
    protected getTemplate(): string
    {
        return `
            <h2>${i18n('license_connector.headline')}</h2>
            <form id="license-connector-form" autocomplete="off">
                <div class="license-connector-container"></div>
            </form>
            <div class="actions">
                <button data-prev>${i18n('actions.back')}</button>
            </div>
        `
    }

    /**
     * @inheritDoc
     */
    protected mount(): void
    {
        // Lock step and protect for deletion
        this.locked = true

        // Handle url trigger
        const params = new URLSearchParams(window.location.search)

        const installer = params.get('installer')
        const startAt = params.get('start')

        if(!installer)
        {
            return
        }

        // Set connector name
        this.connector = installer

        // Set redirect
        if(startAt)
        {
            // Set state
            State.set('isRedirect', true)

            // Set redirection
            this.redirect = parseInt(startAt)

            // Open modal instantly
            this.modal.open()

            // Remove get parameters
            window.history.pushState({}, document.title, window.location.pathname);
        }
    }

    /**
     * @inheritDoc
     */
    protected events(): void
    {
        // Reset / remove steps from modal
        this.modal.removeSteps()

        // Show loader
        this.modal.loader(true, this.redirect ? i18n('license_connector.load.redirect') : i18n('license_connector.load.connector'))

        // Get license connectors
        call("/contao/api/license_connector/config", {}, true).then((response) => {
            // Hide loader
            this.modal.loader(false)

            // Check errors
            if(response.error)
            {
                super.error(response)
                return
            }

            // Check if a direct call to a step is set
            if(this.connector && this.redirect)
            {
                for (const connector of response.license_connectors)
                {
                    if(connector.config.name === this.connector)
                    {
                        this.useLicenseConnector(connector, this.redirect)

                        // Reset
                        this.connector = null
                        this.redirect = null
                        State.clear('isRedirect')

                        return
                    }
                }

                throw new Error('The license connector to be used cannot be found')
            }

            // Skip step if only one license connector is active
            if(response.license_connectors.length === 1)
            {
                // Check if a step has been taken backwards to skip this step backwards as well
                if(!this.modal.lastDirection)
                {
                    this.modal.prev()
                    return
                }

                this.useLicenseConnector(response.license_connectors[0])
                return
            }

            for (const connector of response.license_connectors)
            {
                this.createLicenseConnectorElement(connector)
            }

        }).catch((e: Error) => super.error(e))
    }

    /**
     * Set license connector to use
     *
     * @param config
     * @param startAt
     *
     * @private
     */
    private useLicenseConnector(config: LicenseConnectorConfig, startAt?: number): void
    {
        // Show loader
        this.modal.loader(true, i18n('license_connector.load.steps'))

        // Set connector information
        State.set('connector', config.config.name)

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
        if(startAt)
        {
            this.modal.open(startAt)
        }
        else
        {
            this.modal.next()
        }
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
            <div class="inside">
                <div class="image">${image}</div>
                <div class="content">
                    <div class="title">${config.config.title}</div>
                    <div class="description">${config.config.description}</div>
                </div>
            </div>
        `

        // Append to container
        this.element('.license-connector-container').append(template)
    }
}
