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
    /**
     * @inheritDoc
     */
    protected getTemplate(): string
    {
        return `
            <h2>${i18n('setup.prompt.headline')}</h2>
            <div class="prompts inherit"></div>
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
}
