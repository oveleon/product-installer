import Process, {ProcessErrorResponse} from "./Process";

export default class ProcessManager
{
    /**
     * Current index of process.
     *
     * @private
     */
    private currentIndex: number

    /**
     * Current process instance.
     *
     * @private
     */
    private currentProcess: Process

    /**
     * All processes to be processed.
     *
     * @private
     */
    private processes: Process[] = []

    /**
     * Method which is called when all processes have been completed.
     */
    private fnFinish: Function = () => {}

    /**
     * Method which is called when one process is rejected.
     */
    private fnReject: Function = () => {}

    /**
     * Method which is called when one process is resolved.
     */
    private fnResolve: Function = () => {}

    /**
     * Adds one or more processes to be queued.
     *
     * @param process
     */
    public addProcess(...process: Process[]): ProcessManager
    {
        for (const proc of process)
        {
            // Bind manager instance
            proc.addManager(this)

            // Add process to queue
            this.processes.push(proc)
        }

        return this
    }

    /**
     * Starts the execution of all processes.
     *
     * @param startIndex
     */
    public start(startIndex: number = 0): void
    {
        if(startIndex >= this.processes.length)
        {
            this.callFinish()
            return
        }

        this.currentIndex = startIndex
        this.currentProcess = this.processes[this.currentIndex]

        this.currentProcess.start()
    }

    /**
     * Call the finish method.
     */
    public callFinish(): void
    {
        this.fnFinish.call(this)
    }

    /**
     * Call the reject method.
     *
     * @param err
     * @param process
     */
    public callReject(process: Process, err: Error | ProcessErrorResponse): void
    {
        this.fnReject.call(this, process, err)
    }

    /**
     * Call the resolve method.
     *
     * @param process
     */
    public callResolve(process: Process): void
    {
        this.fnResolve.call(this, process)
    }

    /**
     * Starts the next process.
     */
    public next(): void
    {
        this.start(++this.currentIndex)
    }

    /**
     * Calling the registered method when all processes are finished.
     *
     * @param fn
     */
    public onFinish(fn: Function): ProcessManager
    {
        this.fnFinish = fn

        return this
    }

    /**
     * Calling the registered resolve method.
     */
    public onResolve(fn: Function): ProcessManager
    {
        this.fnResolve = fn

        return this
    }

    /**
     * Calling the registered reject method.
     */
    public onReject(fn: Function): ProcessManager
    {
        this.fnReject = fn

        return this
    }

    /**
     * Reset manager and all processes.
     */
    public reset(): void
    {
        this.currentIndex = 0

        for (const proc of this.processes)
        {
            proc.reset()
        }
    }
}
