<?php

namespace Oveleon\ProductInstaller\Setup;

use Contao\Model;
use Oveleon\ProductInstaller\Import\FileImport;
use Oveleon\ProductInstaller\Import\ImportStateType;
use Oveleon\ProductInstaller\Import\Prompt\FormPrompt;
use Oveleon\ProductInstaller\Import\Prompt\FormPromptType;
use Oveleon\ProductInstaller\Import\Prompt\ImportPromptType;
use Oveleon\ProductInstaller\Import\Prompt\ConfirmPrompt;
use Oveleon\ProductInstaller\Import\Prompt\PromptResponse;
use Oveleon\ProductInstaller\Import\TableImport;
use Oveleon\ProductInstaller\InstallerLock;
use Oveleon\ProductInstaller\SetupLock;
use Oveleon\ProductInstaller\Util\ArchiveUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Content package setup class.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class ContentPackageSetup
{
    /**
     * Create the content package setup class.
     */
    public function __construct(
        protected readonly ArchiveUtil $archiveUtil,
        protected readonly TableImport $tableImporter,
        protected readonly FileImport $fileImporter,
        protected readonly SetupLock $setupLock,
        protected readonly InstallerLock $installerLock,
        protected readonly TranslatorInterface $translator,
    ){}

    /**
     * Run the setup.
     */
    public function run($task, PromptResponse $promptResponse): JsonResponse
    {
        // Catch possible errors and check if the task destination exists
        if(!$destination = $task['destination'])
        {
            return new JsonResponse([
                'error'   => true,
                'message' => $this->translator->trans('setup.error.fileNotFound', [], 'setup')
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
                            ->question($this->translator->trans('setup.prompt.running.question', [], 'setup'))
                            ->answer($this->translator->trans('setup.prompt.running.answerFalse', [], 'setup'), 0)
                            ->answer($this->translator->trans('setup.prompt.running.answerTrue', [], 'setup'),1)
                            ->getResponse();
            }
        }

        // Set scope
        $this->setupLock->setScope($task['hash']);
        $this->tableImporter->setScope($task['hash']);

        // Set archive destination to all importer
        $this->tableImporter->setArchive($destination);
        $this->fileImporter->setArchive($destination);

        // Set file extension
        $this->tableImporter->setFileExtension('table');
        $this->fileImporter->setFileExtension('json');

        // Get valid table
        $tableStructure = $this->getTables($destination);

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
                    // Set default value to true so that file tables can be deselected at any time
                    $hasRows = true;

                    // Get the model of the table to check if records are present
                    if(!\in_array($table, $this->getFileTables()))
                    {
                        /** @var Model $tableModel */
                        $tableModel = $this->tableImporter->getClassFromFileName($table);
                        $hasRows = $tableModel::countAll() > 0;
                    }

                    $values[] = [
                        'name'  => $table,
                        'value' => 1,
                        'text'  => $this->translator->trans('setup.tables.' . $table, [], 'setup'),
                        'options' => [
                            'checked' => true,
                            'disabled' => !$hasRows,
                            'description' => !$hasRows ? $this->translator->trans('setup.global.systemNoContent', [], 'setup') : null
                        ]
                    ];
                }

                return (new FormPrompt('setupConfig'))
                    ->field('tables', $values, FormPromptType::CHECKBOX, ['checked' => true, 'multiple' => true, 'checkAll' => true])
                    ->getResponse();
            }
        }

        // Set prompt response to all importer
        $this->tableImporter->setPromptResponse($promptResponse);
        $this->fileImporter->setPromptResponse($promptResponse);

        // Use default validators
        $this->tableImporter->useDefaultValidators();

        $skipTables = [];

        // Get selected tables to import
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

            // Check for non-database-assisted tables
            if(\in_array($tableName, $this->getFileTables()))
            {
                if(($prompt = $this->fileImporter->importDirectoriesByManifest('content.manifest', ['files'])) !== null)
                {
                    // Extend prompt response data
                    $prompt->setCustomResponseData([
                       'progress' => [
                           'list'    => array_combine(
                               $tablesToImport = array_diff($tableStructure, $skipTables),
                               array_map(
                                   fn($table): string => $this->translator->trans('setup.tables.' . $table, [], 'setup'),
                                   $tablesToImport
                               )
                           ),
                           'current' => $this->tableImporter->getTable()
                       ]
                    ]);

                    // Import need user input
                    return $prompt->getResponse();
                }

                // Set table state
                $this->setupLock->set($tableName, ImportStateType::FINISH->value);
                $this->setupLock->save();

                continue;
            }

            // Get table content or skip if empty
            if(!$tableContent = $this->tableImporter->getArchiveContentByFilename($tableName))
            {
                continue;
            }

            // Start the import and expect a prompt
            if(($prompt = $this->tableImporter->import($tableName, $tableContent)) !== null)
            {
                // Extend prompt response data
                $prompt->setCustomResponseData([
                   'progress' => [
                       'list' => array_combine(
                           $tablesToImport = array_diff($tableStructure, $skipTables),
                           array_map(
                               fn($table): string => $this->translator->trans('setup.tables.' . $table, [], 'setup'),
                               $tablesToImport
                           )
                       ),
                       'current' => $this->tableImporter->getTable()
                   ]
                ]);

                // Import need user input
                return $prompt->getResponse();
            }
        }

        // Get full setup log
        $log = $this->setupLock->getScope($task['hash']);

        // Remove scope to reset the setup for this task
        $this->setupLock->removeScope($task['hash']);
        $this->setupLock->save();

        // Update product in installer-lock
        if($product = $this->installerLock->getProduct($task['productHash']))
        {
            $product['setup'] = true;

            $this->installerLock->setProduct($product);
            $this->installerLock->save();
        }

        return new JsonResponse([
            'log'      => $log,
            'complete' => 1
        ]);
    }

    /**
     * Returns the table structure to import.
     */
    public function getTables(string $archiveDestination): array
    {
        // Fixme: Get structure from config yml
        // Get predefined table structure and order
        $tableOrder = [
            'tl_files',
            'tl_theme',
            'tl_page',
            'tl_module',
            'tl_layout',
            'tl_style_sheet',
            'tl_style',
            'tl_image_size',
            'tl_image_size_item',
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
            'tl_article',
            'tl_content.tl_article',
            'tl_content.tl_news',
            'tl_content.tl_calendar_events'
        ];

        $tableOrder = array_merge(
            $this->getFileTables(),
            $tableOrder
        );

        // Get file extension
        $fileExtension = '.' . $this->tableImporter->getFileExtension();

        // Get table structure by archive and remove file extension
        $archiveTables = array_map(
            fn($table): string => str_replace($fileExtension, '', $table),
            $this->archiveUtil->getFileList($archiveDestination, $fileExtension)
        );

        // Retrieve tables that are not in the configuration but are in the archive in order to attach them afterward
        $archiveTablesOnTop = array_diff($archiveTables, $tableOrder);

        if($diffTables = array_diff($tableOrder, $archiveTables))
        {
            // Clean up order structure (Consider only tables that exist in the archive or are of type file table)
            foreach ($diffTables as $removeTable)
            {
                if(\in_array($removeTable, $this->getFileTables()))
                {
                    continue;
                }

                if($index = \array_search($removeTable, $tableOrder))
                {
                    unset($tableOrder[$index]);
                }
            }
        }

        // Append unknown tables and return full structure
        return array_merge($tableOrder, $archiveTablesOnTop);
    }

    public function getFileTables(): array
    {
        return [
            'tl_templates'
        ];
    }
}
