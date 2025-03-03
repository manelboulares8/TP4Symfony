<?php

namespace App\Controller;
use App\Entity\Article;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class IndexController extends AbstractController
{
    #[Route('/', name: 'article_list')]
    public function home(ManagerRegistry $doctrine): Response
    {
        // Récupérer tous les articles de la base de données
        $articles = $doctrine->getRepository(Article::class)->findAll();

        // Retourner la vue avec les articles
        return $this->render('articles/index.html.twig', ['articles' => $articles]);
    }

    #[Route('/article/{id}', name: 'article_details')]
    public function articleDetails(int $id)
    {
        // Logique pour récupérer l'article avec l'ID (peut être une récupération en base de données)
        return new Response("Détails de l'article #$id");
    }
    #[Route('/article/save', name: 'article_save')]
    public function save(EntityManagerInterface $entityManager): Response
    {
        // Créer un nouvel article
        $article = new Article();
        $article->setNom('Article 1');
        $article->setPrix(1000);

        // Persister l'article
        $entityManager->persist($article);

        // Enregistrer l'article en base de données
        $entityManager->flush();

        // Retourner une réponse
        return new Response('Article enregistré avec id ' . $article->getId());
    }

    #[Route('/article/new', name: 'new_article', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création de l'entité Article
        $article = new Article();

        // Création du formulaire
        $form = $this->createFormBuilder($article)
            ->add('nom', TextType::class)
            ->add('prix', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Créer'])
            ->getForm();

        // Traitement du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire
            $article = $form->getData();

            // Persister et enregistrer l'article en base de données
            $entityManager->persist($article);
            $entityManager->flush();

            // Rediriger vers la liste des articles
            return $this->redirectToRoute('article_list');
        }

        // Rendu de la vue avec le formulaire
        return $this->render('articles/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/article/{id}', name: 'article_show')]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'article par son id
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        // Rendu de la vue avec l'article
        return $this->render('articles/show.html.twig', ['article' => $article]);
    }
    #[Route('/article/edit/{id}', name: 'edit_article', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'article à partir de l'ID
        $article = $entityManager->getRepository(Article::class)->find($id);

        // Vérifier si l'article existe
        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        // Créer le formulaire de modification
        $form = $this->createFormBuilder($article)
            ->add('nom', TextType::class)
            ->add('prix', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Modifier'])
            ->getForm();

        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer les modifications dans la base de données
            $entityManager->flush();

            // Rediriger vers la liste des articles
            return $this->redirectToRoute('article_list');
        }

        // Rendre la vue avec le formulaire
        return $this->render('articles/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/article/delete/{id}', name: 'delete_article', methods: ['DELETE'])]
    public function delete(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'article à partir de l'ID
        $article = $entityManager->getRepository(Article::class)->find($id);

        // Vérifier si l'article existe
        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        // Supprimer l'article de la base de données
        $entityManager->remove($article);
        $entityManager->flush();

        // Rediriger vers la liste des articles
        return $this->redirectToRoute('article_list');
    }
}
