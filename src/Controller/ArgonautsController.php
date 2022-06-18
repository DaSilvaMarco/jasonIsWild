<?php

namespace App\Controller;

use App\Entity\Argonauts;
use App\Form\ArgonautsType;
use App\Repository\ArgonautsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/argonauts')]
class ArgonautsController extends AbstractController
{
    #[Route('/', name: 'app_argonauts_index', methods: ['GET', 'POST'])]
    public function index(Request $request, ArgonautsRepository $argonautsRepository, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {

        $argonaut = new Argonauts();
        $form = $this->createForm(ArgonautsType::class, $argonaut);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $picture = $form->get('picture')->getData();

            if ($picture) {
                $originalFilename = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename. '-'.uniqid().'.'.$picture->guessExtension();

                $picture->move(
                    $this->getParameter('pictures_directory'),
                    $newFilename
                );

                $argonaut->setPicture($newFilename);
            }

            $entityManager->persist($argonaut);
            $entityManager->flush();

            return $this->redirectToRoute('app_argonauts_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('argonauts/index.html.twig', [
            'argonauts' => $argonautsRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_argonauts_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, ArgonautsRepository $argonautsRepository, $id): Response
    {
        $argonaut = $argonautsRepository->find($id);

        $form = $this->createForm(ArgonautsType::class, $argonaut);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $picture = $form->get('picture')->getData();

            if ($picture) {
                $originalFilename = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename. '-'.uniqid().'.'.$picture->guessExtension();

                $picture->move(
                    $this->getParameter('pictures_directory'),
                    $newFilename
                );

                $argonaut->setPicture($newFilename);
            }

            $entityManager->persist($argonaut);
            $entityManager->flush();

            return $this->redirectToRoute('app_argonauts_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('argonauts/edit.html.twig', [
            'argonaut' => $argonaut,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_argonauts_delete', methods: ['GET', 'POST'])]
    public function delete(ArgonautsRepository $argonautsRepository, $id, EntityManagerInterface $entityManager): Response
    {
        $argonaut = $argonautsRepository->find($id);

        $entityManager->remove($argonaut);
        $entityManager->flush();

        return $this->redirectToRoute('app_argonauts_index', [], Response::HTTP_SEE_OTHER);
    }
}
