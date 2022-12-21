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
    /**
     * The current step.
     *
     * @private
     */
    private currentStep: Step

    /**
     * The current step index.
     *
     * @private
     */
    public currentIndex: number

    /**
     * The inside container of the modal.
     *
     * @private
     */
    private readonly insideContainer: HTMLDivElement

    /**
     * The container in which the steps are placed.
     *
     * @private
     */
    private readonly stepContainer: HTMLDivElement

    /**
     * The container in which the notification are placed.
     *
     * @private
     */
    public readonly notificationContainer: HTMLDivElement

    /**
     * The Loader Instance for the modal.
     *
     * @private
     */
    private readonly loaderElement: Loader

    /**
     * Collection of the steps to be displayed.
     *
     * @private
     */
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

        // Create notification container
        this.notificationContainer = <HTMLDivElement> document.createElement('div')
        this.notificationContainer.classList.add('notifications')

        this.insideContainer.append(this.notificationContainer)

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
            this.steps.push(s)

            s.appendTo(this.stepContainer)
            s.addModal(this)
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
        const index = --this.currentIndex;

        if(this.isSkip(index))
        {
            this.prev()
            return
        }

        this.open(index)
    }

    /**
     * Check if a step need to be skipped.
     *
     * @param index
     * @private
     */
    private isSkip(index): boolean
    {
        return this.steps[ index ].skip
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
