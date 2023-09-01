import StepComponent from "../Components/StepComponent";
import {i18n} from "../Language"
import {call} from "../../Utils/network"
import State from "../State";
import Prompt, {PromptType} from "../Prompt/Prompt";
import ConfirmPrompt from "../Prompt/ConfirmPrompt";
import FormPrompt from "../Prompt/FormPrompt";

/**
 * Import content package step.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class SetupPromptStep extends StepComponent
{
    private progressItems: HTMLDivElement[] = []

    /**
     * @inheritDoc
     */
    protected getTemplate(): string
    {
        return `
            <div class="setup inherit">
                <h2>${i18n('setup.prompt.headline')}</h2>
                <div class="prompts inherit"></div>
                <div class="step-progress" hidden>
                    <div class="progress-scroll"></div>
                </div>
            </div>
            <div class="complete inherit" hidden>
                <div class="graphic setup-complete"><span>${i18n('setup.complete')}</span></div>
                <div class="actions">
                    <button class="goto-products">${i18n('actions.products')}</button>
                    <button data-close class="primary">${i18n('actions.close')}</button>
                </div>
            </div>
        `
    }

    /**
     * @inheritDoc
     */
    protected events(): void
    {
        window.addEventListener('beforeunload', this.preventUnload);

        this.run({
            promptResponse: {
                checkRunningSetup: true
            }
        })
    }

    private run(parameters: {} = {}): void
    {
        this.modal.loader(true, i18n('setup.loading.step'))

        call('/contao/api/setup/run', {...State.get('setup'), ...parameters}).then((response) => {
            // Hide loader
            this.modal.loader(false)

            // Check errors
            if(response.error)
            {
                super.error(response)
                return
            }

            if(response.complete)
            {
                window.removeEventListener('beforeunload', this.preventUnload);

                this.progressItems = [];

                this.element('.setup').hidden = true
                this.element('.complete').hidden = false
                this.element('.goto-products').addEventListener('click', () => this.modal.open(this.modal.getStepIndex('DashboardStep')))

                return
            }

            if(response.progress)
            {
                this.updateProgress(response.progress)
            }

            let prompt: Prompt

            switch(response.type)
            {
                case PromptType.CONFIRM:
                    // Create confirm prompt
                    prompt = new ConfirmPrompt(response.data)
                    break

                case PromptType.FORM:
                    // Create form prompt
                    prompt = new FormPrompt(response.data)
                    break;

            }

            // Append prompt
            prompt.appendTo(this.element('.prompts'))

            // Set resolve method
            prompt.onResolve((value) => this.run({
                promptResponse: {
                    ...response,
                    ...{
                        result: value
                    }
                }
            }))

        }).catch((e: Error) => super.error(e))
    }

    private preventUnload(e): void
    {
        e.returnValue = `You are about to cancel the setup, do you really want to leave the site?`
    }

    private updateProgress(progress): void
    {
        // Skip process if there is only one table to import
        if(Object.keys(progress.list).length <= 1)
        {
            return
        }

        if(this.progressItems.length === 0)
        {
            // Add items
            for(let key in progress.list)
            {
                if(!progress.list.hasOwnProperty(key))
                {
                    continue
                }

                const item = document.createElement('div')
                const label = progress.list[key]

                item.classList.add('progress-item')
                item.dataset.key = key
                item.innerHTML = `
                    <span class="indicator"></span>
                    <span class="label">${label}</span>
                `

                this.progressItems.push(item)
                this.element('.step-progress .progress-scroll').appendChild(item)
            }
        }

        let passedActive: boolean = false;

        for(const item of this.progressItems)
        {
            if(!passedActive)
            {
                item.classList.add('finish')
                item.classList.remove('pending', 'active')
            }
            else
            {
                item.classList.add('pending')
            }

            if(item.dataset.key === progress.current)
            {
                passedActive = true;

                setTimeout(() => {
                    item.scrollIntoView({
                        behavior: "smooth",
                        block:    "center",
                        inline:   "center"
                    });
                }, 300)

                item.classList.add('active')
                item.classList.remove('pending')
            }
        }

        this.element(".step-progress").hidden = false
    }
}
