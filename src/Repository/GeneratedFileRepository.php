<?php

namespace App\Repository;

use App\Entity\GeneratedFile;
use App\Entity\Sitting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GeneratedFile>
 *
 * @method GeneratedFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method GeneratedFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method GeneratedFile[]    findAll()
 * @method GeneratedFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GeneratedFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GeneratedFile::class);
    }

    public function getGeneratedFileBySitting(Sitting $sitting, $type): ?GeneratedFile
    {
        return $this->findOneBy([
            'type' => $type,
            'sitting' => $sitting,
        ]);
    }
}
