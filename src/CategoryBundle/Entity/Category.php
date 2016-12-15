<?php

namespace CategoryBundle\Entity;

use BudgetBundle\Entity\Expenses;
use BudgetBundle\Entity\Income;
use Doctrine\Common\Collections\ArrayCollection;
use MainBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Category
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="CategoryBundle\Repository\CategoryRepository")
 */
class Category
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="string", length=50)
     */
    private $name;


    /**
     * @var string
     *
     * @ORM\Column(name="Valid", type="string", length=255)
     */
    private $valid;

    /**
     * @OneToMany(targetEntity="Category", mappedBy="parent", cascade={"persist"}))
     */
    private $children;

    /**
     * @ManyToOne(targetEntity="Category", inversedBy="children")
     * @JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    /**
     * @ORM\ManyToOne(targetEntity = "MainBundle\Entity\User", inversedBy = "CategoryUser")
     * @ORM\JoinColumn(name = "user_id", referencedColumnName = "id")
     * @var UserInterface
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="BudgetBundle\Entity\Expenses", mappedBy="category", cascade={"persist"})
     * @var Expenses[]|ArrayCollection
     */
    protected $Expense;

    /**
     * @ORM\OneToMany(targetEntity="BudgetBundle\Entity\Income", mappedBy="category", cascade={"persist"})
     * @var Income[]|ArrayCollection
     */
    protected $Income;

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="string", length=255)
     */
    protected $type;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * Set valid
     *
     * @param string $valid
     *
     * @return Category
     */
    public function setValid($valid)
    {
        $this->valid = $valid;

        return $this;
    }

    /**
     * Get valid
     *
     * @return string
     */
    public function getValid()
    {
        return (boolean)$this->valid;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Category
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->Expense = new ArrayCollection();
        $this->Income = new ArrayCollection();
    }

    /**
     * Add child
     *
     * @param \CategoryBundle\Entity\Category $child
     *
     * @return Category
     */
    public function addChild(\CategoryBundle\Entity\Category $child)
    {
        $this->children[] = $child;
        $child->setParent($this);
        return $this;
    }

    /**
     * Remove child
     *
     * @param \CategoryBundle\Entity\Category $child
     */
    public function removeChild(\CategoryBundle\Entity\Category $child)
    {
        $this->children->removeElement($child);
        $child->setParent(null);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \CategoryBundle\Entity\Category $parent
     *
     * @return Category
     */
    public function setParent(\CategoryBundle\Entity\Category $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \CategoryBundle\Entity\Category
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set user
     *
     * @param User|UserInterface $user
     * @return Category
     */
    public function setUser(UserInterface $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User|UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add expense
     *
     * @param \BudgetBundle\Entity\Expenses $expense
     *
     * @return Category
     */
    public function addExpense(Expenses $expense)
    {
        $this->Expense[] = $expense;
        $expense->setCategory($this);
        return $this;
    }

    /**
     * Remove expense
     *
     * @param \BudgetBundle\Entity\Expenses $expense
     */
    public function removeExpense(Expenses $expense)
    {
        $this->Expense->removeElement($expense);
        $expense->setCategory(null);
    }

    /**
     * Get expense
     *
     */
    public function getExpense()
    {
        return $this->Expense;
    }

    /**
     * Add income
     *
     * @param \BudgetBundle\Entity\Income $income
     *
     * @return Category
     */
    public function addIncome(Income $income)
    {
        $this->Income[] = $income;
        $income->setCategory($this);
        return $this;
    }

    /**
     * Remove income
     *
     * @param \BudgetBundle\Entity\Income $income
     */
    public function removeIncome(Income $income)
    {
        $this->Income->removeElement($income);
        $income->setCategory(null);
    }

    /**
     * Get income
     *
     */
    public function getIncome()
    {
        return $this->Income;
    }

    /**
     * Get name of category
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
