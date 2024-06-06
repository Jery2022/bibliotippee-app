<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Document;
use App\Form\CommentType;
use App\Model\SearchData;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\DocumentRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/document')]
class DocumentController extends AbstractController
{
    #[Route('/', name: 'app_document_index', methods: ['GET'])]
    public function index(
        Request $request,
        //  Document $document,
        DocumentRepository $documentRepository,
        CategoryRepository $categoryRepository,
        CommentRepository $commentRepository): Response {

        $searchData = new SearchData();
        $form = $this->createForm(SearchType::class, $searchData);
        $form->handleRequest($request);

        //dd($form, $searchData);

        if ($form->isSubmitted() && $form->isValid()) {
            //$documents = $documentRepository->findSearch($searchData);
            dd($form, $searchData);
            /* } else {
        $allDocuments = $documentRepository->findBy(
        ['isPublished' => true],
        ['createdAt' => 'DESC'],
        );
        } */
        }

        $allDocuments = $documentRepository->findBy(
            ['isPublished' => true],
            ['createdAt' => 'DESC'],
        );

        $catogories = $categoryRepository->findAll();

        $websiteName = 'BiblioTIPPEE';

        // dd($form->createView());

        return $this->render('document/index.html.twig', [
            'form' => $form->createView(),
            'websiteName' => $websiteName,
            'documents' => $allDocuments,
            'categories' => $catogories,
        ]);
    }

    #[Route('/{id}', name: 'app_document_show', methods: ['GET', 'POST'])]
    public function show(
        Document $document,
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
        CommentRepository $commentRepository,
        SessionInterface $session,
    ): Response {

        $session->set('previous_url', $request->getUri()); // récupération de l'url précédente et mise en session
        $averageRate = $commentRepository->getAverageByComment($document->getId()); // récupération de la moyenne des notes
        $user = $security->getUser(); // récupération de l'entité utilisateur connecté

        $commentsByDocument = $commentRepository->getCommentsByDocument($document->getId()); // récupération de tous les commentaires du document ciblé
        $commentByDocumentByUser = $commentRepository->getCommentByDocumentByUser($document->getId(), $user); // récupération du commentaire du document ciblé pour l'utilisateur connecté

        //dd($commentByDocumentByUser);
        $comment = $commentRepository->findOneBy([
            'documents' => $document,
            'users' => $user,
        ]);

        if (!$comment) {

            $createdAt = new DateTimeImmutable();
            $comment = new Comment();
            $comment->setDocuments($document);
            $comment->setUsers($user);
            $comment->setIsValided(false);
            $comment->setCreatedAt($createdAt);
        }

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest(($request));

        if ($form->isSubmitted() && $form->isValid()) {

            // ... perform some action, such as saving the task to the database
            $entityManager->persist($comment);
            $entityManager->flush();

            $message = 'Votre commentaire a bien été enregistré. Il sera publié après validation par un administrateur.';
        }

        return $this->render('document/show.html.twig', [
            'document' => $document,
            'form' => $form,
            'user' => $user,
            'averageRate' => $averageRate,
            'comments' => $commentsByDocument,
            'comment' => $commentByDocumentByUser,
            'message' => $message ?? null,
        ]);
    }

}
