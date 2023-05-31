<?php

namespace Oveleon\ProductInstaller\Import;

use Contao\Controller;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Model;
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
     * Starts importing the tables and returns prompts.
     * If no prompt is needed, null is returned.
     */
    public function import(string $tableName, array $tableContent): ?AbstractPrompt
    {
        $this->table = $tableName;
        $this->content = $tableContent;

        // Get current import state
        $state = $this->getTableState($tableName);

        switch($state)
        {
            case ImportStateType::INIT->value:

                // Empty state for sending prompts when a table is initiated for the first time

            case ImportStateType::PROMPT->value:

                // ToDo: Merge prompt response with config
                if($response = $this->getPromptResponse() && false)
                {
                    // Fixme: Something went wrong when merging data -> Prompt again -> break -> otherwise goto import
                    break;
                }

            case ImportStateType::IMPORT->value:

                $this->start();

                // ToDo: Set table state to finished when import was successfully
                //$this->setupLock->set($this->table, ImportStateType::FINISH->value);
                //$this->setupLock->save();
        }

        return $this->prompt ?? null;
    }

    protected function scanPrompts(): void
    {
        $hasPrompts = false;

        /*if($conditions = $this->getConditions($this->table))
        {
            foreach ($conditions as $condition)
            {
                if(\array_key_exists('field', $condition))
                {

                }
            }
        }*/

        if($hasPrompts)
        {
            $this->setupLock->set($this->table, ImportStateType::PROMPT->value);

            $this->setPrompt(new FormPrompt($this->table));
        }

        // No prompt found; continue with import
        else
        {
            $this->setupLock->set($this->table, ImportStateType::IMPORT->value);
        }

        $this->setupLock->save();
    }

    /**
     * Returns the current state of the table.
     */
    public function getTableState($tableName): string
    {
        // Return init state if the table was not found
        if(!$tableMode = $this->setupLock->get($tableName))
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
    public function getTableInformation($tableName): ?array
    {
        // Load data container for the current table
        Controller::loadDataContainer($tableName);

        if(!isset($GLOBALS['TL_DCA'][$tableName]['config']))
        {
            return null;
        }

        return [
            ...array_intersect_key($GLOBALS['TL_DCA'][$tableName]['config'], [
                'dataContainer' => '',
                'ptable'        => '',
                'dynamicPtable' => '',
                'ctable'        => ''
            ]),
            ...[
                'sortingMode'   => $GLOBALS['TL_DCA'][$tableName]['list']['sorting']['mode'] ?? ''
            ]
        ];
    }

    protected function start(): void
    {
        $info = $this->getTableInformation($this->table);

        // Check if it is a table
        if(DC_Table::class === $info['dataContainer'])
        {
            // Determine the type of the import
            if(DataContainer::MODE_TREE === $info['sortingMode'])
            {
                // $this->importTreeTable();
            }
            elseif(\array_key_exists('ptable', $info) && $parentTable = $info['ptable'])
            {
                // $this->importNestedTable($parentTable);
            }
            else
            {
                $this->importTable();
            }
        }
    }

    /**
     * Import table.
     */
    protected function importTable(): void
    {
        // Get model class by table
        $modelClass = Model::getClassFromTable($this->table);

        foreach ($this->content as $row)
        {
            // Temporarily store the ID of the row and delete it before assigning it to the new model
            $id = $row['id'];
            unset($row['id']);

            $model = new $modelClass();
            //$model->setRow($row);

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
