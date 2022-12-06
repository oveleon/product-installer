import Modal from "./Components/Modal";
import {setLanguage} from "./Language/";
import LicenseConnectorStep from "./Steps/LicenseConnectorStep";

export default class Installer
{
    private locale: string
    private readonly modal: Modal

    constructor(locale: string)
    {
        // Set current locale
        this.setLocale(locale)

        // Create modal and steps
        this.modal = new Modal('installer')
        this.modal.addSteps(
            new LicenseConnectorStep()
        )

        this.modal.appendTo('body')

        // Check trigger events
        this.handleTrigger()
    }

    /**
     * Set current language
     *
     * @param locale
     */
    setLocale(locale: string): void
    {
        this.locale = locale
        setLanguage(locale)
    }

    /**
     * Open Installer
     */
    open(): void
    {
        this.modal.open()
    }

    /**
     * Respond to external triggers
     */
    handleTrigger(): void
    {
        debugger

        const params = new URLSearchParams(window.location.search)
        const jumpTo = params.get('installer')

        if(jumpTo)
        {
            // ToDo: Handle license connectors and load steps before jump
            //this.modal.open(parseInt(jumpTo))
        }
    }
}
