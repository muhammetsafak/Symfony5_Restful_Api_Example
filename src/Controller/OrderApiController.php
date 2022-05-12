<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\OrderProductRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\OrderService;
use App\Service\ResponseService;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class OrderApiController extends AbstractController
{
    protected $orderRepository;
    protected $productRepository;
    protected $orderProductRepository;
    protected $userRepository;
    protected $urlGenerator;
    protected $orderService;

    public function __construct(
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        OrderProductRepository $orderProductRepository,
        UserRepository $userRepository,
        UrlGeneratorInterface $urlGenerator,
        OrderService $orderService
    )
    {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->orderProductRepository = $orderProductRepository;
        $this->userRepository = $userRepository;
        $this->urlGenerator = $urlGenerator;
        $this->orderService = $orderService;
    }

    /**
     * @param Request $request
     * @param ResponseService $responseService
     * @return Response
     * @Route("/list", name="order_list", methods={"GET"})
     */
    public function list(Request $request, ResponseService $responseService): Response
    {
        $userMail = $this->orderService->getJWTTokenUserMail($request);
        $user = $this->userRepository->findByMail($userMail);
        if(empty($user)){
            return $responseService->respondUnauthorized('Token okunamadı ya da geçersiz bir token kullanıyorsunuz.');
        }
        $userId = $user[0]->getId();
        $data = $this->orderRepository->findByCustomerOrder($userId);
        if(!empty($data)){
            $res = $this->orderService->stmt2Array($data);
            return $this->json($res);
        }
        return $this->json(['error' => 'Listelenecek sipariş bulunamadı.'], Response::HTTP_NOT_FOUND);
    }

    /**
     * @param int $id
     * @param Request $request
     * @param ResponseService $responseService
     * @return Response
     * @Route("/show/{id}", name="order_show", methods={"GET"})
     */
    public function show(int $id, Request $request, ResponseService $responseService): Response
    {
        $userMail = $this->orderService->getJWTTokenUserMail($request);
        $user = $this->userRepository->findByMail($userMail);
        if(empty($user)){
            return $responseService->respondUnauthorized('Token okunamadı ya da geçersiz bir token kullanıyorsunuz.');
        }
        $userId = $user[0]->getId();
        $data = $this->orderRepository->findByCustomerOrder($userId, $id);
        if(!empty($data)){
            $res = $this->orderService->stmt2Array($data);
            return $this->json($res);
        }
        return $this->json(['error' => 'Sipariş bulunamadı'], Response::HTTP_NOT_FOUND);
    }

    /**
     * @param Request $request
     * @param ResponseService $responseService
     * @return Response
     * @Route("/new", name="order_new", methods={"POST"})
     */
    public function new(Request $request, ResponseService $responseService): Response
    {
        $userMail = $this->orderService->getJWTTokenUserMail($request);
        $user = $this->userRepository->findByMail($userMail);
        if(empty($user)){
            return $responseService->respondUnauthorized('Token okunamadı ya da geçersiz bir token kullanıyorsunuz.');
        }
        $userId = $user[0]->getId();

        $data = json_decode($request->getContent(), true);
        $res = [
            'status' => [
                'code'  => 0,
            ],
            'errors' => []];
        if(!isset($data['address'], $data['products'])){
            $res['errors'][] = [
                'msg'   => 'Adres ve ürünler belirtilmelidir'
            ];
            return $this->json($res, Response::HTTP_NO_CONTENT);
        }
        if(!is_array($data['products']) || !is_string($data['address'])){
            $res['errors'][] = [
                'msg'   => 'Adres ve ürünler doğru formatta bildirilmelidir.'
            ];
            return $this->json($res, Response::HTTP_NO_CONTENT);
        }
        $products = [];
        foreach ($data['products'] as $row) {
            if(!isset($row['id']) || !is_int($row['id'])){
                $res['errors'][] = [
                    'msg'   => 'Ürün ID belirtilmelidir.'
                ];
                continue;
            }
            if(!isset($row['quantity']) || !is_int($row['quantity'])){
                $row['quantity'] = 1;
            }
            if($this->orderService->checkProductAndStock($row['id'], $row['quantity'], $res) === FALSE){
                continue;
            }
            $this->productRepository->updateStock($row['id'], -($row['quantity']));
            $products[] = [
                'id'        => $row['id'],
                'quantity'  => $row['quantity'],
            ];
        }
        if(empty($products)){
            $res['errors'][] = [
                'msg'       => 'Siparişi oluşturulamadı. En az 1 geçerli ürüne ihtiyaç var.'
            ];
            return $this->json($res, Response::HTTP_NOT_ACCEPTABLE);
        }
        $order = new Order();
        $order->setUserId($userId)
            ->setAddress($data['address']);
        $this->orderRepository->add($order);
        $orderId = $order->getId();

        foreach ($products as $product) {
            $productEntity = new OrderProduct();
            $productEntity->setQuantity($product['quantity'])
                ->setProductId($product['id'])
                ->setOrderId($orderId);
            $this->orderProductRepository->push($productEntity);
        }
        $this->orderProductRepository->save();
        $res['orderCode'] = $orderId;
        $res['status'] = [
            'status'    => 1,
            'msg'       => 'Ok!',
        ];
        $res['detailUrl'] = $this->urlGenerator->generate('order_show', ['id' => $orderId]);

        return $this->json($res, Response::HTTP_CREATED);
    }

    /**
     * @param int $id
     * @param Request $request
     * @param ResponseService $responseService
     * @return Response
     * @Route("/update/{id}", name="order_update", methods={"PUT"})
     */
    public function update(int $id, Request $request, ResponseService $responseService): Response
    {
        $userMail = $this->orderService->getJWTTokenUserMail($request);
        $user = $this->userRepository->findByMail($userMail);
        if(empty($user)){
            $responseService->respondUnauthorized('Token okunamadı ya da geçersiz bir token kullanıyorsunuz.');
        }
        $userId = $user[0]->getId();

        $res = [
            'orderCode' => $id,
            'status'    => [
                'code'  => 0,
            ],
            'errors'    => [],
        ];

        $check = $this->orderRepository->checkCustomerOrder($userId, $id);
        if(empty($check)){
            $res['errors'][] = [
                'msg'   => 'Düzenlenmek istenen sipariş bulunamadı',
            ];
            return $this->json($res, Response::HTTP_NOT_FOUND);
        }
        /** @var Order $order */
        $order = $check[0];

        if($order->getShippingDate() !== null){
            $res['errors'][] = [
                'msg'   => 'Sipariş teslimat için gönderildiğinden düzenlenemez',
            ];
            return $this->json($res);
        }

        $inputs = json_decode($request->getContent(), true);

        if(isset($inputs['address']) && $inputs['address'] !== $order->getAddress()){
            $order->setAddress($inputs['address']);
            $this->orderRepository->update($order);
            $res['status'] = [
                'code'  => 1,
                'msg'   => 'Ok',
            ];
            $res['process'] = [
                'addressUpdate'     => 1,
            ];
        }

        if(isset($inputs['products']) and is_array($inputs['products'])) {
            $product = $this->orderService->validProductsArray($inputs['products']);
            if (!empty($product['errors'])) {
                foreach ($product['errors'] as $err) {
                    $res['errors'][] = [
                        'msg' => $err,
                    ];
                }
            }
            if (!empty($product['products'])) {
                /** @var OrderProduct[] $orderProducts */
                $orderProducts = $this->orderProductRepository->getOrderProduct($id);
                foreach ($orderProducts as $orderProduct) {
                    $productId = $orderProduct->getProductId();
                    if (!isset($product['products'][$productId])) {
                        $this->orderProductRepository->remove($orderProduct);
                        continue;
                    }
                    $quantity = $product['products'][$productId];
                    $quantityDiff = ($orderProduct->getQuantity() > $quantity) ? ($orderProduct->getQuantity() - $quantity) : -($quantity - $orderProduct->getQuantity());
                    $this->productRepository->updateStock($productId, $quantityDiff);
                    $orderProduct->setQuantity($quantity);
                    $this->orderProductRepository->update($orderProduct);
                    unset($product['products'][$productId]);
                }
                foreach ($product['products'] as $productId => $quantity) {
                    $productEntity = new OrderProduct();
                    $productEntity->setQuantity($quantity)
                        ->setProductId($productId)
                        ->setOrderId($id);
                    $this->orderProductRepository->push($productEntity);
                }
                $this->orderProductRepository->save();
                $res['status'] = [
                    'status'    => 1,
                    'msg'       => 'Ok!',
                ];
                $res['process']['productsUpdate'] = 1;
            }
        }
        $res['detailUrl'] = $this->urlGenerator->generate('order_show', ['id' => $id]);

        return $this->json($res, Response::HTTP_CREATED);
    }

    /**
     * @param int $id
     * @param Request $request
     * @param ResponseService $responseService
     * @return Response
     * @Route("/delete/{id}", name="order_delete", methods={"DELETE"})
     */
    public function delete(int $id, Request $request, ResponseService $responseService): Response
    {
        $userMail = $this->orderService->getJWTTokenUserMail($request);
        $user = $this->userRepository->findByMail($userMail);
        if(empty($user)){
            return $responseService->respondUnauthorized('Token okunamadı ya da geçersiz bir token kullanıyorsunuz.');
        }
        $userId = $user[0]->getId();

        $res = [
            'status'    => [
                'code'  => 0
            ],
            'errors'    => [],
        ];

        $check = $this->orderRepository->checkCustomerOrder($userId, $id);
        if(empty($check)){
            $res['errors'][] = [
                'msg'   => 'Sipariş bulunamadı'
            ];
            return $this->json($res, Response::HTTP_NOT_FOUND);
        }

        /** @var Order $order */
        $order = $check[0];

        if($order->getShippingDate() !== null){
            $res['errors'][] = [
                'msg'   => 'Sipariş teslimat için gönderildiğinden silinemez'
            ];
            return $this->json($res);
        }
        $this->orderRepository->remove($order);
        $res['status'] = [
            'code'  => 1,
            'msg'   => 'Siparişiniz (#' . $id . ') silindi'
        ];
        return $this->json($res, 200);
    }

}
