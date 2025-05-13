<?php

namespace App\Repository;

use App\Entity\DA;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DA>
 */
class DARepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DA::class);
    }

    public function searchByFieldsAndMonths(?string $term, ?string $monthDA, ?string $monthBCA, ?string $retardDABCA, ?string $retardLivraison): array
    {
        $qb = $this->createQueryBuilder('d');

        if ($term) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('d.ReferenceDA', ':term'),
                    $qb->expr()->like('d.EtatDA', ':term'),
                    $qb->expr()->like('d.ChantierDepartement', ':term'),
                    $qb->expr()->like('d.Description', ':term'),
                    $qb->expr()->like('d.ReferenceBCA', ':term'),
                )
            )->setParameter('term', '%' . $term . '%');
        }

        if ($monthDA) {
            try {
                $startDA = new \DateTimeImmutable($monthDA . '-01');
                $endDA = $startDA->modify('first day of next month');

                $qb->andWhere('d.DateCreationDA >= :startDA AND d.DateCreationDA < :endDA')
                   ->setParameter('startDA', $startDA)
                   ->setParameter('endDA', $endDA);
            } catch (\Exception $e) {}
        }

        if ($monthBCA) {
            try {
                $startBCA = new \DateTimeImmutable($monthBCA . '-01');
                $endBCA = $startBCA->modify('first day of next month');

                $qb->andWhere('d.CreationBCA >= :startBCA AND d.CreationBCA < :endBCA')
                   ->setParameter('startBCA', $startBCA)
                   ->setParameter('endBCA', $endBCA);
            } catch (\Exception $e) {}
        }
        if ($retardDABCA === '>0') {
            $qb->andWhere('d.RetardDABCA > 0');
        } elseif ($retardLivraison === '0') {
            $qb->andWhere('(d.RetardDABCA = 0 OR d.RetardLivraison IS NULL)');
        }

        if ($retardLivraison !== null && $retardLivraison !== '') {
            if ($retardLivraison === '>0') {
                $qb->andWhere('d.RetardLivraison > 0');
            } elseif ($retardLivraison === '0') {
                $qb->andWhere('(d.RetardLivraison = 0 OR d.RetardLivraison IS NULL)');
            }
        }
        
    

        return $qb->orderBy('d.DateCreationDA', 'DESC')
                  ->getQuery()
                  ->getResult();
    }
}
