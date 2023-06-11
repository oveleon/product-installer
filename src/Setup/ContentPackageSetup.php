<?php

namespace Oveleon\ProductInstaller\Setup;

use Contao\Model;
use Oveleon\ProductInstaller\Import\ImportStateType;
use Oveleon\ProductInstaller\Import\Prompt\FormPrompt;
use Oveleon\ProductInstaller\Import\Prompt\FormPromptType;
use Oveleon\ProductInstaller\Import\Prompt\ImportPromptType;
use Oveleon\ProductInstaller\Import\Prompt\ConfirmPrompt;
use Oveleon\ProductInstaller\Import\Prompt\PromptResponse;
use Oveleon\ProductInstaller\Import\TableImport;
use Oveleon\ProductInstaller\SetupLock;
use Oveleon\ProductInstaller\Util\ArchiveUtil;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Content package setup initiator.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class ContentPackageSetup
{
    const TABLE_FILE_EXTENSION = '.table'; // Fixme: Move to TableImport::class

    public function __construct(
        protected readonly ArchiveUtil $archiveUtil,
        protected readonly TableImport $tableImporter,
        protected readonly SetupLock $setupLock
    ){}

    public function run($task, PromptResponse $promptResponse): JsonResponse
    {
        // Catch possible errors and check if the task destination exists
        if(!$destination = $task['destination'])
        {
            return new JsonResponse([
                'error'   => true,
                'message' => 'Zieldatei konnte nicht gefunden werden.'
            ]);
        }

        // Before we start importing tables, we need to check if there is already a setup in progress
        if(
            $promptResponse->has('checkRunningSetup') ||
            ($blnAnswered = (
                $promptResponse->get('name') === 'runningSetup' &&
                $promptResponse->get('type') === ImportPromptType::CONFIRM->value
            ))
        ){
            // We have received a response on whether to continue the setup or start from scratch
            if($blnAnswered ?? false)
            {
                // Restart: result = 0
                if((int) $promptResponse->get('result') === 0)
                {
                    // Delete setup config
                    $this->setupLock->removeScope($task['hash']);
                    $this->setupLock->save();
                }
            }
            // We have noticed that there is still an ongoing setup in progress
            elseif($this->setupLock->getScope($task['hash']))
            {
                // Return a confirm prompt
                return (new ConfirmPrompt('runningSetup'))
                            ->question('Wir haben festgestellt, dass die vorherige Einrichtung nicht vollständig abgeschlossen wurde. Möchten Sie mit der Einrichtung fortfahren oder erneut starten?')
                            ->answer('Einrichtung neu starten', 0)
                            ->answer('Einrichtung fortsetzen',1)
                            ->getResponse();
            }
        }

        // Set scope
        $this->setupLock->setScope($task['hash']);
        $this->tableImporter->setScope($task['hash']);

        // Get table structure
        $tableStructure = $this->getTableStructure($destination);

        // Initial 'expert' prompt (choose tables to import)
        if(
            $task['expert'] &&
            (($blnConfig = (
                $promptResponse->get('name') === 'setupConfig' &&
                $promptResponse->get('type') === ImportPromptType::FORM->value
            )) ||
            !$this->setupLock->get('config'))
        )
        {
            if($blnConfig ?? false)
            {
                $fields = $promptResponse->get('result');
                $fields = array_keys(array_filter($fields['tables']));

                $this->setupLock->set('config', ['tables' => $fields]);
                $this->setupLock->save();
            }
            elseif(!$this->setupLock->get('config'))
            {
                $values = [];

                foreach ($tableStructure as $table)
                {
                    /** @var Model $tableModel */
                    $tableModel = Model::getClassFromTable($table);
                    $hasRows = $tableModel::countAll() > 0;

                    $values[] = [
                        'name'  => $table,
                        'value' => 1,
                        'text'  => $table, // ToDo: Translate e.g. tl_page -> Seitenstruktur
                        'options' => [
                            'checked' => true,
                            'disabled' => !$hasRows,
                            'description' => !$hasRows ? 'Fehlende Datensätze im System' : null
                        ]
                    ];
                }

                return (new FormPrompt('setupConfig'))
                    ->field('tables', $values, FormPromptType::CHECKBOX, ['checked' => true, 'multiple' => true, 'checkAll' => true])
                    ->getResponse();
            }
        }

        // Set prompt response (importer)
        $this->tableImporter->setPromptResponse($promptResponse);

        // Set archive
        $this->tableImporter->setArchive($destination);

        // Get selected tables to import
        $skipTables = [];

        if($config = $this->setupLock->get('config'))
        {
            if($tables = ($config['tables'] ?? false))
            {
                $skipTables = array_diff($tableStructure, $tables);
            }
        }

        // Check wich tables already imported and add them to the skip tables array
        if($scope = $this->setupLock->getScope($task['hash']))
        {
            // Get keys where the value is set to ImportStateType::FINISH
            $skipTables = $skipTables + (array_keys($scope, ImportStateType::FINISH->value) ?? []);
        }

        // Running through the tables in the correct order
        foreach ($tableStructure ?? [] as $tableName)
        {
            // Skip table if needed
            if(\in_array($tableName, $skipTables))
            {
                continue;
            }

            // Get table content or skip if empty
            if(!$tableContent = $this->archiveUtil->getFileContent($destination, $tableName . self::TABLE_FILE_EXTENSION, true))
            {
                continue;
            }

            // Start the import and expect a prompt
            if(($prompt = $this->tableImporter->import($tableName, $tableContent)) !== null)
            {
                // Import need user input
                return $prompt->getResponse();
            }
        }

        // Remove scope to reset the setup for this task
        $this->setupLock->removeScope($task['hash']);
        $this->setupLock->save();

        // ToDo: Update product in installer-lock; Set setup -> true

        return new JsonResponse([
            'complete' => 1
        ]);
    }

    public function getTableStructure(string $archiveDestination): array
    {
        // ToDo: Get structure from config yml
        // ToDo: Check if e.g. news bundle is installed

        // Get table structure by config
        $tableOrder = [
            'tl_theme',
            'tl_style_sheet',
            'tl_style',
            'tl_image_size',
            'tl_image_size_item',
            'tl_module',
            'tl_layout',
            'tl_user_group',
            'tl_member_group',
            'tl_faq_category',
            'tl_faq',
            'tl_news_archive',
            'tl_news_feed',
            'tl_news',
            'tl_calendar',
            'tl_calendar_feed',
            'tl_calendar_events',
            'tl_comments',
            'tl_comments_notify',
            'tl_newsletter_channel',
            'tl_newsletter_deny_list',
            'tl_newsletter',
            'tl_newsletter_recipients',
            'tl_form',
            'tl_form_field',
            'tl_page',
            'tl_article',
            'tl_content.tl_article',
            'tl_content.tl_news',
            'tl_content.tl_calendar_events'
        ];

        // Get table structure by archive and remove file extension
        $archiveTables = array_map(
            fn($table): string => str_replace(self::TABLE_FILE_EXTENSION, '', $table),
            $this->archiveUtil->getFileList($archiveDestination, self::TABLE_FILE_EXTENSION)
        );

        // Retrieve tables that are not in the configuration but are in the archive in order to attach them afterward
        $archiveTablesOnTop = array_diff($archiveTables, $tableOrder);

        if($diffTables = array_diff($tableOrder, $archiveTables))
        {
            // Clean up order structure (Consider only tables that are available in the archive)
            foreach ($diffTables as $removeTable)
            {
                if($index = array_search($removeTable, $tableOrder))
                {
                    unset($tableOrder[$index]);
                }
            }
        }

        // Append unknown tables and return full structure
        return array_merge($tableOrder, $archiveTablesOnTop);
    }

    /**
     * Returns the model based on a filename with table name verification.
     */
    /*protected function getClassFromFileName(string $filename): string
    {
        return Model::getClassFromTable($this->getTableFromFileName($filename));
    }*/

    /**
     * Returns the table based on a table with table name verification.
     */
    /*protected function getTableFromFileName(string $filename): string
    {
        return strtok($filename, '.');
    }*/

    /**
     * Overwrites connected ids in a content element.
     */
    /*private function overwriteContentElement($model): void
    {
        switch($model->type)
        {
            case 'article':
                $model->articleAlias = $this->connections['tl_article'][ $model->articleAlias ] ?? 0;
                break;

            case 'form':
                $model->form = $this->connections['tl_form'][ $model->form ] ?? 0;
                break;

            case 'module':
                $model->module = $this->connections['tl_module'][ $model->module ] ?? 0;
                break;

            case 'teaser':
                $model->article = $this->connections['tl_article'][ $model->article ] ?? 0;
                break;
        }
    }*/

    /**
     * Overwrites the connection from one content element to another (Include: Content Element).
     */
    /*private function overwriteAliasContentElement(?array $contentIds): void
    {
        if(null === $contentIds)
        {
            return;
        }

        if($models = ContentModel::findBy(["type=? AND id IN ('" . implode("', '", array_values($contentIds)) . "')"], ['alias']))
        {
            foreach ($models as $model)
            {
                $model->cteAlias = $contentIds[ $model->cteAlias ];
            }
        }
    }*/
}
