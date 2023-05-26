<?php

namespace Oveleon\ProductInstaller\Import;

use Oveleon\ProductInstaller\Import\Prompt\AbstractPrompt;
use Oveleon\ProductInstaller\Import\Prompt\FormPrompt;
use Oveleon\ProductInstaller\SetupLock;

class TableImport extends AbstractPromptImport
{
    /**
     * Defines the table of the currently handled table.
     */
    private ?string $table = null;

    /**
     * Defines the parent table of the currently handled table.
     */
    private ?int $parentTable = null;

    /**
     * Defines the curren prompt.
     */
    private ?AbstractPrompt $prompt = null;

    /**
     * Defines the content of the currently handled table.
     */
    private ?array $content = null;

    /**
     * Initialize instance and set setup lock state.
     */
    public function __construct(
        protected readonly SetupLock $setupLock
    ){
        $setupLock->setScope(TableImport::class);
    }

    /**
     * Sets the current prompt.
     */
    public function setPrompt(AbstractPrompt $prompt): void
    {
        $this->prompt = $prompt;
    }

    /**
     * Starts importing the tables and returns prompts.
     * If no prompt is needed, null is returned.
     */
    public function import(string $tableName, array $tableContent): ?AbstractPrompt
    {
        $this->table = $tableName;
        $this->content = $tableContent;

        // Get current import state
        $state = $this->getState($tableName);

        switch($state)
        {
            // Check for prompts
            case ImportStateType::INIT->value:

                $this->scanPrompts();
                break;

            // Ready to import
            case ImportStateType::IMPORT->value:

                $this->setupLock->set($this->table, ImportStateType::FINISH->value);
                $this->setupLock->save();

                break;

            default:
                return null;
        }

        // Get DCA from table to detect ptable e.g.
        // Load data container for the current table
        //Controller::loadDataContainer($currentTable);

        if($this->prompt)
        {
            return $this->prompt;
        }

        return null;
    }

    private function scanPrompts(): void
    {
        // Fixme: Simulate prompt found
        if(true)
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

    public function getState($tableName): string
    {
        $tableMode = $this->setupLock->get($tableName);

        // Skip the table if it set to finish
        if($tableMode === ImportStateType::FINISH->value)
        {
            return ImportStateType::SKIP->value;
        }

        // Check table modes
        if($tableMode)
        {
            switch ($tableMode)
            {
                case ImportStateType::PROMPT->value:

                    if($this->getPromptResponse()->get('name') === $tableName)
                    {
                        return ImportStateType::IMPORT->value;
                    }

                    break;
            }
        }

        // Return init state
        return ImportStateType::INIT->value;
    }
}
