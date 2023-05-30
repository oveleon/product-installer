import StepComponent from "../Components/StepComponent";
import {i18n} from "../Language"
import {call} from "../../Utils/network"
import State from "../State";
import {PromptType} from "../Prompt/Prompt";
import ConfirmPrompt from "../Prompt/ConfirmPrompt";

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
            <div class="prompts"></div>
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

            switch(response.type)
            {
                case PromptType.CONFIRM:
                    // Create confirm prompt
                    const confirm: ConfirmPrompt = new ConfirmPrompt(response.data);

                    // Append prompt
                    confirm.appendTo(this.element('.prompts'))

                    // Set resolve method
                    confirm.onResolve((value) => this.run({
                        promptResponse: {
                            ...response,
                            ...{
                                answer: value
                            }
                        }
                    }))

                    break;
            }

            console.log(response);

        }).catch((e: Error) => super.error(e))
    }
}
