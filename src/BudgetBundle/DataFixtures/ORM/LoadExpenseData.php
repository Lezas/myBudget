<?php

namespace BudgetBundle\DataFixtures\ORM;

use BudgetBundle\Entity\Expenses;
use CategoryBundle\Entity\Category;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MainBundle\Entity\User;

/**
 * Class LoadExpenseData
 */
class LoadExpenseData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        //get user
        $user = $manager->getRepository('MainBundle:User')->findOneBy(['username' => 'admin']);
        $category = $manager->getRepository('CategoryBundle:Category')->findOneBy(['name' => 'expense']);

        $expense = new Expenses();
        $expense->setUser($user);
        $expense->setDateTime(new \DateTime('2016-01-05 23:33'));
        $expense->setMoney(99.99);
        $expense->setName('maistas');

        $manager->persist($expense);

        $expense = new Expenses();
        $expense->setUser($user);
        $expense->setDateTime(new \DateTime('2016-01-06 23:33'));
        $expense->setMoney(89.99);
        $expense->setName('kitos islaidos');

        $manager->persist($expense);
        $manager->flush();

        //With categories

        $expense = new Expenses();
        $expense->setUser($user);
        $expense->setDateTime(new \DateTime('2016-01-15 23:33'));
        $expense->setMoney(199.99);
        $expense->setName('maistas2');
        $expense->setCategory($category);

        $manager->persist($expense);
        $manager->flush();

        $expense = new Expenses();
        $expense->setUser($user);
        $expense->setDateTime(new \DateTime('2016-01-16 00:33'));
        $expense->setMoney(0.65);
        $expense->setName('maistas3');
        $expense->setCategory($category);

        $manager->persist($expense);
        $manager->flush();
    }

    public function getOrder()
    {
        return 3;
    }
}