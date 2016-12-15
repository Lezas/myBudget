<?php

namespace MainBundle\Entity;

use BudgetBundle\Entity\Expenses;
use BudgetBundle\Entity\Income;
use CategoryBundle\Entity\Category;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    public function __construct()
    {
        parent::__construct();
        // your own logic
        $this->Income = new ArrayCollection();
        $this->Expense = new ArrayCollection();
        $this->Category = new ArrayCollection();
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="BudgetBundle\Entity\Income", mappedBy="user")
     * @var Income[]|ArrayCollection
     */
    protected $Income;

    /**
     * @ORM\OneToMany(targetEntity="BudgetBundle\Entity\Expenses", mappedBy="user")
     * @var Expenses[]|ArrayCollection
     */
    protected $Expense;

    /**
     * @ORM\OneToMany(targetEntity="CategoryBundle\Entity\Category", mappedBy="user")
     * @var Category[]|ArrayCollection
     */
    protected $Category;

    /**
     * Add income
     *
     * @param Income $income
     *
     * @return User
     */
    public function addIncome(Income $income)
    {
        $this->Income[] = $income;
        $income->setUser($this);

        return $this;
    }

    /**
     * Remove income
     *
     * @param Income $income
     */
    public function removeIncome(Income $income)
    {
        $this->Income->removeElement($income);
        $income->setUser(null);
    }

    /**
     * Get income
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIncome()
    {
        return $this->Income;
    }

    /**
     * Add expense
     *
     * @param \BudgetBundle\Entity\Expenses $expense
     *
     * @return User
     */
    public function addExpense(Expenses $expense)
    {
        $this->Expense[] = $expense;
        $expense->setUser($this);

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
        $expense->setUser(null);
    }


    /**
     * Get expense
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getExpense()
    {
        return $this->Expense;
    }

    /**
     * Add category
     *
     * @param \CategoryBundle\Entity\Category $category
     *
     * @return User
     */
    public function addCategory(Category $category)
    {
        $this->Category[] = $category;
        $category->setUser($this);

        return $this;
    }

    /**
     * Remove category
     *
     * @param \CategoryBundle\Entity\Category $category
     */
    public function removeCategory(Category $category)
    {
        $this->Category->removeElement($category);
        $category->setUser(null);
    }

    /**
     * Get Category
     *
     * @return \CategoryBundle\Entity\Category[]|ArrayCollection
     */
    public function getCategories()
    {
        return $this->Category;
    }

}
