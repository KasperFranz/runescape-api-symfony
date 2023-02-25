<?php

namespace App\Controller;

use App\Service\RsApiService;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractController
{
    private const PLAYER_NAME = 'VincentS';

    /**
     * @return Response
     * @throws GuzzleException
     */
    #[Route(path: '/', name: 'index')]
    public function index(
        ChartBuilderInterface $chartBuilder,
        RsApiService $rsApi,
        Request $request,
        FormFactoryInterface $formFactory
    ): Response {
        $form = $formFactory->createNamedBuilder(name: 'search_form', options: [
            'attr' => [
                'class' => 'row-cols-lg-auto g-3 align-items-center d-flex'
            ]
        ])
            ->add('playerName', TextType::class, [
                'attr' => [
                    'class' => 'player-name-search-box form-control',
                    'placeholder' => 'Search for a player'
                ],
                'label' => false,
            ])
            ->add('search', SubmitType::class, [
                'label' => '<i class="fa fa-search"></i>',
                'attr' => [
                    'class' => 'player-name-search-button'
                ],
                'label_html' => true,
            ])
            ->getForm();

        $form->handleRequest($request);

        $playerName = $form->isSubmitted() && $form->isValid() ? $form->getData()['playerName'] : self::PLAYER_NAME;
        $playerInfo = $rsApi->getProfile($playerName);

        $chart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT)
            ->setOptions(['color' => 'rgb(255, 255, 255)'])
            ->setData([
                'labels' => ['Completed', 'In Progress', 'Not Started'],
                'datasets' => [
                    [
                        'backgroundColor' => ['rgb(225,187,52)', 'rgb(52,189,209)', 'rgb(197,32,55)'],
                        'borderColor' => 'rgb(0, 0, 0)',
                        'data' => [
                            $playerInfo->getQuestsCompleted(),
                            $playerInfo->getQuestsStarted(),
                            $playerInfo->getQuestsNotStarted()
                        ]
                    ]
                ],
            ]);

        return $this->render('index.html.twig', [
            'chart' => $chart,
            'playerInfo' => $playerInfo,
            'form' => $form
        ]);
    }
}