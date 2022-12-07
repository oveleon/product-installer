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
     * Sets a value.
     *
     * @param name
     * @param value
     */
    public static set(name: string, value: any): void
    {
        this.state[name] = value
        this.save()
    }

    /**
     * Returns the state value by name.
     *
     * @param name
     */
    public static get(name): any
    {
        let state = localStorage.getItem(this.key)

        if(state)
        {
            state = JSON.parse(state)
            return state[name]
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

        this.state = {}
        localStorage.removeItem(this.key);
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
}
