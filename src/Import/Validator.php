<?php

namespace Oveleon\ProductInstaller\Import;

use Oveleon\ProductInstaller\Import\Validator\ArticleValidator;
use Oveleon\ProductInstaller\Import\Validator\CollectionValidator;
use Oveleon\ProductInstaller\Import\Validator\ContentArticleValidator;
use Oveleon\ProductInstaller\Import\Validator\ContentEventValidator;
use Oveleon\ProductInstaller\Import\Validator\ContentNewsValidator;
use Oveleon\ProductInstaller\Import\Validator\ContentValidator;
use Oveleon\ProductInstaller\Import\Validator\EventValidator;
use Oveleon\ProductInstaller\Import\Validator\FaqCategoryValidator;
use Oveleon\ProductInstaller\Import\Validator\FaqValidator;
use Oveleon\ProductInstaller\Import\Validator\FileValidator;
use Oveleon\ProductInstaller\Import\Validator\FormFieldValidator;
use Oveleon\ProductInstaller\Import\Validator\FormValidator;
use Oveleon\ProductInstaller\Import\Validator\LayoutValidator;
use Oveleon\ProductInstaller\Import\Validator\MemberGroupValidator;
use Oveleon\ProductInstaller\Import\Validator\ModuleValidator;
use Oveleon\ProductInstaller\Import\Validator\NewsArchiveValidator;
use Oveleon\ProductInstaller\Import\Validator\NewsletterRecipientValidator;
use Oveleon\ProductInstaller\Import\Validator\NewsletterValidator;
use Oveleon\ProductInstaller\Import\Validator\NewsValidator;
use Oveleon\ProductInstaller\Import\Validator\PageValidator;
use Oveleon\ProductInstaller\Import\Validator\ThemeValidator;
use Oveleon\ProductInstaller\Import\Validator\ValidatorInterface;
use Oveleon\ProductInstaller\Import\Validator\ValidatorMode;

/**
 * Validator helper class for import validators.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class Validator
{
    static ?array $validators = null;

    /**
     * Register default validators.
     */
    public static function useDefaultTableValidators(): void
    {
        // File
        self::addValidator(FileValidator::getTrigger(), [FileValidator::class, 'createFile']);
        self::addValidator(FileValidator::getTrigger(), [FileValidator::class, 'createDotFiles'], ValidatorMode::AFTER_IMPORT);

        // Theme
        self::addValidator(ThemeValidator::getTrigger(), [ThemeValidator::class, 'setFolderConnection']);
        self::addValidator(ThemeValidator::getTrigger(), [ThemeValidator::class, 'setSkinFolderConnection']);
        self::addValidator(ThemeValidator::getTrigger(), [ThemeValidator::class, 'setSkinSourceFilesConnection']);
        self::addValidator(ThemeValidator::getTrigger(), [ThemeValidator::class, 'setScreenshotConnection']);

        // Page
        self::addValidator(PageValidator::getTrigger(), [PageValidator::class, 'selectRootPage']);
        self::addValidator(PageValidator::getTrigger(), [PageValidator::class, 'setLayoutConnection']);
        self::addValidator(PageValidator::getTrigger(), [PageValidator::class, 'setPageJumpToConnection'], ValidatorMode::AFTER_IMPORT_ROW);

        // Layout
        self::addValidator(LayoutValidator::getTrigger(), [LayoutValidator::class, 'setThemeConnection']);
        self::addValidator(LayoutValidator::getTrigger(), [LayoutValidator::class, 'setExternalFileConnection']);
        self::addValidator(LayoutValidator::getTrigger(), [LayoutValidator::class, 'setExternalJsFileConnection']);
        self::addValidator(LayoutValidator::getTrigger(), [LayoutValidator::class, 'setModuleConnection']);

        // Module
        self::addValidator(ModuleValidator::getTrigger(), [ModuleValidator::class, 'setThemeConnection']);
        self::addValidator(ModuleValidator::getTrigger(), [ModuleValidator::class, 'setRegPageConnection']);
        self::addValidator(ModuleValidator::getTrigger(), [ModuleValidator::class, 'setRootPageConnection']);
        self::addValidator(ModuleValidator::getTrigger(), [ModuleValidator::class, 'setPagesConnection']);

        // FAQ
        self::addValidator(FaqValidator::getTrigger(), [FaqValidator::class, 'setFaqCategoryConnection']);

        // News
        self::addValidator(NewsValidator::getTrigger(), [NewsValidator::class, 'setNewsArchiveConnection']);

        // Newsletter
        self::addValidator(NewsletterValidator::getTrigger(), [NewsletterValidator::class, 'setChannelConnection']);

        // Newsletter recipient
        self::addValidator(NewsletterRecipientValidator::getTrigger(), [NewsletterRecipientValidator::class, 'setChannelConnection']);

        // Event
        self::addValidator(EventValidator::getTrigger(), [EventValidator::class, 'setEventArchiveConnection']);

        // Form field
        self::addValidator(FormFieldValidator::getTrigger(), [FormFieldValidator::class, 'setFormConnection']);

        // Article
        self::addValidator(ArticleValidator::getTrigger(), [ArticleValidator::class, 'setPageConnection']);

        // Content-Article
        self::addValidator(ContentArticleValidator::getTrigger(), [ContentArticleValidator::class, 'setArticleConnection']);

        // Content-News
        self::addValidator(ContentNewsValidator::getTrigger(), [ContentNewsValidator::class, 'setNewsConnection']);

        // Content-Event
        self::addValidator(ContentEventValidator::getTrigger(), [ContentEventValidator::class, 'setEventConnection']);

        // Connects content includes
        self::addValidatorCollection([
            ContentEventValidator::class,
            ContentNewsValidator::class,
            ContentArticleValidator::class
        ], [
            [ContentValidator::class, 'setIncludes'],
            [ContentValidator::class, 'setFileConnection'],
            [ContentValidator::class, 'setContentIncludes', ValidatorMode::AFTER_IMPORT_ROW],
        ]);

        // Connects jumpTo pages
        self::addValidatorCollection([
            FormValidator::class,
            FaqCategoryValidator::class,
            NewsArchiveValidator::class,
            MemberGroupValidator::class,
            ModuleValidator::class,
        ], ['setJumpToPageConnection']);
    }

    /**
     * Returns an import validator.
     */
    public static function getValidators($trigger = null, ValidatorMode $mode = ValidatorMode::BEFORE_IMPORT_ROW): ?array
    {
        if(null === $trigger)
        {
            return self::$validators[$mode->value] ?? null;
        }

        return self::$validators[$mode->value][$trigger] ?? null;
    }

    /**
     * Adds an import validator.
     */
    public static function addValidator($trigger, $fn, ValidatorMode $mode = ValidatorMode::BEFORE_IMPORT_ROW): void
    {
        if(!\array_key_exists($mode->value, self::$validators ?? []))
        {
            self::$validators[$mode->value] = [];
        }

        if(self::$validators[$mode->value][$trigger] ?? false)
        {
            self::$validators[$mode->value][$trigger][] = $fn;
            return;
        }

        self::$validators[$mode->value][$trigger] = [$fn];
    }

    /**
     * Adds an import validator collection.
     */
    public static function addValidatorCollection(array $validators, array $collectionFnNames): void
    {
        /** @var ValidatorInterface $validator */
        foreach ($validators as $validator)
        {
            foreach ($collectionFnNames as $collectionFnName)
            {
                // Set default class ($collectionFnNames could be an array or a string, if a string is passed we use the CollectionValidator as class)
                $collectionClass = CollectionValidator::class;
                $validatorMode = ValidatorMode::BEFORE_IMPORT_ROW;

                if(is_array($collectionFnName))
                {
                    $collectionClass  = $collectionFnName[0] ?? $collectionClass;
                    $validatorMode    = $collectionFnName[2] ?? $validatorMode;
                    $collectionFnName = $collectionFnName[1];
                }

                switch ($validatorMode)
                {
                    case ValidatorMode::BEFORE_IMPORT_ROW:

                        self::addValidator(
                            $validator::getTrigger(),
                            fn (array &$row, AbstractPromptImport $importer) => call_user_func_array([$collectionClass, $collectionFnName], [&$row, $importer, $validator::getModel()]),
                            $validatorMode
                        );

                        break;

                    case ValidatorMode::AFTER_IMPORT_ROW:
                    case ValidatorMode::AFTER_IMPORT:

                        self::addValidator(
                            $validator::getTrigger(),
                            fn(array $modelRowCollection, AbstractPromptImport $importer) => call_user_func_array([$collectionClass, $collectionFnName], [$modelRowCollection, $importer]),
                            $validatorMode
                        );

                        break;
                }
            }
        }
    }
}
