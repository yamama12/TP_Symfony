<?php

namespace App\Controller;

use App\Entity\Articles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThan;
use App\Form\ArticleType;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="articless_list")
     */
    public function index(EntityManagerInterface $entityManager): Response
    {
        $articless = $entityManager->getRepository(Articles::class)->findAll();

        return $this->render('articless/index.html.twig', [
            'articless' => $articless
        ]);
    }

    /**
     * @Route("/articless/save", name="articless_save", methods={"GET"})
     */
    public function save(EntityManagerInterface $entityManager): Response
    {
        $articlessData = [
            ['Articles 1', 1000],
            ['Articles 2', 500],
            ['Articles 3', 7000]
        ];

        foreach ($articlessData as $data) {
            $articless = new Articles();
            $articless->setNom($data[0]);
            $articless->setPrix($data[1]);
            $entityManager->persist($articless);
        }

        $entityManager->flush();

        return $this->redirectToRoute('articless_list');
    }

   /**
* @Route("/articless/new", name="new_article")
* Method({"GET", "POST"})
*/
public function new(Request $request) {
    $article = new Articles();
    $form = $this->createForm(ArticleType::class,$article);
    $form->handleRequest($request);
    if($form->isSubmitted() && $form->isValid()) {
    $article = $form->getData();
    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->persist($article);
    $entityManager->flush();
    return $this->redirectToRoute('article_list');
    }
    return $this->render('articless/new.html.twig',['form' => $form->createView()]);
    }

    /**
 * @Route("/articless/{id}", name="articless_show", methods={"GET"})
 */
public function show(int $id, EntityManagerInterface $entityManager): Response
{
    $articless = $entityManager->getRepository(Articles::class)->find($id);
    
    if (!$articless) {
        throw $this->createNotFoundException('Article non trouvé');
    }

    return $this->render('articless/show.html.twig', [
        'articless' => $articless
    ]);
}

/**
 * @Route("/articless/edit/{id}", name="edit_article", methods={"GET", "POST"})
 */
public function edit(Request $request, $id, EntityManagerInterface $entityManager): Response
{
    $article = $entityManager->getRepository(Articles::class)->find($id);

    if (!$article) {
        throw $this->createNotFoundException('Article non trouvé avec l\'ID ' . $id);
    }

    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        return $this->redirectToRoute('articless_list');
    }

    return $this->render('articless/edit.html.twig', [
        'form' => $form->createView(),
    ]);
}

/**
 * @Route("/articless/delete/{id}", name="delete_articless", methods={"POST"})
 */
public function delete(Request $request, int $id, EntityManagerInterface $entityManager): Response
{
    $article = $entityManager->getRepository(Articles::class)->find($id);
    
    if (!$article) {
        throw $this->createNotFoundException('Article non trouvé');
    }

    $submittedToken = $request->request->get('_token');
    
    if (!$this->isCsrfTokenValid('delete'.$article->getId(), $submittedToken)) {
        throw $this->createAccessDeniedException('Token CSRF invalide');
    }

    $entityManager->remove($article);
    $entityManager->flush();
    
    $this->addFlash('success', 'Article supprimé avec succès!');
    
    return $this->redirectToRoute('articless_list');
}
}