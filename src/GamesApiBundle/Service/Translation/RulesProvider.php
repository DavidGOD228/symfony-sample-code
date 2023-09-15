<?php

namespace GamesApiBundle\Service\Translation;

use Acme\SymfonyDb\Entity\RulesText;
use CoreBundle\Service\RepositoryProviderInterface;

/**
 * Class RulesProvider
 */
class RulesProvider
{
    private const RULES_TRANSLATION_PROMOTION = 1;
    private const RULES_TRANSLATION_GAME = 2;
    private const RULES_TRANSLATION_INDEX = 3;
    private const RULES_TRANSLATION_KEY_STRUCTURE_LIMIT = 4;

    private const RULES_TITLE_PART = 0;
    private const RULES_DESCRIPTION_PART = 1;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;

    /**
     * RulesProvider constructor.
     *
     * @param RepositoryProviderInterface $repositoryProvider
     */
    public function __construct(RepositoryProviderInterface $repositoryProvider)
    {
        $this->repository = $repositoryProvider->getSlaveRepository(RulesText::class);
    }

    /**
     * @return array
     */
    public function getRulesTexts(): array
    {
        $rulesTexts = $this->repository->findAll();

        return $rulesTexts;
    }

    /**
     * Copy-paste from CodeIgniter. Better to refactor.
     *
     * @param string $code
     * @param string $translation
     *
     * @return array
     */
    public function format(string $code, string $translation): array
    {
        $keys = explode('.', $code);
        if (count($keys) != self::RULES_TRANSLATION_KEY_STRUCTURE_LIMIT) {
            return [];
        }

        $promotion = $keys[self::RULES_TRANSLATION_PROMOTION];
        $gameId = $keys[self::RULES_TRANSLATION_GAME];
        $index = $keys[self::RULES_TRANSLATION_INDEX];

        $message = explode('|||', $translation);

        if (count($message) > 1) {
            $title = $message[self::RULES_TITLE_PART];
            $description = $message[self::RULES_DESCRIPTION_PART];
        } else {
            $title = '';
            $description = $message[0];
        }

        return [
            'promotion' => $promotion,
            'gameId' => $gameId,
            'index' => $index,
            'title' => $title,
            'description' => $description,
        ];
    }
}
