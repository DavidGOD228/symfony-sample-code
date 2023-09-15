<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\InitialInfo\Component;

use Acme\SymfonyDb\Entity\Promotion;
use DateTimeInterface;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class PartnerInitialInfoPromotion
 *
 * @psalm-readonly
 */
final class PartnerInitialInfoPromotion
{
    /** @SerializedName("id") */
    public int $id;
    /** @SerializedName("currencyCode") */
    public string $currencyCode;
    /** @SerializedName("currentAmount") */
    public string $currentAmount;
    /** @SerializedName("eligiblePlayerCount") */
    public ?int $eligiblePlayerCount;
    /** @SerializedName("startsAt") */
    public DateTimeInterface $startsAt;
    /** @SerializedName("endsAt") */
    public ?DateTimeInterface $endsAt;
    /** @SerializedName("status") */
    public int $status;
    /** @SerializedName("type") */
    public string $type;
    /**
     * @var int[]
     * @SerializedName("enabledForGames")
     */
    public array $enabledForGames;

    /**
     * @var string[]
     * @SerializedName("amountByType")
     */
    public array $amountByType;

    /**
     * @param Promotion $promotion
     */
    public function __construct(Promotion $promotion)
    {
        $this->id = $promotion->getId();
        $this->currencyCode = $promotion->getCurrencyCode();
        // Converting to string to get normal precision, not json serialize_precision like 1.0000000001.
        $this->currentAmount = (string) $promotion->getCurrentAmount();
        $this->eligiblePlayerCount = $promotion->getEligiblePlayerCount();
        $this->enabledForGames = $promotion->getEnabledGameIds();
        $this->startsAt = $promotion->getStartsAt();
        $this->endsAt = $promotion->getEndsAt();

        $this->status = $promotion->getStatus();
        $this->type = $promotion->getType();

        $this->amountByType = [];
        $amounts = $promotion->getAmountByType();
        if ($amounts) {
            foreach ($amounts as $amount) {
                // Converting to string to get normal precision, not json serialize_precision like 1.0000000001.
                $this->amountByType[$amount->getType()] = (string) $amount->getAmount();
            }
        }
    }
}
