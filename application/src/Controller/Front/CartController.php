<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Component\Cart\CartContext;
use App\Component\Cart\CartManager;
use App\Entity\OrderItem;
use App\Entity\ProductVariant;
use App\Entity\Release;
use App\Entity\Track;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    public function __construct(
        private CartContext $cartContext,
        private CartManager $cartManager,
        private EntityManagerInterface $em,
    ) {
    }

    #[Route('/panier', name: 'front_cart_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $cart = $this->cartContext->getCart($request);

        return $this->render('front/cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/panier/ajouter', name: 'front_cart_add', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $variantId = (int) ($data['variantId'] ?? 0);
        $qty = max(1, (int) ($data['qty'] ?? 1));

        if ($variantId <= 0) {
            return $this->json(['success' => false, 'message' => 'Invalid variant'], 400);
        }

        /** @var ProductVariant|null $variant */
        $variant = $this->em->find(ProductVariant::class, $variantId);
        if (null === $variant || $variant instanceof Release || $variant instanceof Track) {
            return $this->json(['success' => false, 'message' => 'Variant not found'], 404);
        }

        $cart = $this->cartContext->getCart($request);
        $this->cartManager->addItem($cart, $variant, $qty);

        return $this->json([
            'success' => true,
            'cartCount' => $cart->getItems()->count(),
            'message' => 'product.cart.added',
        ]);
    }

    #[Route('/panier/retirer/{itemId}', name: 'front_cart_remove', methods: ['POST'])]
    public function remove(int $itemId, Request $request): JsonResponse
    {
        /** @var OrderItem|null $item */
        $item = $this->em->find(OrderItem::class, $itemId);
        if (null === $item) {
            return $this->json(['success' => false], 404);
        }

        $cart = $this->cartContext->getCart($request);
        if ($item->getOrder()?->getId() !== $cart->getId()) {
            return $this->json(['success' => false], 403);
        }

        $this->cartManager->removeItem($cart, $item);

        return $this->json([
            'success' => true,
            'cartCount' => $cart->getItems()->count(),
        ]);
    }

    #[Route('/panier/modifier/{itemId}', name: 'front_cart_update', methods: ['POST'])]
    public function update(int $itemId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $qty = (int) ($data['qty'] ?? 0);

        /** @var OrderItem|null $item */
        $item = $this->em->find(OrderItem::class, $itemId);
        if (null === $item) {
            return $this->json(['success' => false], 404);
        }

        $cart = $this->cartContext->getCart($request);
        if ($item->getOrder()?->getId() !== $cart->getId()) {
            return $this->json(['success' => false], 403);
        }

        $this->cartManager->updateItemQty($item, $cart, $qty);

        return $this->json([
            'success' => true,
            'cartCount' => $cart->getItems()->count(),
            'itemTotal' => $qty > 0 ? $item->getTotal() : 0,
            'cartTotal' => $cart->getTotal(),
        ]);
    }
}
