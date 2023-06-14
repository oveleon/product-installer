<?php

namespace Oveleon\ProductInstaller\Import;

use Contao\Controller;
use Contao\DataContainer;
use Contao\DC_Folder;
use Contao\DC_Table;
use Contao\Model;

use Oveleon\ProductInstaller\Import\Prompt\AbstractPrompt;
use Oveleon\ProductInstaller\Import\Prompt\FormPrompt;
use Oveleon\ProductInstaller\Import\Prompt\FormPromptType;
use Oveleon\ProductInstaller\Import\Validator\ValidatorMode;

/**
 * Import class to import table records.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class TableImport extends AbstractPromptImport
{
    /**
     * Defines the table of the currently handled table.
     */
    protected ?string $table = null;

    /**
     * Defines the content of the currently handled table.
     */
    protected ?array $content = null;

    /**
     * Temporary connections which exist only during runtime
     */
    protected array $flashConnections = [];

    /**
     * Starts importing the tables and returns prompts.
     * If no prompt is needed, null is returned.
     */
    public function import(string $tableName, array $tableContent): ?AbstractPrompt
    {
        // Set class variables
        $this->table = $tableName;
        $this->content = $tableContent;

        // Check import state
        switch($this->getState())
        {
            case ImportStateType::INIT->value:

                // Empty state for sending prompts when a table is initiated for the first time

            case ImportStateType::PROMPT->value:

                // Check if the PromptResponse belongs to an import and save it for further processing
                if(
                    ($response = $this->getPromptResponse()) &&
                    $response->get('name') === $this->table
                ){
                    $this->addPromptValue($response->get('result'));
                }

            case ImportStateType::IMPORT->value:

                $this->start();
        }

        return $this->prompt ?? null;
    }

    /**
     * Starts the import.
     */
    protected function start(): void
    {
        $tableInfo = $this->getTableInformation();

        // Consider only data container of type DC_Table and DC_Folder
        if(
             DC_Table::class === $tableInfo->dataContainer ||
            (DC_Folder::class === $tableInfo->dataContainer && $tableInfo->databaseAssisted)
        )
        {
            $this->importTable();
        }
    }

    /**
     * Adds a temporary connection.
     */
    public function addFlashConnection(string|int $a, string|int $b, string $scope): void
    {
        if(!\array_key_exists($scope, $this->flashConnections))
        {
            $this->flashConnections[$scope] = [];
        }

        $this->flashConnections[$scope][$a] = $b;
    }

    /**
     * Get a temporary connection.
     */
    public function getFlashConnection(string|int $a, string $scope): null|string|int
    {
        return $this->flashConnections[$scope][$a] ?? null;
    }

    /**
     * Adds a connection between two records and make them available for further processing.
     */
    public function addConnection(string|int $a, string|int $b, ?string $table = null): void
    {
        $table = $table ?? $this->table;

        if(!$connections = $this->setupLock->get('connections'))
        {
            $connections = [];
        }

        if(\array_key_exists($table, $connections))
        {
            $connections[$table] = $connections[$table] + [$a => $b];
        }else{
            $connections[$table] = [$a => $b];
        }

        $this->setupLock->set('connections', $connections);
        $this->setupLock->save();
    }

    /**
     * Returns the value of a mapped field.
     */
    public function getConnection(string|int $a, ?string $table = null): null|string|int
    {
        $connectedValue = null;
        $table = $table ?? $this->table;

        if($connections = $this->setupLock->get('connections'))
        {
            $connectedValue = $connections[ $table ][ $a ] ?? null;
        }

        return $connectedValue;
    }

    /**
     * Adds results that was retrieved by a prompt based on current table.
     */
    protected function addPromptValue(array $result): void
    {
        if(!$prompts = $this->setupLock->get('prompts'))
        {
            $prompts = [];
        }

        if(\array_key_exists($this->table, $prompts))
        {
            $prompts[$this->table] = $result + $prompts[$this->table];
        }else{
            $prompts[$this->table] = $result;
        }

        $this->setupLock->set('prompts', $prompts);
        $this->setupLock->save();
    }

    /**
     * Returns the value of a field that was retrieved by a prompt based on current table.
     */
    public function getPromptValue(string $fieldName): ?string
    {
        $mappedValue = null;

        if($prompts = $this->setupLock->get('prompts'))
        {
            $mappedValue = $prompts[ $this->table ][ $fieldName ] ?? null;
        }

        return $mappedValue;
    }

    /**
     * Sets the state of the current table.
     */
    protected function setState(ImportStateType $state): void
    {
        $this->setupLock->set($this->table, $state->value);
        $this->setupLock->save();
    }

    /**
     * Returns the current state of the table.
     */
    protected function getState(): string
    {
        // Return init state if the table was not found
        if(!$tableMode = $this->setupLock->get($this->table))
        {
            return ImportStateType::INIT->value;
        }

        // Skip the table if it set to finish
        if($tableMode === ImportStateType::FINISH->value)
        {
            return ImportStateType::SKIP->value;
        }

        // Return state
        return $tableMode;
    }

    /**
     * Returns the current table.
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Returns information about the specified table or null if no information can be determined.
     */
    public function getTableInformation(): ?\stdClass
    {
        $table = $this->getTableFromFileName($this->table);

        // Load data container for the current table
        Controller::loadDataContainer($table);

        if(!isset($GLOBALS['TL_DCA'][$table]['config']))
        {
            return null;
        }

        $info = new \stdClass();
        $conf = $GLOBALS['TL_DCA'][$table]['config'];
        $list = $GLOBALS['TL_DCA'][$table]['list'];

        $info->sortingMode = $list['sorting']['mode'] ?? null;
        $info->dataContainer = $conf['dataContainer'] ?? null;
        $info->databaseAssisted = $conf['databaseAssisted'] ?? null;
        $info->ctable = $conf['ctable'] ?? null;
        $info->dynamicPtable = $conf['dynamicPtable'] ?? null;
        $info->ptable = $conf['ptable'] ?? ($info->sortingMode === DataContainer::MODE_TREE ? $this->table : null);

        $info->hasParent = $info->ptable || $info->dynamicPtable;

        return $info;
    }

    /**
     * Apply default table validators.
     */
    public static function useDefaultValidators(): void
    {
        // Apply default validators
        Validator::useDefaultTableValidators();
    }

    /**
     * Checks the table to be imported considering various rules and returns the import as a valid record.
     */
    protected function validate(): ?array
    {
        // Form field collection for the prompt
        $fields = [];

        // Get content (make copy)
        $content = $this->content;

        // Get model class by table
        if(!$modelClass = $this->getClassFromFileName($this->table))
        {
            // ToDo: CancelPrompt / SkipPrompt (e.g. news-bundle is not installed -> no model -> skip)
        }

        foreach ($content as &$row)
        {
            foreach(Validator::getValidators($this->table) ?? [] as $validator)
            {
                if($validatorFields = call_user_func_array($validator, [&$row, $this]))
                {
                    $fields = $fields + $validatorFields;
                }
            }
        }

        if(!empty($fields))
        {
            // Create form prompt and fields
            $prompt = new FormPrompt($this->table);

            foreach ($fields as $name => $opt)
            {
                $prompt->field(
                    $name,
                    ($opt[0] ?? []),
                    ($opt[1] ?? []),
                    ($opt[2] ?? [])
                );
            }

            // Set table state
            $this->setState(ImportStateType::PROMPT);

            // Set prompt
            $this->setPrompt($prompt);

            return null;
        }

        // Set table state
        $this->setState(ImportStateType::IMPORT);

        return $content;
    }

    /**
     * Import table.
     */
    protected function importTable(): void
    {
        if(!$validatedRows = $this->validate())
        {
            return;
        }

        $modelCollection = [];
        $modelClass = $this->getClassFromFileName($this->table);
        $tableInfo = $this->getTableInformation();
        $hasParent = $tableInfo->hasParent;

        foreach ($validatedRows as $row)
        {
            if(\array_key_exists('_skip', $row))
            {
                continue;
            }

            // Get vars before clean up the row
            $exportId = $row['id'];
            $isRoot = $this->isRootRecord($row);

            // Clean up row
            $this->removeUnnecessaryFields($row);

            // Create model and set data
            $model = new $modelClass();
            $model->setRow($row);

            // Add model to collection for validators in mode AFTER_IMPORT
            $modelCollection[] = $model;

            // Check for parent-connections
            if($hasParent && !$isRoot)
            {
                $parentTable = $tableInfo->ptable;

                if($tableInfo?->dynamicPtable)
                {
                    $parentTable = $row['ptable'];
                }

                $parentId = $this->getConnection($row['pid'], $parentTable);

                $model->pid = $parentId;
            }

            // Add connection
             $this->addConnection($exportId, ($model->save())->id);
        }

        if($validators = Validator::getValidators($this->table, ValidatorMode::AFTER_IMPORT))
        {
            foreach ($modelCollection as $model)
            {
                foreach($validators as $validator)
                {
                    call_user_func_array($validator, [$model, $this]);
                }
            }
        }

        $this->setState(ImportStateType::FINISH);
    }

    /**
     * Returns if a row is a root record.
     */
    protected function isRootRecord(array $row): bool
    {
        // Unknown tables or vendor validators must define their root pages as such if they do not have type=root
        // like tl_pages, otherwise we only check for the passed type
        if(
             \array_key_exists('_root', $row) ||
            (\array_key_exists('type', $row) && $row['type'] === 'root')
        ){
            return true;
        }

        return false;
    }

    /**
     * Removed unnecessary fields from row.
     */
    protected function removeUnnecessaryFields(array &$row): void
    {
        unset(
            $row['id'],
            $row['_create'],
            $row['_root']
        );
    }

    /**
     * Returns the model based on a filename with table name verification.
     */
    public static function getClassFromFileName(string $filename): string
    {
        return Model::getClassFromTable( self::getTableFromFileName($filename) );
    }

    /**
     * Returns the base table of the given filename.
     */
    public static function getTableFromFileName(string $filename): string
    {
        return strtok($filename, '.');
    }

    /**
     * Create new connections between two tables and handle prompts, should they be necessary.
     */
    public function useParentConnectionLogic(array $row, string $tableA, string $tableB, array $promptOptions, ?array $selectableValues = null, string $aField = 'id', string $bField = 'pid'): ?array
    {
        $parentId = $row[$bField];
        $id       = $row[$aField];

        /** @var Model $aModel */
        $aModel   = $this->getClassFromFileName($tableA);

        /** @var Model $bModel */
        $bModel   = $this->getClassFromFileName($tableB);

        $aTable   = $aModel::getTable();
        $bTable   = $bModel::getTable();

        // Skip if we find a connection
        if($this->getConnection($parentId, $bTable))
        {
            return null;
        }

        $connectorName = $bTable . '_' .  $aTable . '_connection';
        $fieldName = $tableA . '_connection_' . $id;
        $skip = [];

        // Check if we got a prompt response and should skip prompts of the same ID
        if($this->getFlashConnection($parentId, $connectorName))
        {
            $skip[] = $parentId;
        }

        // Check if we have already received a user decision
        if($connectedId = (int) $this->getPromptValue($fieldName))
        {
            // Add id connection for child row
            $this->addConnection($parentId, $connectedId, $bTable);
        }
        else
        {
            if(\in_array($parentId, $skip))
            {
                return null;
            }

            // Add a flash connection to display prompts for the same connections only once
            $this->addFlashConnection($parentId, $id, $connectorName);

            $values = $selectableValues ?? [];

            if($selectableValues === null)
            {
                if($records = $bModel::findAll())
                {
                    foreach ($records as $record)
                    {
                        $values[] = [
                            'value' => $record->id,
                            'text'  => $record->name ?: ($record->title ?: $record->headline),
                            'info'  => $record->id
                        ];
                    }
                }
            }

            return [
                $fieldName => [
                    $values ?? [],
                    FormPromptType::SELECT,
                    $promptOptions
                ]
            ];
        }

        return null;
    }
}
