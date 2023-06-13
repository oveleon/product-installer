<?php

namespace Oveleon\ProductInstaller\Import\Validator;

use Contao\Controller;
use Contao\NewsArchiveModel;
use Contao\NewsletterChannelModel;
use Contao\NewsletterRecipientsModel;
use Contao\NewsModel;
use Oveleon\ProductInstaller\Import\AbstractPromptImport;

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

    /**
     * Deals with the relationship with the parent element.
     */
    static function setChannelConnection(array &$row, AbstractPromptImport $importer): ?array
    {
        $translator = Controller::getContainer()->get('translator');

        $newsArchiveStructure = $importer->getArchiveContentByTable(NewsletterChannelModel::getTable(), [
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
            ],
            'class'       => 'w50'
        ]);
    }
}
