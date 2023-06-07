<?php

namespace Oveleon\ProductInstaller\Import;

use Contao\Controller;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Model;
use Contao\PageModel;
use Oveleon\ProductInstaller\Import\Prompt\AbstractPrompt;
use Oveleon\ProductInstaller\Import\Prompt\FormPrompt;

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
        $this->table = $tableName;
        $this->content = $tableContent;

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

        // Consider only data container of type DC_Table
        if(DC_Table::class === $tableInfo->dataContainer)
        {
            $this->importTable();

            /*// Determine the type of the import
            if(DataContainer::MODE_TREE === $info['sortingMode'])
            {
                $this->importTreeTable();
            }*/
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

    public function getFlash(): array
    {
        return $this->flashConnections;
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
     * Returns information about the specified table or null if no information can be determined.
     */
    public function getTableInformation(): ?\stdClass
    {
        // Load data container for the current table
        Controller::loadDataContainer($this->table);

        if(!isset($GLOBALS['TL_DCA'][$this->table]['config']))
        {
            return null;
        }

        $info = new \stdClass();
        $conf = $GLOBALS['TL_DCA'][$this->table]['config'];
        $list = $GLOBALS['TL_DCA'][$this->table]['list'];

        $info->sortingMode = $list['sorting']['mode'] ?? null;
        $info->dataContainer = $conf['dataContainer'] ?? null;
        $info->ctable = $conf['ctable'] ?? null;
        $info->dynamicPtable = $conf['dynamicPtable'] ?? null;
        $info->ptable = $conf['ptable'] ?? ($info->sortingMode === DataContainer::MODE_TREE ? $this->table : null);

        $info->hasParent = $info->ptable || $info->dynamicPtable || $info->sortingMode === DataContainer::MODE_TREE;

        return $info;
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
        if(!$modelClass = Model::getClassFromTable($this->table))
        {
            // ToDo: CancelPrompt
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

        $modelClass = Model::getClassFromTable($this->table);
        $tableInfo = $this->getTableInformation();
        $hasParent = $tableInfo->hasParent;

        // ToDo: sql autocommit false?

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

            // Check for parent-connections
            if($hasParent && !$isRoot)
            {
                $parentId = $this->getConnection($row['pid'], $tableInfo->ptable);

                $model->pid = $parentId;
            }

            // Add connection
            $this->addConnection($exportId, ($model->save())->id);
        }

        // ToDo: sql commit? Then we're able to check if something gone wrong?

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
}
