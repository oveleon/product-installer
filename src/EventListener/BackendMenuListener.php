<?php

namespace Oveleon\ProductInstaller\EventListener;

use Contao\CoreBundle\Event\MenuEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTag;

/**
 * @ServiceTag("kernel.event_listener", event="contao.backend_menu_build", priority=-255)
 */
class BackendMenuListener
{
    public function __construct(
        protected TranslatorInterface $translator
    ){}

    public function __invoke(MenuEvent $event): void
    {
        $factory = $event->getFactory();
        $tree = $event->getTree();

        if ('mainMenu' !== $tree->getName()) {
            return;
        }

        $contentNode = $tree->getChild('system');

        $node = $factory
            ->createItem('product-installer')
            ->setUri('#')
            ->setLabel($this->translator->trans('installer.menu.label', [], 'installer'))
            ->setLinkAttribute('title', $this->translator->trans('installer.menu.title', [], 'installer'))
            ->setLinkAttribute('id', 'product-installer')
            ->setLinkAttribute('class', 'product-installer')
        ;

        $contentNode->addChild($node);
    }
}
