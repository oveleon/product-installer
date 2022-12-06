import * as handlers from "./DynamicInstances"

/**
 * Creates instance by a string.
 *
 * This is a helper method to load classes dynamically based on the string.
 * Should this possibility no longer exist (for whatever reason), classes must be imported and resolved manually.
 *
 * @param className
 * @param args
 */
export function createInstance(className: string, ...args: any[]) {
    return new (<any>handlers)[className](...args);
}
