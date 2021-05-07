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
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SendLsmessageHandler implements MessageHandlerInterface
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

    public function __invoke(ConvocationSent $convocationSent)
    {
        if ('test' === getenv('APP_ENV')) {
            return;
        }

        $sitting = $this->sittingRepository->find($convocationSent->getSittingId());
        $lsmessageConnector = $this->lsmessageConnectorManager->getLsmessageConnector($sitting->getStructure());
        if (!$lsmessageConnector || !$lsmessageConnector->getActive()) {
            return;
        }
        $convocations = $this->convocationRepository->getConvocationsWithUser($convocationSent->getConvocationIds());

        $smsList =$this->prepareSms($convocations, $lsmessageConnector);
        $this->lsmessageConnectorManager->sendSms($sitting, $smsList);
    }

    /**
     * @param iterable<Convocation> $convocations
     * @return Sms[]
     */
    public function prepareSms(iterable $convocations, LsmessageConnector $connector): array
    {
        $smsList = [];
        foreach ($convocations as $convocation) {
            if ($this->isConvocation($convocation) && $this->hasPhone($convocation->getUser())) {
                $smsList[] = new Sms('idelibre', $convocation->getUser()->getPhone(), $connector->getcontent(), $connector->getSender());
            }
        }
        return $smsList;
    }

    private function isConvocation(Convocation $convocation): bool
    {
        return Convocation::CATEGORY_CONVOCATION === $convocation->getCategory();
    }

    private function hasPhone(User $user): bool
    {
        return null !== $user->getPhone();
    }
}
