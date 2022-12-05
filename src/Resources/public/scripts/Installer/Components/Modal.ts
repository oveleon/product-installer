import Step from "./Step"
import Container from "./Container"
import Loader, {LoaderMode} from "./Loader";

/**
 * Modal class - A modal to go through different steps.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class Modal extends Container
{
    private currentStep: Step
    private currentIndex: number

    private readonly insideContainer: HTMLDivElement
    private readonly stepContainer: HTMLDivElement
    private readonly loaderElement: Loader
    private steps: Step[] = []

    /**
     * Creates a new modal instance.
     */
    constructor(id: string) {
        super(id)

        // Hide modal by default
        this.hide()

        // Create inside container
        this.insideContainer = <HTMLDivElement> document.createElement('div')
        this.insideContainer.classList.add('inside')

        this.template.append(this.insideContainer)

        // Create step container
        this.stepContainer = <HTMLDivElement> document.createElement('div')
        this.stepContainer.id = 'steps'

        this.insideContainer.append(this.stepContainer)

        // Create loader
        this.loaderElement = new Loader()
        this.loaderElement.setMode(LoaderMode.COVER)
        this.loaderElement.appendTo(this.insideContainer)
    }

    /**
     * Adds one or more steps.
     *
     * @param step
     */
    public addSteps(...step: Step[]): void
    {
        for (const s of step)
        {
            s.addModal(this)

            this.steps.push(s)

            s.appendTo(this.stepContainer)
        }
    }

    /**
     * Opens the modal window and initializes the passed step index.
     *
     * @param startIndex
     */
    public open(startIndex: number = 0): void
    {
        this.currentIndex = startIndex
        this.currentStep = this.steps[ this.currentIndex ]

        // Close other
        this.closeSteps()

        // Show current step
        this.currentStep.show()

        // Show modal
        this.show()
    }

    /**
     * Shows or hides the modal loader.
     *
     * @param state
     * @param text
     */
    public loader(state: boolean = true, text?: string): void
    {
        state ?
            this.loaderElement.show() :
            this.loaderElement.hide()

        text ?
            this.loaderElement.setText(text) :
            this.loaderElement.setText('')
    }

    /**
     * Goes to the next step.
     */
    public next(): void
    {
        this.open(++this.currentIndex)
    }

    /**
     * Goes to the previous step.
     */
    public prev(): void
    {
        this.open(--this.currentIndex)
    }

    /**
     * Hides all steps.
     */
    public closeSteps(): void
    {
        for (const step of this.steps)
        {
            step.hide()
        }
    }
}
