<?php

declare(strict_types=1);

namespace GamesApiBundle\Service;

use Acme\SymfonyDb\Entity\HowToPlayBlock;
use Acme\SymfonyDb\Entity\HowToPlayBlockContent;
use Acme\SymfonyDb\Entity\Language;
use Acme\SymfonyDb\Entity\HowToPlayPreset;
use Acme\SymfonyDb\Entity\PartnerEnabledGame;
use Acme\SymfonyDb\Type\HowToPlayBlockType;
use CoreBundle\Service\CacheService;
use CoreBundle\Service\GameService;
use CoreBundle\Service\RepositoryProviderInterface;
use CoreBundle\Service\Utility\MoneyService;
use Doctrine\ORM\EntityRepository;
use GamesApiBundle\DataObject\HowToPlay\HowToPlayBlockDTO;
use GamesApiBundle\DataObject\HowToPlay\HowToPlayOnScreen;
use GamesApiBundle\DataObject\HowToPlay\RequiredBlockTypes;

/**
 * Class HowToPlayProvider
 */
class HowToPlayProvider
{
    private const SUPPORT_ON_SCREEN_GAMES = [
        GameService::GAME_ANDAR_BAHAR,
        GameService::GAME_RPS,
        GameService::GAME_MATKA,
    ];

    private const KEY_MAIN = 'how_to_play';
    private const KEY_VIDEO = 'video';

    /** @var EntityRepository */
    private $presetRepository;

    /** @var EntityRepository */
    private $enabledGameRepository;

    /** @var EntityRepository */
    private $contentRepository;

    private CacheService $cacheService;

    /**
     * @param RepositoryProviderInterface $repositoryProvider
     * @param CacheService $cacheService
     */
    public function __construct(
        RepositoryProviderInterface $repositoryProvider,
        CacheService $cacheService
    )
    {
        $this->presetRepository = $repositoryProvider->getSlaveRepository(HowToPlayPreset::class);
        $this->enabledGameRepository = $repositoryProvider->getSlaveRepository(PartnerEnabledGame::class);
        $this->contentRepository = $repositoryProvider->getSlaveRepository(HowToPlayBlockContent::class);
        $this->cacheService = $cacheService;
    }

    /**
     * @param PartnerEnabledGame $requestedEnabledGame
     * @param Language $language
     *
     * @return HowToPlayBlockDTO[]
     */
    public function getBlocks(PartnerEnabledGame $requestedEnabledGame, Language $language): array
    {
        // Creating local variables to overwrite it during recursion.
        $enabledGame = $requestedEnabledGame;
        $partner = $requestedEnabledGame->getPartner();

        $requiredBlockTypes = new RequiredBlockTypes($enabledGame);

        do {
            $preset = $enabledGame ? $enabledGame->getHowToPlayPreset() : null;
            $blocks = $preset ? $this->getHowToPlayBlocks($preset, $language, $enabledGame, $requiredBlockTypes) : [];
            $partner = $partner->getParent();
            /** @var PartnerEnabledGame|null $enabledGame */
            $enabledGame = $this->enabledGameRepository->findOneBy(
                ['partner' => $partner, 'game' => $requestedEnabledGame->getGame()]
            );
        } while ($partner && !$blocks);

        if (!$blocks) {
            $blocks = $this->getDefaultBlocks($requestedEnabledGame, $language, $requiredBlockTypes);
        }

        return $blocks;
    }

    /**
     * @param PartnerEnabledGame $enabledGame
     * @param Language $language
     *
     * @return HowToPlayOnScreen
     */
    public function getOnScreen(
        PartnerEnabledGame $enabledGame,
        Language $language
    ): HowToPlayOnScreen
    {
        $videoUrl = $this->getVideoUrl($enabledGame, $language);
        $onScreen = new HowToPlayOnScreen($videoUrl);

        return $onScreen;
    }

    /**
     * @param PartnerEnabledGame $enabledGame
     * @param Language $language
     * @param RequiredBlockTypes $requiredBlockTypes
     *
     * @return HowToPlayBlockDTO[]
     */
    private function getDefaultBlocks(
        PartnerEnabledGame $enabledGame,
        Language $language,
        RequiredBlockTypes $requiredBlockTypes
    ): array
    {
        /** @var HowToPlayPreset|null $preset */
        $preset = $this->presetRepository->findOneBy(['isDefault' => true, 'game' => $enabledGame->getGame()]);

        $blocks = $preset ? $this->getHowToPlayBlocks($preset, $language, $enabledGame, $requiredBlockTypes) : [];

        return $blocks;
    }

    /**
     * @param HowToPlayPreset $preset
     * @param Language $requestedLanguage
     * @param PartnerEnabledGame $enabledGame
     * @param RequiredBlockTypes $requiredBlockTypes
     *
     * @return HowToPlayBlockDTO[]
     */
    private function getHowToPlayBlocks(
        HowToPlayPreset $preset,
        Language $requestedLanguage,
        PartnerEnabledGame $enabledGame,
        RequiredBlockTypes $requiredBlockTypes
    ): array
    {
        $presetBlocks = $preset->getPresetBlocks();

        /** @var HowToPlayBlockDTO[] $blockList */
        $blockList = [];

        foreach ($presetBlocks as $presetBlock) {
            $block = $presetBlock->getBlock();
            if (!$this->isBlockIncluded($block, $requiredBlockTypes)) {
                continue;
            }
            // Creating local variable to overwrite it during recursion.
            $language = $requestedLanguage;

            do {
                /** @var HowToPlayBlockContent|null $blockContent */
                $blockContent = $this->contentRepository->findOneBy(['language' => $language, 'block' => $block]);
                $language = $language->getFallbackLanguage();
            } while (!$blockContent && $language);

            if (!$blockContent) {
                continue;
            }

            $content = $this->formatContent($blockContent, $enabledGame);

            $howToPlayBlock = new HowToPlayBlockDTO(
                $blockContent->getTitle(),
                $content,
                $block->isExpandable()
            );

            $blockList[] = $howToPlayBlock;
        }

        return $blockList;
    }

    /**
     * @param HowToPlayBlockContent $blockContent
     * @param PartnerEnabledGame $enabledGame
     *
     * @return string
     */
    private function formatContent(
        HowToPlayBlockContent $blockContent,
        PartnerEnabledGame $enabledGame
    ): string
    {
        if ($blockContent->getBlock()->getType() === HowToPlayBlockType::TYPE_RTP) {
            $content = sprintf(
                '%s%s%s',
                $blockContent->getContent(),
                $enabledGame->getOddPreset()->getRtpToShow() * MoneyService::PERCENT_ALL,
                '%'
            );
        } else {
            $content = $blockContent->getContent();
        }

        return $content;
    }

    /**
     * @param HowToPlayBlock $block
     * @param RequiredBlockTypes $requiredBlockTypes
     *
     * @return bool
     */
    private function isBlockIncluded(
        HowToPlayBlock $block,
        RequiredBlockTypes $requiredBlockTypes
    ): bool
    {
        $blockType = $block->getType();
        switch ($blockType) {
            case HowToPlayBlockType::TYPE_SUBSCRIPTIONS:
                $isIncluded = $requiredBlockTypes->isSubscriptionRequired();
                break;
            case HowToPlayBlockType::TYPE_COMBINATIONS:
                $isIncluded = $requiredBlockTypes->isCombinationRequired();
                break;
            case HowToPlayBlockType::TYPE_RTP:
                $isIncluded = $requiredBlockTypes->isRtpRequired();
                break;
            case HowToPlayBlockType::TYPE_JACKPOT:
                $isIncluded = $requiredBlockTypes->isJackpotRequired();
                break;
            case HowToPlayBlockType::TYPE_CASHBACK:
                $isIncluded = $requiredBlockTypes->isCashbackRequired();
                break;
            case HowToPlayBlockType::TYPE_GAMIFICATION:
                $isIncluded = $requiredBlockTypes->isGamificationRequired();
                break;
            default:
                $isIncluded = true;
                break;
        }

        return $isIncluded;
    }

    /**
     * @param PartnerEnabledGame $enabledGame
     * @param Language $language
     *
     * @return string
     */
    private function getVideoUrl(
        PartnerEnabledGame $enabledGame,
        Language $language
    ): string
    {
        $game = $enabledGame->getGame();
        if (!in_array($game->getId(), self::SUPPORT_ON_SCREEN_GAMES, true)) {
            return '';
        }

        $key = $this->cacheService->getCacheKey([self::KEY_MAIN, self::KEY_VIDEO, $game->getId(), $language->getId()]);
        $cacheValue = $this->cacheService->get($key);

        if (is_string($cacheValue)) {
            return $cacheValue;
        }

        $howToPlayBlocks = $this->getBlocks($enabledGame, $language);

        // Merging content from all blocks into single string in order to cut out needed part
        $howToPlayContent = '';
        foreach ($howToPlayBlocks as $block) {
            $howToPlayContent .= $block->getContent();
        }

        $videoSrc = '';
        if ($howToPlayContent) {
            preg_match('/http[^"]+mp4/', $howToPlayContent, $matches);
            $videoSrc = $matches[0] ?? '';
        }

        // If the current language is missing an HTP video, use the fallback's video
        // Follow the chain of fallback languages until we find a video URL, or until we reach the end of the chain
        $fallbackLanguage = $language->getFallbackLanguage();
        if (!$videoSrc && $fallbackLanguage) {
            $videoSrc = $this->getVideoUrl($enabledGame, $fallbackLanguage);
        }

        $this->cacheService->set($key, $videoSrc);

        return $videoSrc;
    }
}
