<?php

namespace Oveleon\ProductInstaller\Import;

use Contao\Controller;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Model;
use Contao\PageModel;
use Oveleon\ProductInstaller\Import\Prompt\AbstractPrompt;
use Oveleon\ProductInstaller\Import\Prompt\FormPrompt;
use Oveleon\ProductInstaller\Import\Prompt\FormPromptType;

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
                    $response->get('name') === 'importPrompt'
                ){
                    $this->addPromptValue($response->get('result'));
                }

            case ImportStateType::IMPORT->value:

                $this->start();

                // ToDo: Set table state to finished when import was successfully
                //$this->setupLock->set($this->table, ImportStateType::FINISH->value);
                //$this->setupLock->save();
        }

        return $this->prompt ?? null;
    }

    /**
     * Starts the import.
     */
    protected function start(): void
    {
        $info = $this->getTableInformation();

        // Consider only data container of type DC_Table
        if(DC_Table::class === $info['dataContainer'])
        {
            // Determine the type of the import
            if(DataContainer::MODE_TREE === $info['sortingMode'])
            {
                //$this->importTreeTable();
                $this->importTable();
            }
            else
            {
                $this->importTable();
            }
        }
    }

    /**
     * Adds a connection between two records and make them available for further processing.
     */
    protected function addConnection(string|int $a, string|int $b): void
    {
        if(!$connections = $this->setupLock->get('connections'))
        {
            $connections = [];
        }

        if(\array_key_exists($this->table, $connections))
        {
            $connections[$this->table] = array_merge(
                $connections[$this->table],
                [$a => $b]
            );
        }else{
            $connections[$this->table] = [$a => $b];
        }

        $this->setupLock->set('connections', $connections);
        $this->setupLock->save();
    }

    /**
     * Returns the value of a mapped field.
     */
    protected function getConnection(string|int $a): null|string|int
    {
        $connectedValue = null;

        if($connections = $this->setupLock->get('connections'))
        {
            $connectedValue = $connections[ $this->table ][ $a ] ?? null;
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
            $prompts[$this->table] = array_merge(
                $prompts[$this->table],
                $result
            );
        }else{
            $prompts[$this->table] = $result;
        }

        $this->setupLock->set('prompts', $prompts);
        $this->setupLock->save();
    }

    /**
     * Returns the value of a field that was retrieved by a prompt based on current table.
     */
    protected function getPromptValue(string $fieldName): ?string
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
    protected function getTableInformation(): ?array
    {
        // Load data container for the current table
        Controller::loadDataContainer($this->table);

        if(!isset($GLOBALS['TL_DCA'][$this->table]['config']))
        {
            return null;
        }

        return [
            ...array_intersect_key($GLOBALS['TL_DCA'][$this->table]['config'], [
                'dataContainer' => '',
                'ptable'        => '',
                'dynamicPtable' => '',
                'ctable'        => ''
            ]),
            ...[
                'sortingMode'   => $GLOBALS['TL_DCA'][$this->table]['list']['sorting']['mode'] ?? ''
            ]
        ];
    }

    /**
     * Checks the table to be imported considering various rules and returns the import as a valid record.
     */
    protected function validate(): ?array
    {
        // Form field collection for the prompt
        $fields = [];

        // Get content
        $content = $this->content;

        // Get table information
        $info = $this->getTableInformation();

        // Check if the current table is a child table
        $isChildTable = \array_key_exists('ptable', $info);

        // Get model class by table
        if(!$modelClass = Model::getClassFromTable($this->table))
        {
            // ToDo: CancelPrompt
        }

        foreach ($content as &$row)
        {
            switch ($this->table)
            {
                case 'tl_page':

                    // Condition: Check root page
                    if($row['type'] === 'root')
                    {
                        if(null === ($mappedValue = $this->getPromptValue('rootPage')))
                        {
                            $values = [
                                '0' => 'Neue Seite anlegen (' . $row['title'] . ')'
                            ];

                            if($pages = PageModel::findAll())
                            {
                                $values = array_combine(
                                    $pages->fetchEach('id'),
                                    $pages->fetchEach('title')
                                );
                            }

                            $fields['rootPage'] = [
                                $values,
                                FormPromptType::SELECT
                            ];
                        }
                        else
                        {
                            // TODO: !

                            // If another root page was selected, the give root page won't be imported
                            if($mappedValue !== "0")
                            {
                                $row['_skip'] = true;
                            }

                            // Add id connection
                            $this->addConnection($row['id'], $mappedValue);
                        }
                    }
            }
        }

        if(!empty($fields))
        {
            // Create prompt ad create fields
            $prompt = new FormPrompt('importPrompt');

            foreach ($fields as $name => [$options, $type])
            {
                $prompt->field($name, $options, $type);
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

        foreach ($validatedRows as $row)
        {
            if(\array_key_exists('_skip', $row))
            {
                continue;
            }

            // Temporarily store the ID of the row and delete it before assigning it to the new model
            $id = $row['id'];
            unset($row['id']);

            $model = new $modelClass();
            $model->setRow($row);

            /*if($isChildTable)
            {
                $model->pid = $parentIds[$row[self::FOREIGN_KEY]];
            }*/

            /*if($callbacks = $this->getCallbacks(self::CALLBACK_EACH_MODULE, $filename))
            {
                foreach ($callbacks as $callback)
                {
                    call_user_func($callback, $model);
                }
            }*/

            // Save model and set new id to collection
            //$idCollection[ $id ] = ($model->save())->id;
        }

        //$this->connections[ $filename ] = $idCollection;

        /*if($callbacks = $this->getCallbacks(self::CALLBACK_ON_FINISHED, $filename))
        {
            foreach ($callbacks as $callback)
            {
                call_user_func($callback, $idCollection);
            }
        }*/
    }
}
