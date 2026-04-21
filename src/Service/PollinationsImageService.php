<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Builds a direct image URL for Pollinations (suitable for <img src> or redirection).
 *
 * @see https://enter.pollinations.ai/api/docs
 */
class PollinationsImageService
{
    public function __construct(
        #[Autowire('%env(POLLINATIONS_API_KEY)%')]
        private string $apiKey,
    ) {
    }

    public function imageUrlForPrompt(string $prompt, int $width = 768, int $height = 512): string
    {
        $encoded = rawurlencode($prompt);
        $url = sprintf('https://image.pollinations.ai/prompt/%s?width=%d&height=%d', $encoded, $width, $height);
        if ($this->apiKey !== '') {
            $url .= '&key='.rawurlencode($this->apiKey);
        }

        return $url;
    }
}
