<?php


namespace App\Service\Search;


use App\Entity\Module\GpReview;
use App\Entity\Ressources\Reviews;
use App\Repository\GpReviewRepository;
use App\Repository\ReviewRepository;

class SearchReviews
{

    private ReviewRepository $reviewRepository;
    private GpReviewRepository $gpReviewRepo;



    public function __construct(ReviewRepository $reviewRepository, GpReviewRepository $gpReviewRepository)
    {
        $this->reviewRepository=$reviewRepository;
        $this->gpReviewRepo=$gpReviewRepository;
    }

    public function findGroupeReviewOfPotins($id): array
    {
        $tablereviews=[];

            $reviews=$this->reviewRepository->findAllById($id);
            if(!empty($reviews)){
                foreach ($reviews as $review){
                  $tablereviews[$review->getCategorie()->getName()][]=$review; //todo modif pour adapter a review
                }
                return $tabressources??[];
            }

        return $tablereviews;
    }
    public function findGpReview($id): ?GpReview
    {
        return $this->gpReviewRepo->find($id);
    }

    public function findAllReviews(): array
    {
        $tabreviews=[];

        $reviews=$this->reviewRepository->findAll();
        if(!empty($reviews)){
            foreach ($reviews as $review){
                $tabreviews[$review->getCategorie()->getName()][]=$review; // todo adapter au review
            }
            return $tabreviews??[];
        }

        return $tabreviews;
    }

    public function findReviews(): array
    {
        return $this->reviewRepository->findAll();
    }

    public function searchOneReview($id): ?Reviews
    {
        return $this->reviewRepository->find($id);
    }
}
