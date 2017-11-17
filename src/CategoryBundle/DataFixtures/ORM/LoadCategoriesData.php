<?php

namespace CategoryBundle\DataFixtures\ORM;

use CategoryBundle\Entity\Category;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCategoriesData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        //get user
        $user = $manager->getRepository('MainBundle:User')->findOneBy(['username' => 'admin']);

        $category = new Category();

        $category->setUser($user);
        $category->setType('expense');
        $category->setName('expense');
        $category->setValid('1');

        $IncomeCategory = new Category();

        $IncomeCategory->setUser($user);
        $IncomeCategory->setType('income');
        $IncomeCategory->setName('income');
        $IncomeCategory->setValid('1');

        $manager->persist($category);
        $manager->persist($IncomeCategory);
        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }
}