<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\MoveTechnoInput;
use App\Dto\Page\TechnoFormPageProps;
use App\Dto\Page\TechnoIndexPageProps;
use App\Dto\TechnoDto;
use App\Dto\TechnoInput;
use App\Entity\Techno;
use App\Repository\TechnoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nytodev\InertiaBundle\Service\Inertia;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/admin/technos", name: "admin_techno_")]
#[IsGranted("ROLE_ADMIN")]
final class TechnoController extends AbstractController
{
    private const PER_PAGE = 4;

    public function __construct(
        private Inertia $inertia,
        private TechnoRepository $technoRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    #[Route("", name: "index", methods: ["GET"])]
    public function index(
        #[MapQueryParameter(options: ["min_range" => 1])] int $page = 1,
    ): Response {
        $paginator = $this->technoRepository->paginateOrdered($page, self::PER_PAGE);
        $totalPages = max(1, (int) ceil(count($paginator) / self::PER_PAGE));

        // e.g. after deleting the last item of the last page
        if ($page > $totalPages) {
            return $this->redirectToRoute("admin_techno_index", ["page" => $totalPages]);
        }

        return $this->inertia->render(
            "Admin/Technos/Index",
            get_object_vars(
                new TechnoIndexPageProps(
                    technos: array_map(TechnoDto::fromEntity(...), iterator_to_array($paginator, false)),
                    page: $page,
                    totalPages: $totalPages,
                ),
            ),
        );
    }

    #[Route("/new", name: "new", methods: ["GET"])]
    public function new(): Response
    {
        return $this->inertia->render(
            "Admin/Technos/Form",
            get_object_vars(new TechnoFormPageProps(techno: null)),
        );
    }

    #[Route("", name: "create", methods: ["POST"])]
    public function create(#[MapRequestPayload] TechnoInput $input): Response
    {
        $techno = $input->applyTo(new Techno())
            ->setPosition($this->technoRepository->nextPosition());

        $this->entityManager->persist($techno);
        $this->entityManager->flush();

        $this->inertia->flash("success", sprintf('"%s" created.', $techno->getName()));

        // The new techno is last: go to the last page
        $lastPage = (int) ceil($this->technoRepository->count() / self::PER_PAGE);

        return $this->redirectToRoute("admin_techno_index", ["page" => $lastPage], Response::HTTP_SEE_OTHER);
    }

    #[Route("/{id}/edit", name: "edit", requirements: ["id" => Requirement::DIGITS], methods: ["GET"])]
    public function edit(Techno $techno): Response
    {
        return $this->inertia->render(
            "Admin/Technos/Form",
            get_object_vars(new TechnoFormPageProps(techno: TechnoDto::fromEntity($techno))),
        );
    }

    #[Route("/{id}", name: "update", requirements: ["id" => Requirement::DIGITS], methods: ["PUT"])]
    public function update(Techno $techno, #[MapRequestPayload] TechnoInput $input): Response
    {
        $input->applyTo($techno);
        $this->entityManager->flush();

        $this->inertia->flash("success", sprintf('"%s" updated.', $techno->getName()));

        return $this->redirectToRoute("admin_techno_index", status: Response::HTTP_SEE_OTHER);
    }

    #[Route("/{id}", name: "delete", requirements: ["id" => Requirement::DIGITS], methods: ["DELETE"])]
    public function delete(
        Techno $techno,
        #[MapQueryParameter(options: ["min_range" => 1])] int $page = 1,
    ): Response {
        $this->entityManager->remove($techno);
        $this->entityManager->flush();

        $this->inertia->flash("success", sprintf('"%s" deleted.', $techno->getName()));

        return $this->redirectToRoute("admin_techno_index", ["page" => $page], Response::HTTP_SEE_OTHER);
    }

    #[Route("/{id}/move", name: "move", requirements: ["id" => Requirement::DIGITS], methods: ["PATCH"])]
    public function move(
        Techno $techno,
        #[MapRequestPayload] MoveTechnoInput $input,
        #[MapQueryParameter(options: ["min_range" => 1])] int $page = 1,
    ): Response {
        $neighbor = $this->technoRepository->findNeighbor($techno, $input->direction);

        if (null !== $neighbor) {
            $position = $techno->getPosition();
            $techno->setPosition($neighbor->getPosition());
            $neighbor->setPosition($position);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute("admin_techno_index", ["page" => $page], Response::HTTP_SEE_OTHER);
    }
}
