<?php

namespace app\service;

use app\lib\enum\OrderStatusEnum;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;
use app\model\Order;
use app\model\OrderProduct;
use app\model\Product;
use app\model\UserAddress;
use think\Exception;
use think\facade\Db;

class OrderService extends BaseService
{
    public function place($uid, $oProducts)
    {
        $products = $this->getProductsByOrder($oProducts);
        $status = $this->getOrderStatus($oProducts, $products);
        if (!$status['pass']) {
            $status['order_id'] = -1;
            return $status;
        }

        $orderSnap = $this->snapOrder($uid, $oProducts, $products);
        $status = $this->createOrderByTrans($uid, $orderSnap, $oProducts);
        $status['pass'] = true;
        return $status;
    }

    private function getProductsByOrder($oProducts)
    {
        $ids = [];
        foreach ($oProducts as $oProduct) {
            array_push($ids, $oProduct['product_id']);
        }
        return Product::where('id', 'in', $ids)
            ->visible(['id', 'price', 'stock', 'name', 'main_img_url'])
            ->select()
            ->toArray();
    }

    private function getOrderStatus($oProducts, $products)
    {
        $status = [
            'pass' => true,
            'orderPrice' => 0,
            'pStatusArray' => []
        ];
        foreach ($oProducts as $oProduct) {
            $pStatus = $this->getProductStatus($oProduct['product_id'], $oProduct['count'], $products);
            if (!$pStatus['haveStock']) {
                $status['pass'] = false;
            }
            $status['orderPrice'] += $pStatus['totalPrice'];
            array_push($status['pStatusArray'], $pStatus);
        }
        return $status;
    }

    private function getProductStatus($oPID, $oCount, $products)
    {
        $pIndex = -1;
        $pStatus = [
            'id' => null,
            'haveStock' => false,
            'count' => 0,
            'name' => '',
            'totalPrice' => 0
        ];

        for ($i = 0; $i < count($products); $i++) {
            if ($oPID == $products[$i]['id']) {
                $pIndex = $i;
            }
        }

        if ($pIndex == -1) {
            throw new OrderException([
                'msg' => 'id为' . $oPID . '的商品不存在，订单创建失败'
            ]);
        }

        $product = $products[$pIndex];
        $pStatus['id'] = $product['id'];
        $pStatus['name'] = $product['name'];
        $pStatus['count'] = $oCount;
        $pStatus['totalPrice'] = $product['price'] * $oCount;
        if ($product['stock'] - $oCount >= 0) {
            $pStatus['haveStock'] = true;
        }
        return $pStatus;
    }

    private function snapOrder($uid, $oProducts, $products)
    {
        // status可以单独定义一个类
        $snap = [
            'orderPrice' => 0,
            'totalCount' => 0,
            'pStatus' => [],
            'snapAddress' => json_encode($this->getUserAddress($uid)),
            'snapName' => $products[0]['name'],
            'snapImg' => $products[0]['main_img_url'],
        ];

        if (count($products) > 1) {
            $snap['snapName'] .= '等';
        }

        for ($i = 0; $i < count($products); $i++) {
            $product = $products[$i];
            $oProduct = $oProducts[$i];
            $pStatus = $this->snapProduct($product, $oProduct['count']);
            $snap['orderPrice'] += $pStatus['totalPrice'];
            $snap['totalCount'] += $pStatus['count'];
            array_push($snap['pStatus'], $pStatus);
        }
        return $snap;
    }

    private function getUserAddress($uid)
    {
        $userAddress = UserAddress::where('user_id', $uid)->find();
        if (!$userAddress) {
            throw new UserException([
                'msg' => '用户收货地址不存在，下单失败',
                'errorCode' => 60001,
            ]);
        }
        return $userAddress->toArray();
    }

    private function snapProduct($product, $oCount)
    {
        return [
            'id' => $product['id'],
            'name' => $product['name'],
            'main_img_url' => $product['main_img_url'],
            'count' => $oCount,
            'totalPrice' => $oCount * $product['price'], // 以服务器价格为准，生成订单
            'price' => $product['price']
        ];
    }

    private function createOrderByTrans($uid, $snap, $oProducts)
    {
        Db::startTrans();
        try {
            $orderNo = $this->makeOrderNo();
            $order = new Order();
            $order->user_id = $uid;
            $order->order_no = $orderNo;
            $order->total_price = $snap['orderPrice'];
            $order->total_count = $snap['totalCount'];
            $order->snap_img = $snap['snapImg'];
            $order->snap_name = $snap['snapName'];
            $order->snap_address = $snap['snapAddress'];
            $order->snap_items = json_encode($snap['pStatus']);
            $order->save();

            $orderID = $order->id;
            $create_time = $order->create_time;

            foreach ($oProducts as &$p) {
                $p['order_id'] = $orderID;
            }
            $orderProduct = new OrderProduct();
            $orderProduct->saveAll($oProducts);
            Db::commit();
            return [
                'order_no' => $orderNo,
                'order_id' => $orderID,
                'create_time' => $create_time
            ];
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    private function makeOrderNo()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn =
            $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%02d', rand(0, 99));
        return $orderSn;
    }

    public function checkOrderStock($orderID)
    {
        // 一定要从订单商品表中直接查询
        // 不能从商品表中查询订单商品
        // 这将导致被删除的商品无法查询出订单商品来
        $oProducts = OrderProduct::where('order_id', '=', $orderID)->select();
        $products = $this->getProductsByOrder($oProducts);
        $status = $this->getOrderStatus($oProducts, $products);
        return $status;
    }

    public function delivery($orderID, $jumpPage = '')
    {
        $order = Order::find($orderID);
        if (!$order) {
            throw new OrderException();
        }
        if ($order->status != OrderStatusEnum::PAID) {
            throw new OrderException([
                'msg' => '还没付款呢，想干嘛？或者你已经更新过订单了，不要再刷了',
                'errorCode' => 80002,
                'code' => 403
            ]);
        }
        $order->status = OrderStatusEnum::DELIVERED;
        $order->save();
        $message = new DeliveryMsgService();
        return $message->sendDeliveryMessage($order, $jumpPage);
    }
}