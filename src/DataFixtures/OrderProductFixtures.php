<?php

namespace App\DataFixtures;

use App\Entity\OrderProduct;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class OrderProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = new Factory();

        for($i = 0; $i < 100; ++$i) {
            $orderId = $i;
            if($i > 20){
                $orderId = rand(1, 20);
            }
            $orderProduct = new OrderProduct();
            $orderProduct->setOrderId($orderId)
                ->setProductId(rand(1, 50))
                ->setQuantity(rand(1, 2));

            $manager->persist($orderProduct);
        }

        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
