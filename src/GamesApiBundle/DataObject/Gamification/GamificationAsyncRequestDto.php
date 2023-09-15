<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\Gamification;

/**
 * Class GamificationAsyncRequestDto
 */
final class GamificationAsyncRequestDto
{
    private ?int $betId;
    private int $playerId;
    private AsyncRequestInterface $request;

    /**
     * GamificationAsyncRequestDto constructor.
     *
     * @param AsyncRequestInterface $request
     * @param int $playerId
     * @param int|null $betId
     */
    public function __construct(AsyncRequestInterface $request, int $playerId, ?int $betId)
    {
        $this->betId = $betId;
        $this->playerId = $playerId;
        $this->request = $request;
    }

    /**
     * @return int|null
     */
    public function getBetId(): ?int
    {
        return $this->betId;
    }

    /**
     * @return int
     */
    public function getPlayerId(): int
    {
        return $this->playerId;
    }

    /**
     * @return AsyncRequestInterface
     */
    public function getRequest(): AsyncRequestInterface
    {
        return $this->request;
    }
}