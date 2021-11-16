<?php

namespace App\Service\Structure;

use App\Entity\Connector\Exception\ComelusConnectorException;
use App\Entity\Connector\Exception\LsmessageConnectorException;
use App\Entity\Group;
use App\Entity\Structure;
use App\Entity\User;
use App\Service\Configuration\ConfigurationManager;
use App\Service\Connector\ComelusConnectorManager;
use App\Service\Connector\LsmessageConnectorManager;
use App\Service\EmailTemplate\DefaultTemplateCreator;
use App\Service\Theme\ThemeManager;
use App\Service\User\UserManager;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class StructureCreator
{
    public function __construct(
        private UserManager $userManager,
        private EntityManagerInterface $em,
        private ThemeManager $themeManager,
        private ComelusConnectorManager $comelusConnectorManager,
        private LsmessageConnectorManager $lsmessageConnectorManager,
        private DefaultTemplateCreator $defaultTemplateCreator,
        private ConfigurationManager $configurationManager
    ) {
    }

    /**
     * @throws ComelusConnectorException
     * @throws LsmessageConnectorException
     * @throws ConnectionException
     */
    public function create(Structure $structure, User $user, string $plainPassword, Group $group = null): ?ConstraintViolationListInterface
    {
        $this->em->getConnection()->beginTransaction();
        $structure->setLegacyConnectionName($this->createLegacyConnexionName($structure->getSuffix()));

        $structure->setGroup($group);
        $this->em->persist($structure);

        $this->addSuffixToUsername($user, $structure->getSuffix());
        $errors = $this->userManager->saveStructureAdmin($user, $plainPassword, $structure);

        if (!empty($errors)) {
            $this->em->getConnection()->rollBack();

            return $errors;
        }

        $this->initConfig($structure);
        $this->em->flush();
        $this->em->getConnection()->commit();

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
     */
    private function initConfig(Structure $structure)
    {
        $this->themeManager->createStructureRootNode($structure);
        $this->comelusConnectorManager->createConnector($structure);
        $this->lsmessageConnectorManager->createConnector($structure);
        $this->defaultTemplateCreator->initDefaultTemplates($structure);
        $this->configurationManager->createConfiguration($structure);
    }
}
