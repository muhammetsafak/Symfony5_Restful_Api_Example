<?php

namespace App\DataFixtures;

use App\Entity\Order;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class OrderFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($i = 0; $i < 20; ++$i) {
            $order = new Order();
            $order->setUserId(rand(1, 3));
            $order->setAddress($faker->address);
            $order->setShippingDate(null);
            $manager->persist($order);
        }
        $manager->flush();
    }
}
