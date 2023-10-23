import ModalComponent from "./Components/ModalComponent";
import {setLanguage} from "./Language/";
import State from "./State";

import LicenseConnectorStep from "./Steps/LicenseConnectorStep";
import DashboardStep from "./Steps/DashboardStep";

/**
 * Installer class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
export default class Installer
{
    public static locale: string
    public static modal: ModalComponent

    constructor(locale: string)
    {
        // Restore state
        State.init()

        // Set current locale
        this.setLocale(locale)

        // Create modal and steps
        Installer.modal = new ModalComponent('installer')
        Installer.modal.addSteps(
            new DashboardStep(),
            new LicenseConnectorStep()
        )

        Installer.modal.appendTo('body')
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
     * Set color scheme.
     *
     * @param scheme
     */
    static setColorScheme(scheme?: string)
    {
        if(!scheme)
        {
            // Detect contao scheme
            scheme = document.documentElement.dataset.colorScheme ?? 'light'
        }

        Installer.modal.template.dataset.colorScheme = scheme
    }

    /**
     * Open Installer
     */
    open(): void
    {
        Installer.setColorScheme()
        Installer.modal.open()
    }
}
