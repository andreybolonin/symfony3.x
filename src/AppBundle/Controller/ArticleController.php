<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Form\ArticleType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ArticleController
 * @package AppBundle\Controller
 */
class ArticleController extends Controller
{
    /**
     * @Route("/article/", name="article_homepage")
     *
     * @param Request $request
     *
     * @return string|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $queryBuilder = $this->getDoctrine()->getRepository(Article::class)->findAllArticle();

        if ($request->query->getAlnum('filter')) {
            $queryBuilder->where('a.name LIKE :name')
                ->setParameter('name', '%' . $request->query->getAlnum('filter') . '%');
        }

        $pagination = $this->get('knp_paginator')->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            5/*limit per page*/
        );

        return $this->render('article/index.html.twig', array('pagination' => $pagination, 'direction' => $request->get('direction'), 'sort' => $request->get('sort')));
    }

    /**
     * @Route("/article/detail/{id}", name="article_detail")
     *
     * @param $id
     *
     * @return string|\Symfony\Component\HttpFoundation\Response
     */
    public function detailAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $article = $em->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('The article does not exist');
        }

        return $this->render('article/detail.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/article/create", name="article_create")
     *
     * @param Request $request
     *
     * @return string|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('article_homepage');
        }

        return $this->render('article/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/article/update/{id}", name="article_update")
     *
     * @param Request $request
     *
     * @return string|\Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $article = $em->getRepository(Article::class)->find($id);

        if ($article) {
            $updateForm = $this->createForm(ArticleType::class, $article);
            $updateForm->handleRequest($request);

            if ($updateForm->isSubmitted() && $updateForm->isValid()) {
                $em->persist($article);
                $em->flush();

                return $this->redirectToRoute('article_homepage');
            }

            return $this->render('article/update.html.twig', array('article' => $article,
                'edit_form' => $updateForm->createView()));
        }

        return $this->redirectToRoute('article_homepage');
    }

    /**
     * @Route("/article/delete/{id}", name="article_delete")
     *
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $article = $em->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('The article does not exist');
        }

        if ($article) {
            $em->remove($article);
            $em->flush();
        }

        return $this->redirectToRoute('article_homepage');
    }
}
