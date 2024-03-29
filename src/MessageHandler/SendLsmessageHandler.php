<?php

namespace App\MessageHandler;

use App\Entity\Connector\LsmessageConnector;
use App\Entity\Convocation;
use App\Entity\User;
use App\Message\ConvocationSent;
use App\Repository\ConvocationRepository;
use App\Repository\SittingRepository;
use App\Service\Connector\LsmessageConnectorManager;
use Libriciel\LsMessageWrapper\Sms;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendLsmessageHandler
{
    private LsmessageConnectorManager $lsmessageConnectorManager;
    private ConvocationRepository $convocationRepository;
    private SittingRepository $sittingRepository;

    public function __construct(LsmessageConnectorManager $lsmessageConnectorManager, ConvocationRepository $convocationRepository, SittingRepository $sittingRepository)
    {
        $this->lsmessageConnectorManager = $lsmessageConnectorManager;
        $this->convocationRepository = $convocationRepository;
        $this->sittingRepository = $sittingRepository;
    }

    public function __invoke(ConvocationSent $convocationSent): void
    {
        if ('test' === getenv('APP_ENV')) {
            return;
        }

        $sitting = $this->sittingRepository->find($convocationSent->getSittingId());

        if ($sitting->getType()->getIsSms() || $sitting->getType()->getIsSmsEmployees() || $sitting->getType()->getIsSmsGuests()) {
            $lsmessageConnector = $this->lsmessageConnectorManager->getLsmessageConnector($sitting->getStructure());
            if (!$lsmessageConnector || !$lsmessageConnector->getActive()) {
                return;
            }
            $convocations = $this->convocationRepository->getConvocationsWithUser($convocationSent->getConvocationIds());
            $smsList = $this->prepareSms($convocations, $lsmessageConnector);
            $this->lsmessageConnectorManager->sendSms($sitting, $smsList);
        }
    }

    /**
     * @param iterable<Convocation> $convocations
     *
     * @return Sms[]
     */
    public function prepareSms(iterable $convocations, LsmessageConnector $connector): array
    {
        $smsList = [];
        foreach ($convocations as $convocation) {
            if ($this->isActorConvocableWithPhone($convocation) || $this->isEmployeeConvocableWithPhone($convocation) || $this->isGuestConvocableWithPhone($convocation)) {
                $smsList[] = new Sms('idelibre', $convocation->getUser()->getPhone(), $connector->getContent(), $connector->getSender());
            }
        }

        return $smsList;
    }

    private function isConvocation(Convocation $convocation): bool
    {
        return Convocation::CATEGORY_CONVOCATION === $convocation->getCategory();
    }

    private function isInvitation(Convocation $convocation): bool
    {
        return Convocation::CATEGORY_INVITATION === $convocation->getCategory();
    }

    private function hasPhone(User $user): bool
    {
        return null !== $user->getPhone();
    }

    private function isActorConvocableWithPhone(Convocation $convocation)
    {
        $sittingWithSms = $convocation->getSitting()->getType()->getIsSms();
        $isConvocated = $this->isConvocation($convocation);
        $hasPhoneNumber = $this->hasPhone($convocation->getUser());

        return $sittingWithSms && $isConvocated && $hasPhoneNumber;
    }

    private function isEmployeeConvocableWithPhone(Convocation $convocation)
    {
        $isSittingWithSms = $convocation->getSitting()->getType()->getIsSmsEmployees();
        $isInvitated = $this->isInvitation($convocation);
        $hasPhoneNumber = $this->hasPhone($convocation->getUser());
        $isAReelEmployee = in_array($convocation->getUser()->getRole()->getName(), ['Secretary', 'Admin', 'Employee']);

        return $isSittingWithSms && $isInvitated && $hasPhoneNumber && $isAReelEmployee;
    }

    private function isGuestConvocableWithPhone(Convocation $convocation)
    {
        $isSittingWithSms = $convocation->getSitting()->getType()->getIsSmsGuests();
        $isInvitated = $this->isInvitation($convocation);
        $hasPhoneNumber = $this->hasPhone($convocation->getUser());
        $isAReelGuest = in_array($convocation->getUser()->getRole()->getName(), ['Guest']);

        return $isSittingWithSms && $isInvitated && $hasPhoneNumber && $isAReelGuest;
    }
}
