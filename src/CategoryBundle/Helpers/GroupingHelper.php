<?php
namespace CategoryBundle\Helpers;

use CategoryBundle\Entity\Category;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * This helper groups array of categories into required groups.
 *
 * Class GroupingHelper
 * @package CategoryBundle\Helpers
 */
class GroupingHelper
{
    /**
     * Groups array of categories into groups of parents. Sets keys as parents ID's.
     * If category don't have any parents, then this category is grouped to 0.
     *
     * @param $categories
     * @return ArrayCollection
     */
    public function groupByParent($categories)
    {
        $orderedCategories = new ArrayCollection();
        $orderedCategories->set(0, new ArrayCollection());

        foreach ($categories as $category) {
            /** @var $category Category */
            if ($category->getParent()) {
                $parent = $category->getParent();
                if (!$orderedCategories->get($parent->getId())) {
                    $orderedCategories->set($parent->getId(), new ArrayCollection());
                    $orderedCategories[$parent->getId()]->add($category);
                } else {
                    $orderedCategories[$parent->getId()]->add($category);
                }
            } else {
                $orderedCategories[0]->add($category);
            }
        }

        return $orderedCategories;
    }
}