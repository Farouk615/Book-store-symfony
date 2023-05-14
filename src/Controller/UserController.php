<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(UserRepository $userRepository,SerializerInterface $serializer): JsonResponse
    {
        $users = $userRepository->findAll();
        return $this->json(json_decode($serializer->serialize($users,'json', ['groups' => 'user'])), Response::HTTP_OK);
    }
    #[Route('/user/create', name: 'app_user_create',methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $data = json_decode($request->getContent(), true);
        $form->submit($data['user']);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($data);
            $em->flush();
            return new JsonResponse(['status' => 'User created'], Response::HTTP_CREATED);
        }
        return new JsonResponse(['status' => 'User not created'], Response::HTTP_OK);
    }
    #[Route('/user/update/{id}', name: 'app_user_modify',methods: ['PATCH'])]
    public function update(Request $request, EntityManagerInterface $em,User $user): JsonResponse
    {
        $form = $this->createForm(UserType::class, $user);
        $data = json_decode($request->getContent(), true);
        $form->submit($data['book']);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return new JsonResponse(['status' => 'User modified'], Response::HTTP_CREATED);
        }
        return new JsonResponse(['status' => 'User not modified'], Response::HTTP_OK);
    }
    #[Route('/user/delete/{id}', name: 'app_user_delete',methods: ['DELETE'])]
    public function delete(UserRepository $userRepository,EntityManagerInterface $em,int $id): JsonResponse
    {
        try{
            $user = $userRepository->find($id);
            $userRepository->remove($user);
            $em->flush();
            return new JsonResponse(['status' => 'user removed'], Response::HTTP_OK);
        }
        catch(\TypeError $e){
            return new JsonResponse(['status' => 'user not removed Exception occured'], Response::HTTP_NOT_FOUND);
        }
    }
}
