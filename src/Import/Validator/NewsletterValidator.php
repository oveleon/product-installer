<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\Controller;
use Contao\NewsArchiveModel;
use Contao\NewsletterChannelModel;
use Contao\NewsletterModel;
use Contao\NewsModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

/**
 * Validator class for validating the newsletter records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class NewsletterValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return NewsletterModel::getTable();
    }

    /**
     * Deals with the relationship with the parent element.
     */
    static function setChannelConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        $translator = Controller::getContainer()->get('translator');

        $newsArchiveStructure = $importer->getArchiveContentByFilename(NewsletterChannelModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, NewsletterModel::getTable(), NewsletterChannelModel::getTable(), [
            'label'       => $translator->trans('setup.prompt.newsletter.channel.label', [], 'setup'),
            'description' => $translator->trans('setup.prompt.newsletter.channel.description', [], 'setup'),
            'explanation' => [
                'type'        => 'TABLE',
                'description' => $translator->trans('setup.prompt.newsletter.channel.explanation', [], 'setup'),
                'content'     => $newsArchiveStructure ?? []
            ]
        ]);
    }
}
