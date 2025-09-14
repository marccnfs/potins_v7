<?php

namespace App\Controller\Quiz;

use App\Entity\Quiz\Questionnaire;
use App\Classe\PublicSession;
use App\Entity\Quiz\Quiz;
use App\Entity\Quiz\Userquizz;
use App\Form\QuestionType;
use App\Form\QuizType;
use App\Lib\Links;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuizController extends AbstractController
{
    use PublicSession;

    #[Route('/quiz', name:"quiz")]
    public function index(QuestionRepository $questionRepository): Response
    {
        $questions = $questionRepository->findAll();

        $vartwig=$this->menuNav->templatepotins(
            Links::PUBLIC,
            'quiz',
            2,
            "");
        $post=['id'=>1];

        return $this->render($this->useragentP.'ptn_quiz/home.html.twig', [
            'directory'=>'quiz',
            'replacejs'=>false,
            'board'=>$this->board,
            'vartwig'=>$vartwig,
            'post'=>$post,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'questions' => $questions,
            ]);


    }

    #[Route('/quiz/submit', name:"quiz_submit", methods:"POST")]
    public function submit(Request $request, QuestionRepository $questionRepository): Response
    {
        $questions = $questionRepository->findAll();
        $score = 0;

        foreach ($questions as $question) {
            $answer = $request->request->get('question_' . $question->getId());
            if ($answer === $question->getCorrectAnswer()) {
                $score++;
            }
        }
        $vartwig=$this->menuNav->templatepotins(
            Links::PUBLIC,
            'reponsequiz',
            2,
            "");
        $post=['id'=>1];

        return $this->render($this->useragentP.'ptn_quiz/home.html.twig', [
            'directory'=>'quiz',
            'replacejs'=>false,
            'board'=>$this->board,
            'vartwig'=>$vartwig,
            'post'=>$post,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'questions' => $questions,
            'score' => $score,
            'total' => count($questions),
        ]);
    }


    #[Route('/quiz/start/{id}', name:"quiz_start")]
    public function startQuiz(Quiz $quiz, QuizRepository $quizRepository): Response
    {
        $quiz->setQrCode(uniqid());
        $this->em->persist($quiz);
        $this->em->flush();

        $vartwig=$this->menuNav->templatepotins(
            Links::PUBLIC,
            'start',
            2,
            "");
        $post=['id'=>1];

        return $this->render($this->useragentP.'ptn_quiz/home.html.twig', [
            'directory'=>'quiz',
            'replacejs'=>false,
            'board'=>$this->board,
            'vartwig'=>$vartwig,
            'post'=>$post,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'quiz' => $quiz,
        ]);

    }


    #[Route('/quiz/register/{qrCode}', name:"quiz_register")]
    public function register(string $qrCode, Request $request, QuizRepository $quizRepository): Response
    {
        $quiz = $quizRepository->findOneBy(['qrCode' => $qrCode]);
        if (!$quiz) {
            throw $this->createNotFoundException('Quiz not found');
        }
        if ($request->isMethod('POST')) {
            $pseudo = $request->request->get('pseudo');
            $user = new Userquizz();
            $user->setPseudo($pseudo);
            $user->setQuiz($quiz);

            $this->em->persist($user);
            $this->em->flush();

            return $this->redirectToRoute('quiz_questions', ['qrCode' => $qrCode]);
        }

        $vartwig=$this->menuNav->templatepotins(
            Links::PUBLIC,
            'register',
            2,
            "");
        $post=['id'=>1];

        return $this->render($this->useragentP.'ptn_quiz/home.html.twig', [
            'directory'=>'quiz',
            'replacejs'=>false,
            'board'=>$this->board,
            'vartwig'=>$vartwig,
            'post'=>$post,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'quiz' => $quiz,
        ]);

    }


    /**
     * @throws NonUniqueResultException
     */
    #[Route('/quiz/questions/{qrCode}', name:"quiz_questions")]
    public function questions(string $qrCode, QuizRepository $quizRepository): Response
    {
        $quiz = $quizRepository->findByqrcode($qrCode);
        if (!$quiz) {
            throw $this->createNotFoundException('Quiz not found');
        }

        // Assurez-vous que l'utilisateur est inscrit
      /*  $user = $this->getUser(); // Assurez-vous que l'utilisateur est authentifié
        if (!$user || $user->getQuiz() !== $quiz) {
            return $this->redirectToRoute('quiz_register', ['qrCode' => $qrCode]);
        }
      */

        // Récupérez les questions du quiz
        $questions = $quiz->getQuestionaire(); // Assurez-vous que cette méthode existe
dump($questions);
        $vartwig=$this->menuNav->templatepotins(
            Links::PUBLIC,
            'quiz',
            2,
            "");
        $post=['id'=>1];

        return $this->render($this->useragentP.'ptn_quiz/home.html.twig', [
            'directory'=>'quiz',
            'replacejs'=>false,
            'board'=>$this->board,
            'vartwig'=>$vartwig,
            'post'=>$post,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'quiz' => $quiz,
            'questions' => $questions,
        ]);
    }

    #[Route('/quiz/admin', name:"quiz_admin")]
    public function admin(QuizRepository $quizRepository): Response
    {
        $quizzes = $quizRepository->findAll();

        $vartwig=$this->menuNav->templatepotins(
            Links::PUBLIC,
            'admin',
            2,
            "");
        $post=['id'=>1];

        return $this->render($this->useragentP.'ptn_quiz/home.html.twig', [
            'directory'=>'quiz',
            'replacejs'=>false,
            'board'=>$this->board,
            'vartwig'=>$vartwig,
            'post'=>$post,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'quizzes' => $quizzes,
        ]);

    }


    #[Route('/quiz/new', name:"quiz_new")]
    public function newQuiz(Request $request): Response
    {
        $quiz = new Quiz();
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quiz->setQrCode(uniqid());
            $this->em->persist($quiz);
            $this->em->flush();

            return $this->redirectToRoute('quiz_admin');
        }

        $vartwig=$this->menuNav->templatepotins(
            Links::PUBLIC,
            'new',
            2,
            "");
        $post=['id'=>1];

        return $this->render($this->useragentP.'ptn_quiz/home.html.twig', [
            'directory'=>'quiz',
            'replacejs'=>false,
            'board'=>$this->board,
            'vartwig'=>$vartwig,
            'post'=>$post,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'form' => $form->createView(),
        ]);

    }


    #[Route('/quiz/{id}/new-question', name:"quiz_new_question")]
    public function newQuestion(Quiz $quiz, Request $request): Response
    {
        $question = new Questionnaire();
        $question->setQuiz($quiz);
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($question);
            $this->em->flush();

            return $this->redirectToRoute('quiz_admin');
        }
        $vartwig=$this->menuNav->templatepotins(
            Links::PUBLIC,
            'new_question',
            2,
            "");
        $post=['id'=>1];

        return $this->render($this->useragentP.'ptn_quiz/home.html.twig', [
            'directory'=>'quiz',
            'replacejs'=>false,
            'board'=>$this->board,
            'vartwig'=>$vartwig,
            'post'=>$post,
            'member'=>$this->member,
            'customer'=>$this->customer,
            'form' => $form->createView(),
        ]);

    }

}