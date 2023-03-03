import Modal from "./Components/Modal";
import {setLanguage} from "./Language/";
import State from "./State";

import LicenseConnectorStep from "./Steps/LicenseConnectorStep";
import DashboardStep from "./Steps/DashboardStep";

export default class Installer
{
    public static locale: string
    private readonly modal: Modal

    constructor(locale: string)
    {
        // Restore state
        State.init()

        // Set current locale
        this.setLocale(locale)

        // Create modal and steps
        this.modal = new Modal('installer')
        this.modal.addSteps(
            new DashboardStep(),
            new LicenseConnectorStep()
        )

        this.modal.appendTo('body')
    }

    /**
     * Set current language
     *
     * @param locale
     */
    setLocale(locale: string): void
    {
        Installer.locale = locale
        setLanguage(locale)
    }

    /**
     * Open Installer
     */
    open(): void
    {
        this.modal.open()
    }
}
