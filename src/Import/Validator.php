<?php

namespace Oveleon\ProductInstaller\Import;

use Oveleon\ProductInstaller\Import\Validator\ArticleValidator;
use Oveleon\ProductInstaller\Import\Validator\ContentArticleValidator;
use Oveleon\ProductInstaller\Import\Validator\ContentEventValidator;
use Oveleon\ProductInstaller\Import\Validator\ContentNewsValidator;
use Oveleon\ProductInstaller\Import\Validator\EventValidator;
use Oveleon\ProductInstaller\Import\Validator\FaqValidator;
use Oveleon\ProductInstaller\Import\Validator\FormFieldValidator;
use Oveleon\ProductInstaller\Import\Validator\LayoutValidator;
use Oveleon\ProductInstaller\Import\Validator\ModuleValidator;
use Oveleon\ProductInstaller\Import\Validator\NewsletterRecipientValidator;
use Oveleon\ProductInstaller\Import\Validator\NewsletterValidator;
use Oveleon\ProductInstaller\Import\Validator\NewsValidator;
use Oveleon\ProductInstaller\Import\Validator\PageValidator;
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
        self::addValidator(ContentArticleValidator::getTrigger(), [ContentArticleValidator::class, 'setIncludes']);
        self::addValidator(ContentArticleValidator::getTrigger(), [ContentArticleValidator::class, 'setContentIncludes'], ValidatorMode::AFTER_IMPORT);

        // Content-News
        self::addValidator(ContentNewsValidator::getTrigger(), [ContentNewsValidator::class, 'setNewsConnection']);
        self::addValidator(ContentNewsValidator::getTrigger(), [ContentNewsValidator::class, 'setIncludes']);
        self::addValidator(ContentNewsValidator::getTrigger(), [ContentNewsValidator::class, 'setContentIncludes'], ValidatorMode::AFTER_IMPORT);

        // Content-Event
        self::addValidator(ContentEventValidator::getTrigger(), [ContentEventValidator::class, 'setEventConnection']);
        self::addValidator(ContentEventValidator::getTrigger(), [ContentEventValidator::class, 'setIncludes']);
        self::addValidator(ContentEventValidator::getTrigger(), [ContentEventValidator::class, 'setContentIncludes'], ValidatorMode::AFTER_IMPORT);
    }

    /**
     * Returns an import validator.
     */
    public static function getValidators($trigger = null, ValidatorMode $mode = ValidatorMode::BEFORE_IMPORT): ?array
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
    public static function addValidator($trigger, $fn, ValidatorMode $mode = ValidatorMode::BEFORE_IMPORT): void
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
}
