<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class BookController extends AbstractController
{
    #[Route('/book', name: 'app_book')]
    public function index(BookRepository $bookRepository,SerializerInterface $serializer): JsonResponse
    {
        $books = $bookRepository->findAll();
        return $this->json(json_decode($serializer->serialize($books,'json', ['groups' => 'book'])), JsonResponse::HTTP_OK);
    }
    #[Route('/book/create', name: 'app_book_create',methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $data = json_decode($request->getContent(), true);
        $form->submit($data['book']);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($book);
            $em->flush();
            return new JsonResponse(['status' => 'Product created'], Response::HTTP_CREATED);
        }
        return new JsonResponse(['status' => 'Product not created'], Response::HTTP_OK);
    }
    #[Route('/book/update/{id}', name: 'app_book_modify',methods: ['PATCH'])]
    public function update(Request $request, EntityManagerInterface $em,Book $book): JsonResponse
    {
        $form = $this->createForm(BookType::class, $book);
        $data = json_decode($request->getContent(), true);
        $form->submit($data['book']);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return new JsonResponse(['status' => 'Book modified'], Response::HTTP_CREATED);
        }
        return new JsonResponse(['status' => 'Book not modified'], Response::HTTP_OK);
    }
    #[Route('/book/delete/{id}', name: 'app_book_delete',methods: ['DELETE'])]
    public function delete(BookRepository $bookRepository,EntityManagerInterface $em,int $id): JsonResponse
    {
        try{
            $book = $bookRepository->find($id);
            $bookRepository->remove($book);
            $em->flush();
            return new JsonResponse(['status' => 'Book removed'], Response::HTTP_OK);
        }
        catch(\TypeError $e){
            return new JsonResponse(['status' => 'Book not removed Exception occured'], Response::HTTP_NOT_FOUND);
        }
    }
}
