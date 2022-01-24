<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArtistRepository;
use App\Service\SoundCloudParser;
use App\Service\TrackManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    private ArtistRepository $artistRepository;
    private SoundCloudParser $parser;
    private TrackManager $trackManager;

    public function __construct(
        ArtistRepository $artistRepository,
        SoundCloudParser $parser,
        TrackManager     $manager,
    )
    {
        $this->artistRepository = $artistRepository;
        $this->parser = $parser;
        $this->trackManager = $manager;
    }

    #[Route('/', name: 'search')]
    public function search(Request $request): Response
    {
        $extUserID = $this->parser->resolveUserID($request->get('link'));
        $tracks = $this->parser->getTracks($extUserID);
        $this->trackManager->upsert($tracks);

        $artist = $this->artistRepository->findByExtID($extUserID);
        if (!$artist) {
            throw new HttpException(404, 'artist not found in db');
        }

        return $this->render('Index/search.html.twig', [
            'tracks' => $artist->getTracks(),
        ]);
    }
}
