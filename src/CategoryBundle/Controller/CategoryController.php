<?php

namespace CategoryBundle\Controller;

use CategoryBundle\Entity\Category;
use CategoryBundle\Form\Type\CategoryType;
use CategoryBundle\Helpers\GroupingHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class CategoryController
 */
class CategoryController extends Controller
{
    /**
     * @Route("/category/new/{category}", name="new_category")
     *
     * @param Category $category
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Security("user.getId() == category.getUser().getId()")
     */
    public function newCategoryAction(Category $category = null, Request $request)
    {
        $user = $this->getUser();

        if ($category == null) {
            $category = new Category();
        }

        $parents = $this->getDoctrine()->getRepository('CategoryBundle:Category')->findBy(['user' => $user]);

        $form = $this->createForm(CategoryType::class, $category, ['parents' => $parents]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $category->setUser($user);
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('category_list');
        }

        return $this->render('@Category/category/newCategory.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/category/all", name="category_list")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listCategoryAction()
    {
        $user = $this->getUser();

        $categoryRepository = $this->getDoctrine()->getManager()->getRepository('CategoryBundle:Category');
        $categories = $categoryRepository->findBy(['user' => $user]);
        $groupingHelper = new GroupingHelper();

        $groupedData = $groupingHelper->groupByParent($categories);
        $parentCategories = new ArrayCollection();

        foreach ($groupedData as $key => $parentID) {
            if ($key != 0) {
                $parent = $categoryRepository->find($key);
                $parentCategories->set($key, $parent);
            }
        }

        return $this->render('@Category/category/categoryList.html.twig', [
            'groupedCategories' => $groupedData,
            'parentCategories' => $parentCategories,
        ]);
    }

    /**
     * @Route("category/{category}/delete", name="delete_category")
     *
     * @Security("user.getId() == category.getUser().getId()")
     *
     * @param Category $category
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteCategoryAction(Category $category)
    {
        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        if ($category->canDelete()) {
            $em->remove($category);
            $em->flush();
            $this->addFlash(
                'notice',
                'Category '.$category->getName().' has been deleted!'
            );
        } else {
            $this->addFlash(
                'notice',
                'Category '.$category->getName().' has relations with budget or has children categories.'
            );
        }

        return $this->redirect($this->generateUrl('category_list'));
    }
}
