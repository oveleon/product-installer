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
        public config: ConfirmPromptConfig
    ){
        super('confirm_prompt');

        // Set content
        this.setContent()
    }

    setContent(): void
    {
        this.content(`
            <p class="question">
                ${this.config.question}
            </p>
            <div class="actions"></div>
        `)

        for(const answer of this.config.answers)
        {
            this.element('.actions').appendChild(this.createAnswer(answer))
        }
    }

    createAnswer(answer): HTMLButtonElement
    {
        const [label, value] = answer;

        const button = <HTMLButtonElement> document.createElement('button')

        button.innerHTML = label
        button.value = value

        // Resolve
        if(parseInt(value))
        {
            button.classList.add('primary')
        }

        button.addEventListener('click', () => {
            this.resolve(value)
        })

        return button
    }
}
