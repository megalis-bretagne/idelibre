<?php

namespace App\Service\Structure;

use App\Entity\Connector\Exception\ComelusConnectorException;
use App\Entity\Connector\Exception\LsmessageConnectorException;
use App\Entity\Connector\Exception\LsvoteConnectorException;
use App\Entity\Group;
use App\Entity\Structure;
use App\Entity\User;
use App\Security\Password\ResetPassword;
use App\Service\Configuration\ConfigurationManager;
use App\Service\Connector\ComelusConnectorManager;
use App\Service\Connector\LsmessageConnectorManager;
use App\Service\Connector\LsvoteConnectorManager;
use App\Service\EmailTemplate\DefaultTemplateCreator;
use App\Service\Subscription\SubscriptionManager;
use App\Service\Theme\ThemeManager;
use App\Service\User\UserManager;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class StructureCreator
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly EntityManagerInterface $em,
        private readonly ThemeManager $themeManager,
        private readonly ComelusConnectorManager $comelusConnectorManager,
        private readonly LsmessageConnectorManager $lsmessageConnectorManager,
        private readonly LsvoteConnectorManager $lsvoteConnectorManager,
        private readonly DefaultTemplateCreator $defaultTemplateCreator,
        private readonly ConfigurationManager $configurationManager,
        private readonly ResetPassword $resetPassword,
        private readonly SubscriptionManager $subscriptionManager,
    ) {
    }

    /**
     * @throws ComelusConnectorException
     * @throws LsmessageConnectorException
     * @throws ConnectionException
     */
    public function create(Structure $structure, User $user, Group $group = null): ?ConstraintViolationListInterface
    {
        $this->em->getConnection()->beginTransaction();
        $structure->setLegacyConnectionName($this->createLegacyConnexionName($structure->getSuffix()));

        $structure->setGroup($group);
        $this->em->persist($structure);

        $this->addSuffixToUsername($user, $structure->getSuffix());

        $errors = $this->userManager->saveStructureAdmin($user, $structure);
        if (!empty($errors)) {
            $this->em->getConnection()->rollBack();

            return $errors;
        }

        $this->initConfig($structure);
        $this->em->flush();

        $subscription = $this->subscriptionManager->add($user);
        $this->subscriptionManager->save($subscription);

        $this->em->getConnection()->commit();

        $this->resetPassword->sendEmailDefinePassword($user);

        return null;
    }

    private function addSuffixToUsername(User $user, string $suffix): void
    {
        $usernameWithSuffix = $user->getUsername() . '@' . $suffix;
        $user->setUsername($usernameWithSuffix);
    }

    private function createLegacyConnexionName(string $suffix): string
    {
        return strtolower(preg_replace('/[^A-Za-z0-9 ]/', '', $suffix));
    }

    /**
     * @throws ComelusConnectorException
     * @throws LsmessageConnectorException
     * @throws LsvoteConnectorException
     */
    private function initConfig(Structure $structure)
    {
        $this->themeManager->createStructureRootNode($structure);
        $this->comelusConnectorManager->createConnector($structure);
        $this->lsmessageConnectorManager->createConnector($structure);
        $this->lsvoteConnectorManager->createConnector($structure);
        $this->defaultTemplateCreator->initDefaultTemplates($structure);
        $this->configurationManager->createConfiguration($structure);
    }
}
