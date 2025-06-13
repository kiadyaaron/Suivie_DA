<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use Symfony\Component\Mime\Part\FilePart;
use Symfony\Component\Mime\Part\Multipart\MixedPart;

class DAEmailService
{
    private MailerInterface $mailer;
    private DAExportService $daExportService;

    public function __construct(MailerInterface $mailer, DAExportService $daExportService)
    {
        $this->mailer = $mailer;
        $this->daExportService = $daExportService;
    }

    public function sendValidatedDAs(string $to, string $cc): void
    {
        $filePath = $this->daExportService->exportValidatedDAExcel();

        $email = (new Email())
            ->from('aaronkiady@gmail.com')
            ->to($to)
            ->cc($cc)
            ->subject('Demandes d\'achat en attente de BC')
            ->text('Bonjour, Veuillez trouver ci-joint la liste des demandes d\'achat en attente de bon de commande.')
            ->attachFromPath($filePath, 'DA_Validees.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->mailer->send($email);

        // Nettoyage
        unlink($filePath);
    }
}
