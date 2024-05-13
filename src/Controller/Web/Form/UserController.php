<?php

declare(strict_types=1);

namespace App\Controller\Web\Form;

use App\Entity\Skill;
use App\Entity\User;
use App\Manager\UserManager;
use App\Service\Builder\UserBuilderService;
use App\Service\Form\Type\UserType;
use App\Service\Security\AppLoginAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: 'users')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly FormFactoryInterface $form_factory,
        private readonly EntityManagerInterface $em,
        private readonly UserBuilderService $user_builder,
        private readonly Security $security,
        private readonly UserManager $user_manager
    ) {
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/form-create', name: 'create-user', methods: ['GET', 'POST'])]
    #[Route(path: '/form-update/{id}', name: 'update-user', methods: ['GET', 'POST'])]
    public function manageUserAction(Request $request, string $_route, ?User $user = null): Response
    {
        $is_route_create = $_route === 'create-user';
        $is_route_update = $_route === 'update-user';

        $skills = $this->em->getRepository(Skill::class)->findAll();
        $form = $this->form_factory->create(UserType::class, $user ?? null, [
            'is_route_create' => $is_route_create,
            'is_route_update' => $is_route_update,
            'skills' => $skills,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($is_route_update) {
                $user_id = $this->user_builder->updateUserWithRelatedEntities($user, $data);
            } else {
                $user_id = $this->user_builder->saveUserWithRelatedEntities($data);
            }
            if (!is_null($user_id)) {
                return $this
                    ->security
                    ->login($this->user_manager->findUser($user_id), AppLoginAuthenticator::class);
            }

            return $this->redirectToRoute('app_web_user_getusers');
        }

        return $this->render('manage_user.html.twig', [
            'form' => $form,
            'is_route_create' => $is_route_create,
            'is_route_update' => $is_route_update,
            'user' => $user,
        ]);
    }
}
