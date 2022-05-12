<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function add(Order $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function remove(Order $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    public function update(Order $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function findByCustomerOrder(int $customerId, ?int $orderId = null)
    {
        $builder = $this->createQueryBuilder('o')
            ->select(['o', 'op', 'p'])
            ->andWhere('o.userId = :userId')
            ->setParameter('userId', $customerId);
        if($orderId !== null){
            $builder->andWhere('o.id = :order_id')
                ->setParameter('order_id', $orderId);
        }
        $builder->leftJoin('App:OrderProduct', 'op', Join::WITH, 'o.id = op.orderId');
        $builder->leftJoin('App:Product', 'p', Join::WITH, 'op.productId = p.id');
        $builder->orderBy('o.id', 'ASC');

        $query = $builder->getQuery();
        return $query->execute();
    }

    public function checkCustomerOrder(int $customerId, int $orderId)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.userId = :user_id')
            ->andWhere('o.id = :order_id')
            ->setParameter('order_id', $orderId)
            ->setParameter('user_id', $customerId)
            ->getQuery()
            ->execute();
    }

//    /**
//     * @return Order[] Returns an array of Order objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Order
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
