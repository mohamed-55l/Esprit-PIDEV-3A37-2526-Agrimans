<?php

namespace App\Twig\Extension;

use App\Service\SessionCartService;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Exposes the SessionCartService as a Twig global variable (`sessionCartService`)
 * so that base.html.twig can call sessionCartService.getCartCount() on every page.
 */
class CartGlobalExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private SessionCartService $sessionCartService) {}

    public function getGlobals(): array
    {
        return [
            'sessionCartService' => $this->sessionCartService,
        ];
    }
}
