import Step from "../Components/Step";
import LicenseStep from "../Steps/LicenseStep";
import ProductStep from "../Steps/ProductStep";
import ProcessStep from "../Steps/ProcessStep";
import Process, {ProcessConfig} from "../Process/Process";
import DefaultProcess from "../Process/DefaultProcess";

/**
 * Creates a step instance by a string.
 *
 * @param name
 */
export function getStepInstanceByString(name: string): Step
{
    let instance;

    switch (name)
    {
        case 'LicenseStep':
            instance = new LicenseStep()
            break

        case 'ProductStep':
            instance = new ProductStep()
            break

        case 'ProcessStep':
            instance = new ProcessStep()
            break

        default:
            throw new Error(`Step instance ${name} not exists.`)
    }

    return instance
}

/**
 * Creates a process instance by a string.
 *
 * @param name
 * @param container
 * @param config
 */
export function getProcessInstanceByString(name: string, container: HTMLElement, config: ProcessConfig): Process
{
    let instance;

    switch (name)
    {
        case 'DefaultProcess':
            instance = new DefaultProcess(container, config)
            break

        default:
            throw new Error(`Process instance ${name} not exists.`)
    }

    return instance
}
