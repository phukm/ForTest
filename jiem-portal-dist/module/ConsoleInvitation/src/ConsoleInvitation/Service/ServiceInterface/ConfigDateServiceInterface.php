<?php
namespace ConsoleInvitation\Service\ServiceInterface;

interface ConfigDateServiceInterface
{
    public function importConfigDate($fileName);

    public function exportConfigDate($year, $kai);
}