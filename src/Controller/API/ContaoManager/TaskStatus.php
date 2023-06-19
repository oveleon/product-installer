<?php

namespace Oveleon\ProductInstaller\Controller\API\ContaoManager;

/**
 * Defines the status of a contao manager task.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
enum TaskStatus: string
{
    case ALREADY_RUNNING = 'already_running';
    case NOT_AVAILABLE   = 'not_available';
}
