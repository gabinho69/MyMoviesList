<?php

namespace App\Controller;


use App\Entity\Movie;
use App\Form\MovieType;
use App\Form\PasswordType;
use App\Form\MassImportType;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FilmDescriptionService;

/**
 * @Route("/")
 */
class MovieController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(MovieRepository $movieRepository): Response
    {
        $erreur= "";
        if(isset($_GET['erreur'])){
            $erreur = $_GET['erreur'];
        }

        return $this->render('movie/index.html.twig', [
            'movies' => $movieRepository->findAll(),
            'erreur' => $erreur,
        ]);
    }

    /**
     * @Route("/upload", name="uploadmovie", methods={"GET", "POST"})
     */
    public function uploadMovie(Request $request,MovieRepository $filmsRepository,EntityManagerInterface $entityManager): Response
    {  
        
        $form = $this->createForm(MassImportType::class);
        $form->handleRequest($request);
        $erreur = "";
        
        
        if ($form->isSubmitted() && $form->isValid()) {
            $csvFile = $form->get('fichier_csv')->getData();
            $csvFile = $csvFile->getRealPath();
            

            $em = $this->getDoctrine()->getManager();
            
         
         
            if ( $xlsx = \SimpleXLSX::parse($csvFile) ) {
         
                    foreach( $xlsx->rows() as $key=> $r ) {
                        $Film= new Movie();
                        
                        $Film->setName($r[0]);
                        $Film->setDescription($r[1]);
                        $Film->setScore((int)$r[2]);
                        $Film->setVotersNumber((int)$r[3]); 
                    }
                    $em->persist($Film);
                    $em->flush();
         
            } else {
                echo \SimpleXLSX::parseError();
                return $this->redirectToRoute('addmovie', ['erreur'=>"impossible de récupérer les données"], Response::HTTP_SEE_OTHER);
            }
        }
        return $this->renderForm('movie/addmovie.html.twig', [
            'erreur'=>$erreur,
            'form' => $form,
            
        ]);
        
    }

    /**
     * @Route("/add", name="addmovie", methods={"GET", "POST"})
     */
    public function addmovie(Request $request, EntityManagerInterface $entityManager): Response
    {
        $erreur= "";
        if(isset($_GET['erreur'])){
            $erreur = $_GET['erreur'];
        }
        $movie = new Movie();
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filmDescriptionService = new FilmDescriptionService();
            $resultat = $filmDescriptionService->getDescription($_POST["movie"]["name"]);
            if($resultat == "erreur lors de la recherche dans l'API"){
                return $this->redirectToRoute('addmovie', ['erreur'=>$resultat], Response::HTTP_SEE_OTHER);
            }else{
                $description = $resultat->data->Plot;
                $movie->setDescription($description);
                $entityManager->persist($movie);
                $entityManager->flush();
                return $this->redirectToRoute('index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->redirectToRoute('index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('movie/addmovie.html.twig', [
            'movie' => $movie,
            'form' => $form,
            'erreur' => $erreur,
        ]);
    }

    /**
     * @Route("/{id}", name="movie", methods={"GET"})
     */
    public function movie(Movie $movie): Response
    {
        return $this->render('movie/movie.html.twig', [
            'movie' => $movie,
        ]);
    }

    

    /**
     * @Route("/{id}", name="deletemovie", methods={"POST"})
     */
    public function delete(Request $request, Movie $film, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PasswordType::class, $film);
        $form->handleRequest($request);
        $mdp = $this->getParameter('app.password');

        if ($form->isSubmitted()) {
            
            $mdpPosted = $_POST['password']['password'];
            

            if($mdp == $mdpPosted){
                $id = $request->attributes->get('id');
                $film = $entityManager->getRepository(Movie::class)->find($id);
                $entityManager->remove($film);
                $entityManager->flush();
                return $this->redirectToRoute('index', [[]], Response::HTTP_SEE_OTHER);
            }
            else{
                return $this->redirectToRoute('index', ['erreur'=>"mot de passe admin incorrect"], Response::HTTP_SEE_OTHER);
            }
            
        }

        return $this->renderForm('movie/password.html.twig', [
            'film' => $film,
            'form' => $form,
        ]);
    }
}
