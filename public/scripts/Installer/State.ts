export default class State
{
    /**
     * The key used for the localStorage object.
     *
     * @private
     */
    private static key = 'product_installer'

    /**
     * The state object.
     *
     * @private
     */
    private static state = {}

    /**
     * Object for properties that are retained even after cleanup.
     *
     * @private
     */
    private static persistKeys = []

    /**
     * Sets a value.
     *
     * @param name
     * @param value
     * @param persists
     */
    public static set(name: string, value: any, persists: boolean = false): void
    {
        this.state[name] = value

        if(persists)
        {
            this.persistKeys.push(name)
        }

        this.save()
    }

    /**
     * Returns the state value by name.
     *
     * @param name
     */
    public static get(name?: string): any
    {
        let state = localStorage.getItem(this.key)

        if(state)
        {
            state = JSON.parse(state)

            if(!name)
            {
                return state
            }

            if(state.hasOwnProperty(name))
            {
                return state[name]
            }
        }

        return null
    }

    /**
     * Clears one or the entire state.
     *
     * @param name
     */
    public static clear(name?: string): void
    {
        if(name)
        {
            delete this.state[name]
            this.save()
            return
        }

        const persistentState = {};

        if(this.persistKeys.length)
        {
            for(const key of this.persistKeys)
            {
                persistentState[key] = this.state[key]
            }
        }

        this.state = persistentState

        this.save()
    }

    /**
     * Saves the state object to the localStorage.
     *
     * @private
     */
    private static save(): void
    {
        localStorage.setItem(this.key, JSON.stringify(this.state))
    }

    /**
     * Restore sate.
     *
     * @private
     */
    public static init(): void
    {
        let state = localStorage.getItem(this.key)

        if(state)
        {
            this.state = JSON.parse(state)
        }
    }
}
