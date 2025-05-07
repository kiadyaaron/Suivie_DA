<?php

namespace App\Controller;

use App\Entity\DA;
use App\Form\DAForm;
use App\Repository\DARepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
final class DAController extends AbstractController
{
    #[Route(name: 'app_d_a_index', methods: ['GET'])]
    public function index(Request $request, DARepository $dARepository): Response
    {
        $searchTerm = $request->query->get('search');
        $monthDA = $request->query->get('month_da');
        $monthBCA = $request->query->get('month_bca');
        $retardDABCA = $request->query->get('retard_dabca');

        $dAs = $dARepository->searchByFieldsAndMonths($searchTerm, $monthDA, $monthBCA, $retardDABCA);

        return $this->render('da/index.html.twig', [
            'd_as' => $dAs,
            'searchTerm' => $searchTerm,
        ]);
    }

    #[Route('/new', name: 'app_d_a_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $dA = new DA();
        $form = $this->createForm(DAForm::class, $dA);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dA->calculerRetards();
            $entityManager->persist($dA);
            $entityManager->flush();

            return $this->redirectToRoute('app_d_a_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('da/new.html.twig', [
            'd_a' => $dA,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_d_a_show', methods: ['GET'])]
    public function show(int $id, DARepository $dARepository): Response
    {
        $dA = $dARepository->find($id);

        return $this->render('da/show.html.twig', [
            'd_a' => $dA,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_d_a_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, DARepository $dARepository, EntityManagerInterface $entityManager): Response
    {
        $dA = $dARepository->find($id);

        if (!$dA) {
            throw $this->createNotFoundException('Demande d\'achat introuvable.');
        }

        $form = $this->createForm(DAForm::class, $dA);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dA->calculerRetards();
            $entityManager->flush();

            return $this->redirectToRoute('app_d_a_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('da/edit.html.twig', [
            'd_a' => $dA,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_d_a_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, DARepository $dARepository, EntityManagerInterface $entityManager): Response
    {
        $dA = $dARepository->find($id);

        if (!$dA) {
            throw $this->createNotFoundException('Demande d\'achat introuvable.');
        }

        if ($this->isCsrfTokenValid('delete' . $dA->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($dA);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_d_a_index', [], Response::HTTP_SEE_OTHER);
    }
}
