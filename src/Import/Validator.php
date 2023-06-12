<?php

namespace Oveleon\ProductInstaller\Import;

use Contao\FormFieldModel;
use Oveleon\ProductInstaller\Import\Validator\ArticleValidator;
use Oveleon\ProductInstaller\Import\Validator\ContentArticleValidator;
use Oveleon\ProductInstaller\Import\Validator\ContentEventValidator;
use Oveleon\ProductInstaller\Import\Validator\ContentNewsValidator;
use Oveleon\ProductInstaller\Import\Validator\EventValidator;
use Oveleon\ProductInstaller\Import\Validator\FaqValidator;
use Oveleon\ProductInstaller\Import\Validator\FormFieldValidator;
use Oveleon\ProductInstaller\Import\Validator\LayoutValidator;
use Oveleon\ProductInstaller\Import\Validator\ModuleValidator;
use Oveleon\ProductInstaller\Import\Validator\NewsValidator;
use Oveleon\ProductInstaller\Import\Validator\PageValidator;

class Validator
{
    static ?array $validators = null;

    /**
     * Register default validators.
     */
    public static function useDefaultTableValidators(): void
    {
        // Page
        self::addValidator(PageValidator::getTrigger(), [PageValidator::class, 'selectRootPage']);
        self::addValidator(PageValidator::getTrigger(), [PageValidator::class, 'setLayoutConnection']);

        // Layout
        self::addValidator(LayoutValidator::getTrigger(), [LayoutValidator::class, 'setThemeConnection']);

        // Module
        self::addValidator(ModuleValidator::getTrigger(), [ModuleValidator::class, 'setThemeConnection']);

        // FAQ
        self::addValidator(FaqValidator::getTrigger(), [FaqValidator::class, 'setFaqCategoryConnection']);

        // News
        self::addValidator(NewsValidator::getTrigger(), [NewsValidator::class, 'setNewsArchiveConnection']);

        // Event
        self::addValidator(EventValidator::getTrigger(), [EventValidator::class, 'setEventArchiveConnection']);

        // Form field
        self::addValidator(FormFieldValidator::getTrigger(), [FormFieldValidator::class, 'setFormConnection']);

        // Article
        self::addValidator(ArticleValidator::getTrigger(), [ArticleValidator::class, 'setPageConnection']);

        // Content-Article
        self::addValidator(ContentArticleValidator::getTrigger(), [ContentArticleValidator::class, 'setArticleConnection']);
        self::addValidator(ContentArticleValidator::getTrigger(), [ContentArticleValidator::class, 'setIncludes']);

        // Content-News
        self::addValidator(ContentNewsValidator::getTrigger(), [ContentNewsValidator::class, 'setNewsConnection']);
        self::addValidator(ContentNewsValidator::getTrigger(), [ContentNewsValidator::class, 'setIncludes']);

        // Content-Event
        self::addValidator(ContentEventValidator::getTrigger(), [ContentEventValidator::class, 'setEventConnection']);
        self::addValidator(ContentEventValidator::getTrigger(), [ContentEventValidator::class, 'setIncludes']);
    }

    /**
     * Returns an import validator.
     */
    public static function getValidators($trigger = null): ?array
    {
        if(null === $trigger)
        {
            return self::$validators ?? null;
        }

        return self::$validators[$trigger] ?? null;
    }

    /**
     * Adds an import validator.
     */
    public static function addValidator($trigger, $fn): void
    {
        if(self::$validators[$trigger] ?? false)
        {
            self::$validators[$trigger][] = $fn;
            return;
        }

        self::$validators[$trigger] = [$fn];
    }
}
