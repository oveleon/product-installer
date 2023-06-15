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
            <h2>${i18n('setup.prompt.headline')}</h2>
            <div class="prompts inherit"></div>
            <div class="step-progress" hidden>
                <div class="progress-scroll"></div>
            </div>
        `
    }

    /**
     * @inheritDoc
     */
    protected events(): void
    {
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
                alert('FERTIG 🎉')

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
                    prompt = new ConfirmPrompt(response.data)
                    break

                case PromptType.FORM:
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

            console.log(response);

        }).catch((e: Error) => super.error(e))
    }

    private updateProgress(progress): void
    {
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
