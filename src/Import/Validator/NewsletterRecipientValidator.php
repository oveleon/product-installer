<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\Controller;
use Contao\NewsletterChannelModel;
use Contao\NewsletterRecipientsModel;
use Oveleon\ProductInstaller\Import\TableImport;

/**
 * Validator class for validating the newsletter recipient records during and after import.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class NewsletterRecipientValidator implements ValidatorInterface
{
    static public function getTrigger(): string
    {
        return NewsletterRecipientsModel::getTable();
    }

    static public function getModel(): string
    {
        return NewsletterRecipientsModel::class;
    }

    /**
     * Handles the relationship with the parent element.
     */
    static function setChannelConnection(array &$row, TableImport $importer): ?array
    {
        $translator = Controller::getContainer()->get('translator');

        $newsArchiveStructure = $importer->getArchiveContentByFilename(NewsletterChannelModel::getTable(), [
            'value' => $row['pid'],
            'field' => 'id'
        ]);

        return $importer->useParentConnectionLogic($row, NewsletterRecipientsModel::getTable(), NewsletterChannelModel::getTable(), [
            'label'       => $translator->trans('setup.prompt.newsletter.recipient_channel.label', [], 'setup'),
            'description' => $translator->trans('setup.prompt.newsletter.recipient_channel.description', [], 'setup'),
            'explanation' => [
                'type'        => 'TABLE',
                'description' => $translator->trans('setup.prompt.newsletter.recipient_channel.explanation', [], 'setup'),
                'content'     => $newsArchiveStructure ?? []
            ]
        ]);
    }
}
