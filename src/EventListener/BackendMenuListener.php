<?php

declare(strict_types=1);

namespace Oveleon\ProductInstaller\EventListener;

use Contao\BackendUser;
use Contao\CoreBundle\Event\MenuEvent;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTag;

/**
 * @ServiceTag("kernel.event_listener", event="contao.backend_menu_build", priority=-255)
 */
class BackendMenuListener
{
    public function __construct(private readonly Security $security, protected TranslatorInterface $translator)
    {}

    public function __invoke(MenuEvent $event): void
    {
        $user = $this->security->getUser();

        if (!$user instanceof BackendUser || !$user->isAdmin) {
            return;
        }

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
