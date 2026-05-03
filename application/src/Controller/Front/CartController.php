<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Component\Cart\CartContext;
use App\Component\Cart\CartManager;
use App\Entity\OrderItem;
use App\Entity\ProductVariant;
use Aropixel\AdminBundle\Component\Media\Resolver\PathResolverInterface;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
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
        private CacheManager $cacheManager,
        private PathResolverInterface $pathResolver,
    ) {
    }

    #[Route('/panier/mini', name: 'front_cart_mini', methods: ['GET'])]
    public function mini(Request $request): JsonResponse
    {
        if (0 === $this->cartContext->getCartItemCount($request)) {
            return $this->json(['count' => 0, 'total' => 0, 'items' => []]);
        }

        $cart = $this->cartContext->getCart($request);
        $items = [];

        foreach ($cart->getItems() as $item) {
            /** @var OrderItem $item */
            $variant = $item->getVariant();
            /** @var \App\Entity\ProductVariant|null $variant */
            $product = $variant?->getProduct();
            /** @var \App\Entity\Product|null $product */
            $rawImage = $product?->getImage()?->getImage();

            $imageUrl = null;
            if (null !== $rawImage) {
                $path = parse_url($this->pathResolver->getImagePath($rawImage), \PHP_URL_PATH);
                $imageUrl = $this->cacheManager->getBrowserPath((string) $path, 'product_card', [], null);
            }

            $items[] = [
                'id' => (int) $item->getId(),
                'name' => $product?->getName() ?? '—',
                'variantLabel' => $variant?->getOptionValuesLabel() ?? '',
                'qty' => $item->getQuantity(),
                'unitPrice' => $item->getUnitPrice(),
                'lineTotal' => $item->getTotal(),
                'image' => $imageUrl,
            ];
        }

        return $this->json([
            'count' => $cart->getItems()->count(),
            'total' => $cart->getTotal(),
            'items' => $items,
        ]);
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
        if (null === $variant) {
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
            'cartTotal' => $cart->getTotal(),
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
