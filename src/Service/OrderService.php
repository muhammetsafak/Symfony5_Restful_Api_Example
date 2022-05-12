<?php
/**
 * OrderService.php
 *
 * This file is part of Symfony.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 Muhammet ŞAFAK
 * @license    ./LICENSE  MIT
 * @version    1.0
 * @link       https://www.muhammetsafak.com.tr
 */

declare(strict_types=1);

namespace App\Service;

use App\Repository\OrderProductRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Entity\{Order, OrderProduct, Product};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderService
{
    protected $orderRepository;
    protected $productRepository;
    protected $orderProductRepository;

    public function __construct(
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        OrderProductRepository $orderProductRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->orderProductRepository = $orderProductRepository;
    }

    public function stmt2Array(iterable $data): array
    {
        $res = [];
        foreach ($data as $row) {
            if($row instanceof Order){
                $orderId = $row->getId();
                if(($shippingDate = $row->getShippingDate()) !== null){
                    $status = [
                        'code'          => 1,
                        'msg'           => 'Gönderildi',
                        'shippingDate'  => $shippingDate,
                    ];
                }else{
                    $status = [
                        'code'  => 0,
                        'msg'   => 'Hazırlanıyor',
                    ];
                }
                $res[$orderId] = [
                    'orderCode'     => $orderId,
                    'address'       => $row->getAddress(),
                    'products'      => [],
                    'status'        => $status
                ];
                continue;
            }
            if($row instanceof OrderProduct){
                $orderId = $row->getOrderId();
                $productId = $row->getProductId();
                $res[$orderId]['products'][$productId] = [
                    'productId' => $row->getProductId(),
                    'quantity'  => $row->getQuantity(),
                ];
            }
            if($row instanceof Product){
                $productId = $row->getId();
                $res[$orderId]['products'][$productId]['name'] = $row->getName();
            }
        }
        return $res;
    }

    public function validProductsArray(array $array): array
    {
        $res = [
            'errors'        => [],
            'products'      => [],
        ];
        foreach ($array as $row) {
            if(!isset($row['id']) || !is_int($row['id'])){
                $res['errors'][] = 'Ürün ID belirtilmelidir.';
                continue;
            }
            if(!isset($row['quantity']) || !is_int($row['quantity'])){
                $row['quantity'] = 1;
            }
            $res['products'][$row['id']] = $row['quantity'];
        }
        return $res;
    }

    public function checkProductAndStock(int $productId, int $quantity, array &$res): bool
    {
        $productAndStockCheck = $this->productRepository->productAndStockCheck($productId, $quantity);
        if(empty($productAndStockCheck)){
            $res['errors'][] = [
                'msg'   => 'Ürün (#' . $productId . ') bulunamadı ya da yeterli stok yok.'
            ];
            return false;
        }
        return true;
    }

    public function getJWTTokenUserMail(Request $request)
    {
        $token = $request->server->get('HTTP_AUTHORIZATION', null);
        $tokenHeaderSplit = explode(" ", $token);
        $JWTTokenSplit = explode('.', $tokenHeaderSplit[1]);
        $JWTTokenPayloadData = json_decode(base64_decode($JWTTokenSplit[1]), true);
        return $JWTTokenPayloadData['mail'];
    }

}
