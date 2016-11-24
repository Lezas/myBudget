<?php

namespace CategoryBundle\Controller;

use CategoryBundle\Entity\Category;
use CategoryBundle\Form\Type\CategoryType;
use CategoryBundle\Helpers\GroupingHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use MainBundle\Form\Type\ConfirmType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends Controller
{
    /**
     * @Route("/category/new", name="new_category")
     *
     */
    public function newCategoryAction(Request $request)
    {
        $user = $this->getUser();
        
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category, ['user' => $user]);

        $form->handleRequest($request);

        if ($form->isValid()){
            $category->setUser($this->getUser());
            
            $this->getDoctrine()->getEntityManager()->persist($category);
            $this->getDoctrine()->getEntityManager()->flush();
            return $this->redirectToRoute('category_list');
        }

        return $this->render('@Category/category/newCategory.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/category/all", name="category_list")
     *
     */
    public function listCategoryAction(Request $request)
    {
        $user = $this->getUser();
        
        $categoryRepository = $this->getDoctrine()->getEntityManager()->getRepository('CategoryBundle:Category');
        
        $categories = $categoryRepository->findBy(['user' => $user]);

        $groupingHelper = new GroupingHelper();

        $groupedData = $groupingHelper->groupByParent($categories);
        $parentCategories = new ArrayCollection();

        foreach ($groupedData as $key => $parentID) {
            if ($key != 0){
                $parent = $categoryRepository->find($key);
                $parentCategories->set($key, $parent);
            }
        }

        return $this->render('@Category/category/categoryList.html.twig',[
            'groupedCategories' => $groupedData,
            'parentCategories' => $parentCategories,
        ]);
    }

    /**
     * @Route("category/{id}", name="edit_category")
     *
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editCategoryAction(Request $request, $id)
    {
        if (!$this->isLogged()){
            return $this->redirectToRoute('fos_user_security_login');
        } else {

            $categoryRepository = $this->getDoctrine()->getRepository('CategoryBundle:Category');
            $category = $categoryRepository->findOneBy(['id'=>$id]);
            $user = $this->getUser();
            $categoryUser = $category->getUser();
            
            if ($categoryUser !== $user || $category === null) {
                throw $this->createNotFoundException('The category does not exist');
            }

            $form = $this->createForm(CategoryType::class, $category, ['user' => $user]);
            $form->handleRequest($request);

            if ($form->isValid()){
                $this->getDoctrine()->getEntityManager()->persist($category);
                $this->getDoctrine()->getEntityManager()->flush();

                $this->addFlash(
                    'notice',
                    'Your category has been saved!'
                );
                return $this->redirectToRoute('category_list');
            }

            return $this->render('@Category/category/newCategory.html.twig',[
                'form' => $form->createView(),
            ]);
        }
    }

    /**
     * @Route("category/{id}/delete", name="delete_category")
     *
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteCategoryAction(Request $request, $id)
    {
        if (!$this->isLogged()){
            return $this->redirectToRoute('fos_user_security_login');
        } else {

            $categoryRepository = $this->getDoctrine()->getRepository('CategoryBundle:Category');

            /** @var $category Category*/
            $category = $categoryRepository->findOneBy(['id'=>$id]);

            $user = $this->getUser();
            $categoryUser = $category->getUser();

            if ($categoryUser !== $user || $category === null) {
                throw $this->createNotFoundException('The category does not exist');
            }

            /** @var $em EntityManager */
            $em = $this->getDoctrine()->getManager();

            dump($category->getExpense()->count());
            dump($category->getChildren()->count());
            dump($category->getParent());

            if ($category->getExpense()->count() == 0 && $category->getChildren()->count() == 0 && $category->getIncome()->count() == 0) {
                $em->remove($category);
                $em->flush();
                $this->addFlash(
                    'notice',
                    'Category' . $category->getName() . ' has been deleted!'
                );
            } else {
                $this->addFlash(
                    'notice',
                    'Category' . $category->getName() . ' has relations with budget or has children categories.'
                );
            }

            return $this->redirect($this->generateUrl('category_list'));
        }
    }

    /**
     * @return bool - True if user is logged in, false if not.
     */
    private function isLogged()
    {
        $securityContext = $this->container->get('security.authorization_checker');

        if (!$securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {

            return false;
        } else {

            return true;
        }
    }

}
