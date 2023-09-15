<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification;

use Acme\SymfonyDb\Entity\Player;
use Acme\SymfonyDb\Entity\PlayerProfile;
use CoreBundle\Repository\PlayerRepository;
use Acme\SymfonyDb\Entity\PlayerProfileStateHistory;
use Acme\SymfonyDb\Type\PlayerProfileStateType;
use Carbon\CarbonImmutable;
use CoreBundle\Exception\ValidationException;
use CoreBundle\Service\RepositoryProviderInterface;
use GamesApiBundle\DataObject\Gamification\GamificationProfile;
use GamesApiBundle\Event\Gamification\PostPlayerProfileCreationEvent;
use GamesApiBundle\Exception\Gamification\CaptainUpException;
use GamesApiBundle\Repository\PlayerProfileRepository;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class PlayerProfileService
 */
class PlayerProfileService
{
    private RequestHandler $requestHandler;
    private PlayerRepository $playerRepository;
    private PlayerProfileRepository $playerProfileRepository;
    private AvatarProvider $avatarProvider;
    private AvatarValidator $avatarValidator;
    private array $captainUpLevelIds;
    private EventDispatcherInterface $dispatcher;

    /**
     * PlayerProfileService constructor.
     *
     * @param RequestHandler $requestHandler
     * @param RepositoryProviderInterface $repositoryProvider
     * @param AvatarProvider $avatarProvider
     * @param AvatarValidator $avatarValidator
     * @param EventDispatcherInterface $dispatcher
     * @param array $captainUpLevelIds
     */
    public function __construct(
        RequestHandler $requestHandler,
        RepositoryProviderInterface $repositoryProvider,
        AvatarProvider $avatarProvider,
        AvatarValidator $avatarValidator,
        EventDispatcherInterface $dispatcher,
        array $captainUpLevelIds
    )
    {
        $this->requestHandler = $requestHandler;
        $this->playerRepository = $repositoryProvider->getMasterRepository(PlayerRepository::class);
        $this->playerProfileRepository = $repositoryProvider->getMasterRepository(
            PlayerProfileRepository::class
        );
        $this->avatarProvider = $avatarProvider;
        $this->avatarValidator = $avatarValidator;
        $this->captainUpLevelIds = $captainUpLevelIds;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param Player $player
     * @param string $name
     * @param string $rootDomain
     *
     * @return GamificationProfile
     *
     * @throws CaptainUpException - when API responds with error
     * @throws ValidationException
     *      - when gamification disabled for partner
     *      - when player profile already exists
     */
    public function create(Player $player, string $name, string $rootDomain): GamificationProfile
    {
        $this->validateGamificationPartnerAvailability($player);

        if ($player->getProfile()) {
            throw new ValidationException('PROFILE_ALREADY_EXISTS');
        }

        $defaultAvatarUrl = $this->avatarProvider->getDefaultUrl($rootDomain);

        $profile = (new PlayerProfile())
            ->setName($name)
            ->setAvatarUrl($defaultAvatarUrl)
            ->setPlayer($player)
            ->setBlocked(false);

        $state = (new PlayerProfileStateHistory())
            ->setUpdateReason(null)
            ->setState(PlayerProfileStateType::CREATED)
            ->setUpdatedAt(CarbonImmutable::now())
            ->setPlayer($player);

        $player->setProfile($profile);
        $player->addProfileState($state);

        $createProfileResponse = $this->requestHandler->createProfile($profile);
        $profile->setExternalId($createProfileResponse->getExternalId());

        $player->setProfile($profile);

        $this->playerRepository->save($player);

        $this->dispatcher->dispatch(
            PostPlayerProfileCreationEvent::fromPlayerProfile($profile, CarbonImmutable::now('UTC'))
        );

        return new GamificationProfile(
            $createProfileResponse->getId(),
            $profile,
            $createProfileResponse->getSignedUser()
        );
    }

    /**
     * @param Player $player
     * @param string $avatarFileName
     * @param string $rootDomain
     *
     * @return GamificationProfile
     *
     * @throws CaptainUpException - when API responds with error
     * @throws ValidationException
     *      - when gamification disabled for partner
     *      - when player profile does not exist
     *      - when player profile level is too low to update specific avatar
     */
    public function update(
        Player $player,
        string $avatarFileName,
        string $rootDomain
    ): GamificationProfile
    {
        $this->validateGamificationAvailability($player);

        $profile = $player->getProfile();
        $externalLevelId = $this->requestHandler->retrieveProfileLevel($profile);
        $profileLevel = $this->captainUpLevelIds[$externalLevelId];
        $this->avatarValidator->validateLevelAvailability($avatarFileName, $profileLevel);

        $avatarUrl = $this->avatarProvider->getAvatarUrl($rootDomain, $avatarFileName);
        $profile->setAvatarUrl($avatarUrl);

        $response = $this->requestHandler->updateProfile($profile);

        $this->playerRepository->save($profile);

        return $response;
    }

    /**
     * @param Player $player
     * @param string $reason
     *
     * @return GamificationProfile
     *
     * @throws CaptainUpException - when API responds with error
     * @throws ValidationException
     *      - when gamification disabled for partner
     *      - when player profile does not exist
     *      - when player profile is already blocked
     */
    public function block(Player $player, string $reason): GamificationProfile
    {
        $this->validateGamificationAvailability($player);

        $profile = $player->getProfile();

        if ($profile->isBlocked()) {
            throw new ValidationException('PROFILE_ALREADY_BLOCKED');
        }

        $profile->setBlocked(true);
        $state = (new PlayerProfileStateHistory())
            ->setUpdateReason($reason)
            ->setState(PlayerProfileStateType::BLOCKED)
            ->setUpdatedAt(CarbonImmutable::now())
            ->setPlayer($player);

        $player->setProfile($profile);
        $player->addProfileState($state);

        $response = $this->requestHandler->blockProfile($profile);
        $this->playerRepository->save($player);

        return $response;
    }

    /**
     * @param Player $player
     *
     * @return GamificationProfile
     *
     * @throws CaptainUpException - when API responds with error
     * @throws ValidationException
     *      - when gamification disabled for partner
     *      - when player profile does not exist
     *      - when player profile is already unblocked
     */
    public function unblock(Player $player): GamificationProfile
    {
        $this->validateGamificationAvailability($player);

        $profile = $player->getProfile();

        if (!$profile->isBlocked()) {
            throw new ValidationException('PROFILE_ALREADY_UNBLOCKED');
        }

        $profile->setBlocked(false);
        $state = (new PlayerProfileStateHistory())
            ->setUpdateReason(null)
            ->setState(PlayerProfileStateType::UNBLOCKED)
            ->setUpdatedAt(CarbonImmutable::now())
            ->setPlayer($player);

        $player->setProfile($profile);
        $player->addProfileState($state);

        $response = $this->requestHandler->unblockProfile($profile);
        $this->playerRepository->save($player);

        return $response;
    }

    /**
     * @param Player $player
     * @param string $reason
     *
     * @return GamificationProfile
     *
     * @throws CaptainUpException
     * @throws ValidationException
     */
    public function delete(Player $player, string $reason): GamificationProfile
    {
        $this->validateGamificationAvailability($player);

        $profile = $player->getProfile();

        $state = (new PlayerProfileStateHistory())
            ->setUpdateReason($reason)
            ->setState(PlayerProfileStateType::DELETED)
            ->setUpdatedAt(CarbonImmutable::now())
            ->setPlayer($player);

        $player->removeProfile($profile);
        $player->addProfileState($state);

        $response = $this->requestHandler->deleteProfile($profile);
        $this->playerProfileRepository->delete($profile);

        $this->playerRepository->save($player);

        return $response;
    }

    /**
     * @param Player $player
     *
     * @throws ValidationException
     */
    private function validateGamificationAvailability(Player $player): void
    {
        $this->validateGamificationPartnerAvailability($player);

        if (!$player->getProfile()) {
            throw new ValidationException('PROFILE_NOT_EXISTS');
        }
    }

    /**
     * @param Player $player
     *
     * @throws ValidationException
     */
    private function validateGamificationPartnerAvailability(Player $player): void
    {
        if (!$player->getPartner()->getGamificationEnabled()) {
            throw new ValidationException('GAMIFICATION_DISABLED');
        }
    }
}
