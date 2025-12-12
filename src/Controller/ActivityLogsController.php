<?php

namespace App\Controller;

use App\Repository\ActivityLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActivityLogsController extends AbstractController
{
    #[Route('/activity_logs', name: 'app_activity_logs')]
    public function index(Request $request, ActivityLogRepository $activityLogsRepository): Response
    {
        // --------------------------------
        // ⭐ GET ALL UNIQUE USERS FOR DROPDOWN
        // --------------------------------
        $allUsers = $activityLogsRepository->createQueryBuilder('l')
            ->select('DISTINCT l.username')
            ->orderBy('l.username', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        // Get filters from query params
        $username = $request->query->get('user');   // matches your Twig select "name=user"
        $action = $request->query->get('action');
        $dateFrom = $request->query->get('date_from');
        $dateTo = $request->query->get('date_to');

        $qb = $activityLogsRepository->createQueryBuilder('l')
            ->orderBy('l.datetime', 'DESC');

        // Filter by username
        if (!empty($username)) {
            $qb->andWhere('l.username = :username')
               ->setParameter('username', $username);
        }

        // Filter by action groups (Create/Update/Delete)
        if (!empty($action)) {
            if ($action === 'create') {
                $qb->andWhere("l.action LIKE '%Created%'");
            } elseif ($action === 'update') {
                $qb->andWhere("l.action LIKE '%Updated%' OR l.action LIKE '%Edited%'");
            } elseif ($action === 'delete') {
                $qb->andWhere("l.action LIKE '%Deleted%'");
            }
        }

       // DATE FILTER (correct timezone conversion)
$ph = new \DateTimeZone('Asia/Manila');
$utc = new \DateTimeZone('UTC');

if ($dateFrom && !$dateTo) {
    // PH START
    $fromPH = new \DateTime($dateFrom . ' 00:00:00', $ph);
    // PH END
    $toPH   = new \DateTime($dateFrom . ' 23:59:59', $ph);

    // Convert to UTC
    $fromUTC = clone $fromPH;
    $fromUTC->setTimezone($utc);

    $toUTC = clone $toPH;
    $toUTC->setTimezone($utc);

    $qb->andWhere('l.datetime BETWEEN :from AND :to')
       ->setParameter('from', $fromUTC)
       ->setParameter('to', $toUTC);
}

if ($dateFrom && $dateTo) {
    $fromPH = new \DateTime($dateFrom . ' 00:00:00', $ph);
    $toPH   = new \DateTime($dateTo . ' 23:59:59', $ph);

    $fromUTC = clone $fromPH;
    $fromUTC->setTimezone($utc);

    $toUTC = clone $toPH;
    $toUTC->setTimezone($utc);

    $qb->andWhere('l.datetime BETWEEN :from AND :to')
       ->setParameter('from', $fromUTC)
       ->setParameter('to', $toUTC);
}

if (!$dateFrom && $dateTo) {
    $toPH = new \DateTime($dateTo . ' 23:59:59', $ph);
    $toUTC = clone $toPH;
    $toUTC->setTimezone($utc);

    $qb->andWhere('l.datetime <= :to')
       ->setParameter('to', $toUTC);
}

        // Execute
        $logs = $qb->getQuery()->getResult();

        return $this->render('activity_logs/index.html.twig', [
            'logs' => $logs,
            'allUsers' => $allUsers,   // ← REQUIRED for dropdown
        ]);
    }
}
