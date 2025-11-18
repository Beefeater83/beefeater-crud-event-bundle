<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Repository;

use Beefeater\CrudEventBundle\Exception\ResourceNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

abstract class AbstractRepository extends ServiceEntityRepository
{
    private string $entityClass;

    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
        $this->entityClass = $entityClass;
    }

    public function countAll(): int
    {
        return $this->count([]);
    }

    protected function applyCriteria(QueryBuilder $qb, array $criteria): void
    {
        foreach ($criteria as $field => $filters) {
            foreach ($filters as $operator => $value) {
                $paramName = $field . '_' . $operator;

                switch ($operator) {
                    case 'eq':
                        if ($value !== null) {
                            $qb->andWhere("entity.$field = :$paramName");
                        } else {
                            $qb->andWhere("entity.$field IS NULL");
                        }
                        break;
                    case 'like':
                        $qb->andWhere("entity.$field LIKE :$paramName");
                        $value = '%' . $value . '%';
                        break;
                    case 'gte':
                        $qb->andWhere("entity.$field >= :$paramName");
                        break;
                    case 'lte':
                        $qb->andWhere("entity.$field <= :$paramName");
                        break;
                    case 'gt':
                        $qb->andWhere("entity.$field > :$paramName");
                        break;
                    case 'lt':
                        $qb->andWhere("entity.$field < :$paramName");
                        break;
                    case 'neq':
                        if ($value !== null) {
                            $qb->andWhere("entity.$field <> :$paramName");
                        } else {
                            $qb->andWhere("entity.$field IS NOT NULL");
                        }
                        break;
                    case 'in':
                        $hasNull = false;
                        $nonNullValues = [];

                        foreach ($value as $v) {
                            if ($v === null) {
                                $hasNull = true;
                            } else {
                                $nonNullValues[] = $v;
                            }
                        }

                        if (!empty($nonNullValues) && $hasNull) {
                            $qb->andWhere("(entity.$field IN (:$paramName) OR entity.$field IS NULL)");
                            $qb->setParameter($paramName, $nonNullValues);
                        } elseif (!empty($nonNullValues)) {
                            $qb->andWhere($qb->expr()->in("entity.$field", ":$paramName"));
                            $qb->setParameter($paramName, $nonNullValues);
                        } else {
                            $qb->andWhere("entity.$field IS NULL");
                        }
                        break;
                    case 'nin':
                        $hasNull = false;
                        $nonNullValues = [];

                        foreach ($value as $v) {
                            if ($v === null) {
                                $hasNull = true;
                            } else {
                                $nonNullValues[] = $v;
                            }
                        }

                        if (!empty($nonNullValues) && $hasNull) {
                            $qb->andWhere("(entity.$field NOT IN (:$paramName) AND entity.$field IS NOT NULL)");
                            $qb->setParameter($paramName, $nonNullValues);
                        } elseif (!empty($nonNullValues)) {
                            $qb->andWhere($qb->expr()->notIn("entity.$field", ":$paramName"));
                            $qb->setParameter($paramName, $nonNullValues);
                        } else {
                            $qb->andWhere("entity.$field IS NOT NULL");
                        }
                        break;
                    default:
                        throw new \InvalidArgumentException("Unknown operator: $operator");
                }
                if ($value !== null) {
                    $type = $value instanceof Uuid ? 'uuid' : null;
                    $qb->setParameter($paramName, $value, $type);
                }
            }
        }
    }

    public function countByCriteria(array $criteria = []): int
    {
        $qb = $this->createQueryBuilder('entity')
            ->select('COUNT(entity.id)');

        $this->applyCriteria($qb, $criteria);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findPaginated(array $criteria = [], array $orderBy = [], int $offset = 0, int $limit = 25): array
    {
        $qb = $this->createQueryBuilder('entity');

        $this->applyCriteria($qb, $criteria);

        foreach ($orderBy as $field => $direction) {
            $qb->addOrderBy("entity.$field", $direction);
        }

        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function find(mixed $id, LockMode|int|null $lockMode = null, int|null $lockVersion = null): object
    {
        $entity = parent::find($id, $lockMode, $lockVersion);
        if (!$entity) {
            throw new ResourceNotFoundException($this->entityClass, (string) $id);
        }
        return $entity;
    }
}
