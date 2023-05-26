import Prompt from "./Prompt";

/**
 * Prompt configurations.
 */
export interface ConfirmPromptConfig {
    question: string,
    answers: [[string, string]]
}

/**
 * Confirm prompt class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ConfirmPrompt extends Prompt
{
    constructor(
        protected config: ConfirmPromptConfig
    ){
        super('confirm_prompt');
    }

    getTemplate(): string {

        let buttons = '';

        for(const answer of this.config.answers)
        {

        }

        return `
            <div class="question">
                ${this.config.question}
            </div>
            <div class="actions">
                
            </div>
        `;
    }

    createAnswer(answer): HTMLButtonElement
    {
        const [label, value] = answer;

        const button = <HTMLButtonElement> document.createElement('button')

        button.innerHTML = label
        button.value = value

        return button
    }
}
