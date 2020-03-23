<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function loadUserByUsername(string $identifier): ?User
    {
        $qb = $this->createQueryBuilder('u');

        return $qb
            ->where($qb->expr()->eq('u.identificationNumber', ':identificationNumber'))
            ->orWhere($qb->expr()->eq('u.emailAddress', ':emailAddress'))
            ->setParameter('identificationNumber', User::normalizeIdentificationNumber($identifier))
            ->setParameter('emailAddress', User::normalizeEmailAddress($identifier))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return ArrayCollection
     */
    public function findByFilters(array $formData)
    {
        $qb = $this->createQueryBuilder('u');

        $skillsQueries = [];
        foreach (array_values($formData['volunteerSkills']) as $key => $skill) {
            $skillsQueries[] = sprintf('CONTAINS(u.skillSet, ARRAY(:skill%d)) = TRUE', $key);
            $qb->setParameter(sprintf('skill%d', $key), $skill);
        }

        if (0 < $formData['organizations']->count()) {
            $qb->andWhere('u.organization IN (:organisations)')->setParameter('organisations', $formData['organizations']);
        }

        if ($formData['volunteerEquipped']) {
            $qb->andWhere('u.fullyEquipped = TRUE');
        }

        if ($formData['volunteerHideVulnerable']) {
            $qb->andWhere('u.vulnerable = FALSE');
        }

        if (0 < count($formData['volunteerSkills'])) {
            $skillsQueries = [];
            foreach (array_values($formData['volunteerSkills']) as $key => $skill) {
                $skillsQueries[] = sprintf('CONTAINS(u.skillSet, ARRAY(:skill%d)) = TRUE', $key);
                $qb->setParameter(sprintf('skill%d', $key), $skill);
            }

            $qb->andWhere($qb->expr()->orX(...$skillsQueries));
        }

        return $qb->getQuery()->getResult();
    }
}
