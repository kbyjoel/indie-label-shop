<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\ShopUser;
use App\Form\Front\RegistrationFormType;
use App\Repository\ShopUserRepository;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class AccountController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ShopUserRepository $shopUserRepo,
    ) {
    }

    #[Route('/compte/connexion', name: 'front_account_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('front_account_dashboard');
        }

        return $this->render('front/account/login.html.twig', [
            'error' => $authUtils->getLastAuthenticationError(),
            'lastUsername' => $authUtils->getLastUsername(),
        ]);
    }

    #[Route('/compte/deconnexion', name: 'front_account_logout', methods: ['GET'])]
    public function logout(): never
    {
        throw new \LogicException('This method can be blank — it will be intercepted by the logout key on the firewall.');
    }

    #[Route('/compte/inscription', name: 'front_account_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $hasher,
        UserAuthenticatorInterface $userAuthenticator,
        LoginFormAuthenticator $authenticator,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('front_account_dashboard');
        }

        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $email = strtolower((string) $data['email']);

            if ($this->shopUserRepo->findOneByEmail($email)) {
                $form->get('email')->addError(
                    new \Symfony\Component\Form\FormError($this->container->get('translator')->trans('account.register.email_taken'))
                );
            } else {
                $customer = new Customer();
                $customer->setEmail($data['email']);
                $customer->setEmailCanonical($email);
                $customer->setFirstName($data['firstName']);
                $customer->setLastName($data['lastName']);

                $shopUser = new ShopUser();
                $shopUser->setUsername($data['email']);
                $shopUser->setUsernameCanonical($email);
                $shopUser->setEnabled(true);
                $shopUser->setCustomer($customer);
                $shopUser->setPassword($hasher->hashPassword($shopUser, $data['plainPassword']));

                $this->em->persist($shopUser);
                $this->em->flush();

                return $userAuthenticator->authenticateUser($shopUser, $authenticator, $request)
                    ?? $this->redirectToRoute('front_account_dashboard');
            }
        }

        return $this->render('front/account/register.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/compte', name: 'front_account_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var ShopUser $shopUser */
        $shopUser = $this->getUser();
        $customer = $shopUser->getCustomer();

        $orders = $this->em->getRepository(Order::class)->findBy(
            ['customer' => $customer],
            ['id' => 'DESC'],
        );

        $orders = array_filter($orders, fn (Order $o) => Order::STATE_CART !== $o->getState());

        return $this->render('front/account/dashboard.html.twig', [
            'orders' => array_values($orders),
        ]);
    }

    #[Route('/compte/commande/{number}', name: 'front_account_order_show', methods: ['GET'])]
    public function orderShow(string $number): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var ShopUser $shopUser */
        $shopUser = $this->getUser();

        /** @var Order|null $order */
        $order = $this->em->getRepository(Order::class)->findOneBy(['number' => $number]);

        if (null === $order || $order->getCustomer() !== $shopUser->getCustomer()) {
            throw $this->createNotFoundException();
        }

        return $this->render('front/account/order_show.html.twig', [
            'order' => $order,
        ]);
    }
}
