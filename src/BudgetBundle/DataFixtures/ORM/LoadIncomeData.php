<?php
/**
 * Created by PhpStorm.
 * User: Lezas
 * Date: 2016-12-13
 * Time: 22:04
 */

namespace BudgetBundle\DataFixtures\ORM;

use BudgetBundle\Entity\Expenses;
use BudgetBundle\Entity\Income;
use CategoryBundle\Entity\Category;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MainBundle\Entity\User;

class LoadIncomeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        //get user
        $user = $manager->getRepository('MainBundle:User')->findOneBy(['username' => 'admin']);

        $incomeCategory = $manager->getRepository('CategoryBundle:Category')->findOneBy(['name' => 'income']);

        $expense = new Income();
        $expense->setUser($user);
        $expense->setDateTime(new \DateTime('2016-01-06 23:33'));
        $expense->setMoney(109.99);
        $expense->setName('YT');

        $manager->persist($expense);

        $expense = new Income();
        $expense->setUser($user);
        $expense->setDateTime(new \DateTime('2016-01-08 17:33'));
        $expense->setMoney(50);
        $expense->setName('MamosPinigai');

        $manager->persist($expense);
        $manager->flush();

        //With categories

        $expense = new Income();
        $expense->setUser($user);
        $expense->setDateTime(new \DateTime('2016-01-15 23:33'));
        $expense->setMoney(199.99);
        $expense->setName('Alga');
        $expense->setCategory($incomeCategory);

        $manager->persist($expense);
        $manager->flush();

        $expense = new Income();
        $expense->setUser($user);
        $expense->setDateTime(new \DateTime('2016-01-16 00:33'));
        $expense->setMoney(65.23);
        $expense->setName('uz atostogas');
        $expense->setCategory($incomeCategory);

        $manager->persist($expense);
        $manager->flush();
    }

    public function getOrder()
    {
        return 3;
    }
}