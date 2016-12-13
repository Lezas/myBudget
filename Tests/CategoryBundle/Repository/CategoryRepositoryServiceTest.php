<?php

namespace Tests\CategoryBundle\Repository;

use BudgetBundle\Repository\BudgetRepositoryService;
use CategoryBundle\Entity\Category;
use CategoryBundle\Repository\CategoryRepositoryService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;

class CategoryRepositoryServiceTest extends WebTestCase
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

    public function testGetIncomeCategories()
    {
        $user = $this->em->getRepository('MainBundle:User')->findOneBy(['username' => 'admin']);
        $catRepService = new CategoryRepositoryService(static::$kernel->getContainer()
            ->get('doctrine'));

        $categories = $catRepService->getAllUserIncomeCategories($user);
        /** @var Category $category */
        $category = $categories[0];
        $this->assertCount(1, $categories);
        $this->assertEquals('income',$category->getName());
    }

    public function testGetExpenseCategories()
    {
        $user = $this->em->getRepository('MainBundle:User')->findOneBy(['username' => 'admin']);
        $catRepService = new CategoryRepositoryService(static::$kernel->getContainer()
            ->get('doctrine'));

        $categories = $catRepService->getAllUserExpenseCategories($user);
        /** @var Category $category */
        $category = $categories[0];
        $this->assertCount(1, $categories);
        $this->assertEquals('expense',$category->getName());
    }

}