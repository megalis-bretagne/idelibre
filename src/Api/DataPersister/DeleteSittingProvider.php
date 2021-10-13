<?php
/*
namespace App\Api\DataPersister;



use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;

class DeleteSittingProvider implements ContextAwareDataPersisterInterface
{

    public function __construct(ContextAwareDataPersisterInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function supports($data, array $context = []): bool
    {
        dump("DeleteSittingProvider");
        dump($data);
        dump($context);
        return $this->decorated->supports($data, $context);
    }

    public function persist($data, array $context = [])
    {
        $result = $this->decorated->persist($data, $context);

        //do other stuff

        return $result;
    }

    public function remove($data, array $context = [])
    {
        return $this->decorated->remove($data, $context);
    }
}
*/
