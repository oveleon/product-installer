import StepComponent from "./StepComponent"
import ContainerComponent from "./ContainerComponent"
import LoaderComponent, {LoaderMode} from "./LoaderComponent";

/**
 * Modal class - A modal to go through different steps.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class ModalComponent extends ContainerComponent
{
    /**
     * The current step.
     *
     * @private
     */
    private currentStep: StepComponent

    /**
     * The current step index.
     *
     * @private
     */
    public currentIndex: number

    /**
     * Indicates from which direction the user is coming (Next = 1, Prev = 0)
     */
    public lastDirection: number = 1

    /**
     * The inside container of the modal.
     *
     * @private
     */
    public readonly insideContainer: HTMLDivElement

    /**
     * The scroll container of the modal.
     *
     * @private
     */
    private readonly scrollContainer: HTMLDivElement

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
    private readonly loaderElement: LoaderComponent

    /**
     * Collection of the steps to be displayed.
     *
     * @private
     */
    private steps: StepComponent[] = []

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

        // Create scroll container
        this.scrollContainer = <HTMLDivElement> document.createElement('div')
        this.scrollContainer.classList.add('scrollable')

        this.insideContainer.append(this.scrollContainer)

        // Create step container
        this.stepContainer = <HTMLDivElement> document.createElement('div')
        this.stepContainer.id = 'steps'

        this.scrollContainer.append(this.stepContainer)

        // Create notification container
        this.notificationContainer = <HTMLDivElement> document.createElement('div')
        this.notificationContainer.classList.add('notifications')

        this.insideContainer.append(this.notificationContainer)

        // Create loader
        this.loaderElement = new LoaderComponent()
        this.loaderElement.setMode(LoaderMode.COVER)
        this.loaderElement.appendTo(this.insideContainer)
    }

    /**
     * Adds one or more steps.
     *
     * @param step
     */
    public addSteps(...step: StepComponent[]): void
    {
        for (const s of step)
        {
            this.steps.push(s)

            s.appendTo(this.stepContainer)
            s.addModal(this)
        }
    }

    /**
     * Removes the given step.
     *
     * @param step
     */
    public removeStep(step: StepComponent): void
    {
        this.steps = this.steps.filter((_step) => step !== _step)
    }

    /**
     * Removes all steps (keep locked steps).
     */
    public removeSteps(): void
    {
        for(let i = this.steps.length - 1; i >= 0; i--)
        {
            if(!this.isLocked(i))
            {
                // Call the step method remove to unload events e.g.
                // The step method calls the removeStep-method from modal.
                this.steps[i].remove()
            }
        }
    }

    /**
     * Returns the step index by string or StepComponent.
     *
     * @param step
     */
    public getStepIndex(step: string|StepComponent): number
    {
        for (const index in this.steps)
        {
            if(!this.steps.hasOwnProperty(index))
            {
                continue
            }

            let _step: StepComponent|string = this.steps[index]

            if(typeof step === 'string')
            {
                _step = this.steps[index].constructor.name
            }

            if(step === _step)
            {
                return parseInt(index)
            }
        }

        return 0
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
        this.lastDirection = 1

        this.open(++this.currentIndex)
    }

    /**
     * Goes to the previous step.
     */
    public prev(): void
    {
        this.lastDirection = 0

        // Close when it is already the first step
        if(this.currentIndex === 0)
        {
            this.hide();
            return
        }

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
     * Check if a step is locked.
     *
     * @param index
     * @private
     */
    private isLocked(index): boolean
    {
        return this.steps[ index ].locked
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
