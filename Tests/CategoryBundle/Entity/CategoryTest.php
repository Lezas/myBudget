<?php

namespace Tests\CategoryBundle\Entity;

use BudgetBundle\Entity\Expenses;
use BudgetBundle\Entity\Income;
use BudgetBundle\Repository\BudgetRepositoryService;
use CategoryBundle\Entity\Category;
use CategoryBundle\Repository\CategoryRepositoryService;
use Doctrine\Common\Collections\ArrayCollection;
use MainBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;

class CategoryTest extends WebTestCase
{
    protected static $application;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    protected function setUp()
    {
        self::runCommand('doctrine:database:drop --force --env=test');
        self::runCommand('doctrine:database:create');
        self::runCommand('doctrine:schema:update --force');
        self::runCommand('doctrine:fixtures:load');

        self::bootKernel();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null; // avoid memory leaks
    }

    protected static function runCommand($command)
    {
        $command = sprintf('%s --quiet', $command);

        return self::getApplication()->run(new StringInput($command));
    }

    protected static function getApplication()
    {
        if (null === self::$application) {
            $client = static::createClient();

            self::$application = new Application($client->getKernel());
            self::$application->setAutoExit(false);
        }

        return self::$application;
    }

    public function testToString()
    {
        $category = new Category();
        $category->setName('SpecialCaseString');

        $this->assertEquals('SpecialCaseString', $category->__toString());
    }

    public function testGetExpense()
    {
        $category = $this->em->getRepository('CategoryBundle:Category')->findOneBy(['name' => 'expense']);
        $expenses = $category->getExpense();

        $this->assertCount(2, $expenses);
        $this->assertEquals('expense', $category->getType());
        $this->assertInstanceOf(Expenses::class, $expenses[0]);
        $this->assertInstanceOf(Expenses::class, $expenses[1]);
    }

    public function testGetIncome()
    {
        $category = $this->em->getRepository('CategoryBundle:Category')->findOneBy(['name' => 'income']);
        $income = $category->getIncome();

        $this->assertCount(2, $income);
        $this->assertEquals('income', $category->getType());
        $this->assertInstanceOf(Income::class, $income[0]);
        $this->assertInstanceOf(Income::class, $income[1]);
        $this->assertEquals($category, $income[0]->getCategory());
        $this->assertEquals($category, $income[0]->getCategory());
    }

    public function testGetUser()
    {
        $category = $this->em->getRepository('CategoryBundle:Category')->findOneBy(['name' => 'income']);
        $user = $category->getUser();

        $this->assertEquals('income', $category->getType());
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('admin', $user->getUsername());
    }

    public function testIsValid()
    {
        $category = $this->em->getRepository('CategoryBundle:Category')->findOneBy(['name' => 'income']);

        $this->assertEquals('income', $category->getType());
        $this->assertTrue($category->getValid());
    }

    public function testChildrenFunctionality()
    {
        $repository = $this->em->getRepository('CategoryBundle:Category');
        $category = $repository->findOneBy(['name' => 'income']);

        $childCategory = new Category();
        $childCategory->setName('childCategory');
        $childCategory->setValid('1');
        $childCategory->setUser($category->getUser());
        $childCategory->setType('income');
        $childCategory->setParent($category);

        $category->addChild($childCategory);

        $this->em->persist($category);
        $this->em->flush();

        $child = $repository->findOneBy(['name' => 'childCategory']);

        $this->assertEquals($childCategory->getName(), $child->getName());
        $this->assertEquals($childCategory->getValid(), $child->getValid());
        $this->assertEquals($childCategory->getUser(), $child->getUser());
        $this->assertEquals($childCategory->getType(), $child->getType());
        $this->assertEquals($category, $child->getParent());
        $this->assertEquals(1, $category->getChildren()->count());

        $category->removeChild($child);
        $child->setParent(null);
        $this->em->persist($child);
        $this->em->persist($category);
        $this->em->flush();

        $child = $repository->findOneBy(['name' => 'childCategory']);
        $this->assertEquals(null, $child->getParent());
        $this->assertEquals(0, $category->getChildren()->count());
    }

    public function testBudgetFunctionality()
    {
        $repository = $this->em->getRepository('CategoryBundle:Category');
        $category = $repository->findOneBy(['name' => 'income']);

        $incomeCollection = $category->getIncome();

        $this->assertEquals(2, $incomeCollection->count());

        $income = new Income();
        $income->setName('testingCategories');
        $income->setDateTime(new \DateTime('now'));
        $income->setMoney(95);
        $income->setUser($category->getUser());

        $category->addIncome($income);
        $this->em->persist($category);
        $this->em->flush();

        $incomeCollection = $category->getIncome();

        $this->assertEquals(3, $incomeCollection->count());

        $income = $this->em->getRepository('BudgetBundle:Income')->findOneBy(['name' => 'testingCategories']);
        $this->assertEquals($category, $income->getCategory());

        $category->removeIncome($income);

        $this->em->persist($category);
        $this->em->persist($income);
        $this->em->flush();

        $income = $this->em->getRepository('BudgetBundle:Income')->findOneBy(['name' => 'testingCategories']);
        $this->assertEquals(null, $income->getCategory());
        $this->assertEquals(2, $incomeCollection->count());
        //------------
        $category = $repository->findOneBy(['name' => 'expense']);

        $expenseCollection = $category->getExpense();

        $this->assertEquals(2, $expenseCollection->count());

        $expense = new Expenses();
        $expense->setName('testingCategories');
        $expense->setDateTime(new \DateTime('now'));
        $expense->setMoney(95);
        $expense->setUser($category->getUser());

        $category->addExpense($expense);
        $this->em->persist($category);
        $this->em->flush();

        $expenseCollection = $category->getExpense();

        $this->assertEquals(3, $expenseCollection->count());

        $expense = $this->em->getRepository('BudgetBundle:Expenses')->findOneBy(['name' => 'testingCategories']);
        $this->assertEquals($category, $expense->getCategory());

        $category->removeExpense($expense);

        $this->em->persist($category);
        $this->em->persist($expense);
        $this->em->flush();

        $expense = $this->em->getRepository('BudgetBundle:Expenses')->findOneBy(['name' => 'testingCategories']);
        $this->assertEquals(null, $expense->getCategory());
        $this->assertEquals($category->getUser(), $expense->getUser());
        $this->assertEquals(2, $expenseCollection->count());
    }

}