<?php


namespace App\Entity\Comments;

use App\Entity\Member\Activmember;
use App\Repository\MsgsCommentNoticeRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MsgsCommentNoticeRepository::class)]
#[ORM\Table(name:'aff_msgscommentnotice')]
class MsgsCommentNotice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Activmember::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Activmember $author= null;

    #[ORM\ManyToOne(targetEntity: CommentNotice::class,inversedBy: 'msgs')]
    #[ORM\JoinColumn(nullable: true)]
    private ?CommentNotice $comment= null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contentHtml = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bodyTxt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private \DateTime $create_at;

    public function __construct()
    {
        $this->create_at = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContentHtml(): ?string
    {
        return $this->contentHtml;
    }

    public function setContentHtml(?string $contentHtml): self
    {
        $this->contentHtml = $contentHtml;

        return $this;
    }

    public function getBodyTxt(): ?string
    {
        return $this->bodyTxt;
    }

    public function setBodyTxt(?string $bodyTxt): self
    {
        $this->bodyTxt = $bodyTxt;

        return $this;
    }

    public function getCreateAt(): ?\DateTime
    {
        return $this->create_at;
    }

    public function setCreateAt(\DateTime $create_at): self
    {
        $this->create_at = $create_at;

        return $this;
    }

    public function getAuthor(): ?Activmember
    {
        return $this->author;
    }

    public function setAuthor(?Activmember $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getComment(): ?CommentNotice
    {
        return $this->comment;
    }

    public function setComment(?CommentNotice $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}