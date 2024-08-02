<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->ignoreUnknownClasses([
        'Contao\CalendarEventsModel',
        'Contao\CalendarFeedModel',
        'Contao\CalendarModel',
        'Contao\FaqCategoryModel',
        'Contao\FaqModel',
        'Contao\NewsArchiveModel',
        'Contao\NewsModel',
        'Contao\NewsletterChannelModel',
        'Contao\NewsletterModel',
        'Contao\NewsletterRecipientsModel',
        'closure',
    ])

    ->ignoreErrorsOnPackage('contao/manager-plugin', [ErrorType::DEV_DEPENDENCY_IN_PROD])
;
