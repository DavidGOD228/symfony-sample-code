<?php

namespace GamesApiBundle\Service\Translation;

use Acme\WebApi\Shared\TranslationAdapterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class WebApiAdapter
 */
class WebApiAdapter implements TranslationAdapterInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * WebApiAdapter constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param int $gameId
     * @param string $oddClass
     * @param string $locale
     *
     * @return string
     */
    public function translateOdd(int $gameId, string $oddClass, string $locale): string
    {
        return $this->translator->trans($gameId . '-' . $oddClass, [], 'odds', $locale);
    }

    /**
     * @param int $gameId
     * @param string $locale
     *
     * @return string
     */
    public function translateGame(int $gameId, string $locale): string
    {
        return $this->translator->trans($gameId, [], 'games', $locale);
    }
}
