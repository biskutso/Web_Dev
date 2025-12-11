<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ActivityLogRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/activity_logs')]
final class ActivityLogsController extends AbstractController
{
    #[Route('/', name: 'app_activity_logs', methods:['GET'])]
    public function index(ActivityLogRepository $activityLogRepository): Response
    {
        $logs = $activityLogRepository -> findBy([], ['datetime' => 'DESC']);

        return $this->render('activity_logs/index.html.twig', [
            'controller_name' => 'ActivityLogsController',
            'logs' => $logs,
        ]);
    }
}
