<?php

declare(strict_types=1);

namespace Oveleon\ProductInstaller\EventListener;

use Contao\BackendUser;
use Contao\CoreBundle\Event\ContaoCoreEvents;
use Contao\CoreBundle\Event\MenuEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsEventListener(ContaoCoreEvents::BACKEND_MENU_BUILD, priority: -255)]
class BackendMenuListener
{
    public function __construct(
        protected TranslatorInterface $translator,
        protected TokenStorageInterface $tokenStorage,
    ) {}

    public function __invoke(MenuEvent $event): void
    {
        $user = $this->tokenStorage->getToken()?->getUser();

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
            ->setLinkAttribute('data-turbo-prefetch', 'false')
        ;

        $contentNode->addChild($node);
    }
}
