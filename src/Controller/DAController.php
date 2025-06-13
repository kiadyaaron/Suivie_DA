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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Service\DAEmailService;

#[Route('/da')]
final class DAController extends AbstractController
{
    #[Route(name: 'app_d_a_index', methods: ['GET'])]
    public function index(Request $request, DARepository $dARepository): Response
    {
        $searchTerm = $request->query->get('search');
        $monthDA = $request->query->get('month_da');
        $monthBCA = $request->query->get('month_bca');
        $retardDABCA = $request->query->get('retard_dabca');
        $retardLivraison = $request->query->get('retard_livraison');

        $dAs = $dARepository->searchByFieldsAndMonths($searchTerm, $monthDA, $monthBCA, $retardDABCA, $retardLivraison);

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

    #[Route('/{id<\d+>}', name: 'app_d_a_show', methods: ['GET'])]
    public function show(int $id, DARepository $dARepository): Response
    {
        $dA = $dARepository->find($id);

        return $this->render('da/show.html.twig', [
            'd_a' => $dA,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_d_a_edit', methods: ['GET', 'POST'])]
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

    #[Route('/{id<\d+>}', name: 'app_d_a_delete', methods: ['POST'])]
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

    #[Route('/export/excel', name: 'app_d_a_export_excel', methods: ['GET'])]
public function exportToExcel(Request $request, DARepository $daRepository): StreamedResponse
{
    // Récupérer les mêmes paramètres que dans ton index
    $term = $request->query->get('search');
    $monthDA = $request->query->get('monthDA');
    $monthBCA = $request->query->get('monthBCA');
    $retardDABCA = $request->query->get('retardDABCA');
    $retardLivraison = $request->query->get('retardLivraison');

    // Utiliser ta méthode personnalisée de recherche
    $das = $daRepository->searchByFieldsAndMonths($term, $monthDA, $monthBCA, $retardDABCA, $retardLivraison);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $headers = [
        'ID',
        'ReferenceDA',
        'DateCreationDA',
        'EtatDA',
        'ChantierDepartement',
        'Description',
        'ReferenceBCA',
        'CreationBCA',
        'DateLivraison',
        'RetardDA_BCA',
        'RetardLivraison',
    ];

    foreach ($headers as $col => $header) {
        $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
    }

    $row = 2;
    foreach ($das as $da) {
        $sheet->setCellValueByColumnAndRow(1, $row, $da->getId());
        $sheet->setCellValueByColumnAndRow(2, $row, $da->getReferenceDA());
        $sheet->setCellValueByColumnAndRow(3, $row, $da->getDateCreationDA()?->format('Y-m-d'));
        $sheet->setCellValueByColumnAndRow(4, $row, $da->getEtatDA());
        $sheet->setCellValueByColumnAndRow(5, $row, $da->getChantierDepartement());
        $sheet->setCellValueByColumnAndRow(6, $row, $da->getDescription());
        $sheet->setCellValueByColumnAndRow(7, $row, $da->getReferenceBCA());
        $sheet->setCellValueByColumnAndRow(8, $row, $da->getCreationBCA()?->format('Y-m-d'));
        $sheet->setCellValueByColumnAndRow(9, $row, $da->getDateLivraison()?->format('Y-m-d'));
        $sheet->setCellValueByColumnAndRow(10, $row, $da->getRetardDABCA());
        $sheet->setCellValueByColumnAndRow(11, $row, $da->getRetardLivraison());
        $row++;
    }

    $writer = new Xlsx($spreadsheet);

    $response = new StreamedResponse(function () use ($writer) {
        $writer->save('php://output');
    });

    $disposition = $response->headers->makeDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        'DAs_filtrees.xlsx'
    );

    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', $disposition);

    return $response;
}
    #[Route('/envoyer-da-validees', name: 'send_validated_das')]
    public function sendValidatedDAs(DAEmailService $daEmailService): Response
    {
        $daEmailService->sendValidatedDAs('ramijoroaaaron@gmail.com', 'andriamitantsoafabyh@gmail.com');
        return new Response('Email envoyé avec succès');
    }


}
