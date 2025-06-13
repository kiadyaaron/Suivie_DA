<?php

namespace App\Service;

use App\Repository\DARepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DAExportService
{
    private DARepository $daRepository;

    public function __construct(DARepository $daRepository)
    {
        $this->daRepository = $daRepository;
    }

    public function exportValidatedDAExcel(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // En-têtes
        $sheet->fromArray([
            'Référence DA', 'Date de création DA', 'Etat', 'Chantier/Departement', 'Description', 'Référence BCA', 'Date de création BCA', 'Retard BCA', 
        ], null, 'A1');

        // Récupérer les DA validées
        $das = $this->daRepository->findBy(['EtatDA' => 'validée']);

        $row = 2;
        foreach ($das as $da) {
            $sheet->fromArray([
                $da->getReferenceDA(),
                $da->getDateCreationDA()?->format('Y-m-d'),
                $da->getEtatDA(),
                $da->getChantierDepartement(),
                $da->getDescription(),
                $da->getReferenceBCA(),
                $da->getCreationBCA(),
                $da->getRetardDABCA(),
            ], null, "A$row");
            $row++;
        }

        // Sauvegarder le fichier temporairement
        $filePath = sys_get_temp_dir() . '/DA_Validees.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }
}
