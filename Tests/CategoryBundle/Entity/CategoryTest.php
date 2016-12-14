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

        $this->assertEquals('SpecialCaseString',$category->__toString());
    }

    public function testGetExpense()
    {
        $category = $this->em->getRepository('CategoryBundle:Category')->findOneBy(['name' => 'expense']);
        $expenses = $category->getExpense();

        $this->assertCount(2,$expenses);
        $this->assertEquals('expense',$category->getType());
        $this->assertInstanceOf(Expenses::class,$expenses[0]);
        $this->assertInstanceOf(Expenses::class,$expenses[1]);
    }

    public function testGetIncome()
    {
        $category = $this->em->getRepository('CategoryBundle:Category')->findOneBy(['name' => 'income']);
        $income = $category->getIncome();

        $this->assertCount(2,$income);
        $this->assertEquals('income',$category->getType());
        $this->assertInstanceOf(Income::class,$income[0]);
        $this->assertInstanceOf(Income::class,$income[1]);
    }

    public function testGetUser()
    {
        $category = $this->em->getRepository('CategoryBundle:Category')->findOneBy(['name' => 'income']);
        $user = $category->getUser();

        $this->assertEquals('income',$category->getType());
        $this->assertInstanceOf(User::class,$user);
        $this->assertEquals('admin', $user->getUsername());
    }

    public function testIsValid()
    {
        $category = $this->em->getRepository('CategoryBundle:Category')->findOneBy(['name' => 'income']);

        $this->assertEquals('income',$category->getType());
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

        $this->assertEquals($childCategory->getName(),$child->getName());
        $this->assertEquals($childCategory->getValid(),$child->getValid());
        $this->assertEquals($childCategory->getUser(),$child->getUser());
        $this->assertEquals($childCategory->getType(),$child->getType());
        $this->assertEquals($category,$child->getParent());
        $this->assertEquals(1,$category->getChildren()->count());

        $category->removeChild($child);
        $child->setParent(null);
        $this->em->persist($child);
        $this->em->persist($category);
        $this->em->flush();

        $child = $repository->findOneBy(['name' => 'childCategory']);
        $this->assertEquals(null,$child->getParent());
        $this->assertEquals(0,$category->getChildren()->count());
    }

}