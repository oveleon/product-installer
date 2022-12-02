import Step, {StepConfig} from "../components/Step"
import {i18n} from "../lang/"
import {call} from "../../Utils/network"
import {routes} from "../Installer";

export interface LicenserConfig {
    config: {
        title: string,
        description: string,
        image: string
    }
    steps: StepConfig[]
}

export default class LicenserStep extends Step
{
    /**
     * @inheritDoc
     */
    getTemplate(): string
    {
        return `
            <h2>${i18n('licenser.headline')}</h2>
            <form id="licenser-form" autocomplete="off">
                <div class="licenser-container"></div>
            </form>
        `
    }

    /**
     * @inheritDoc
     */
    protected events(): void
    {
        // Show loader
        this.modal.loader(true, i18n('licenser.load.licenser'))

        // Get licensers
        call(routes.licenser, {}, true).then((response) => {
            // Hide loader
            this.modal.loader(false)

            // Check errors
            if(response.error)
            {
                super.error(response)
                return
            }

            // Skip step if only one licenser active
            if(response.licensers.length === 1)
            {
                this.onChooseLicenser(response.licensers[0])
                return
            }

            for (const licenser of response.licensers)
            {
                this.createLicenserElement(licenser)
            }

        }).catch(() => {
            // ToDo: Error
            console.log('error catch')
        })
    }

    /**
     * On click method
     *
     * @param config
     */
    onChooseLicenser(config: LicenserConfig): void
    {
        this.modal.loader(true, i18n('licenser.load.steps'))
        this.modal.addStepsByString(config.steps)
    }

    /**
     * Create a single licenser element
     *
     * @param config
     */
    createLicenserElement(config: LicenserConfig): void
    {
        const image = config.config.image ? `<img src="${config.config.image}" alt="${config.config.title}"/>` : ''
        const template = <HTMLDivElement> document.createElement('div')
              template.classList.add('licenser')

        // Add click event
        template.addEventListener('click', (e: MouseEvent) => this.onChooseLicenser(config))

        // Create content
        template.innerHTML = `
            <div class="image">${image}</div>
            <div class="content">
                <div class="title">${config.config.title}</div>
                <div class="description">${config.config.description}</div>
            </div>
        `

        // Append to container
        this.template.querySelector('.licenser-container').append(template)
    }
}
