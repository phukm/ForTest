<?php
namespace ConsoleInvitation\Service;

interface LearningProgressServiceInterface
{
    public function receiveLearningInfoFromEnavi();
    public function sendPersonalIdToEnavi();
}