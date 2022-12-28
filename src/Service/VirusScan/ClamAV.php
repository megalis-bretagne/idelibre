<?php

namespace App\Service\VirusScan;

use Symfony\Component\Process\Process;

class ClamAV implements VirusScanInterface
{
    /**
     * Vérifie qu'un fichier est sain en fournissant son chemin d'accès.
     *
     * @param string $filePath     Chemin du fichier à contrôler
     * @param bool   $removeUnsafe Supprimer automatiquement le fichier si celui-ci n'est pas sûr
     *
     * @throws VirusScanException
     */
    public function isFileSafe($filePath, $removeUnsafe = true): bool
    {
        $process = new Process(['clamdscan', $filePath]);
        $process->setTimeout(10);
        $process->run();

        // On supprime le fichier si virus détecté + demande explicite de suppression
        if ($removeUnsafe && !$process->isSuccessful()) {
            unlink($filePath);
            throw new VirusScanException('the file contain a virus', 400);
        }

        return $process->isSuccessful();
    }
}
