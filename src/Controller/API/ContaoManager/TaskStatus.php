<?php

namespace Oveleon\ProductInstaller\Controller\API\ContaoManager;

enum TaskStatus: string
{
    case ALREADY_RUNNING = 'already_running';
    case NOT_AVAILABLE   = 'not_available';
}
