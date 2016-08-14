<?php
/**
 * Created by PhpStorm.
 * User: LatteCake
 * Date: 16/7/25
 * Time: 下午6:32
 * File: RedisController.php
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Address;
use AppBundle\Entity\History;
use AppBundle\Form\Type\AddressType;
use Knp\Component\Pager\Pagination\AbstractPagination;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Class RedisController
 *
 * @Route("/redis")
 *
 * @package AppBundle\Controller
 */
class RedisController extends Controller
{

    /**
     *
     * @Route("/info/{address}")
     *
     * @param Request $request
     * @param $address
     * @return \Redis|JsonResponse
     */
    public function infoAction(Request $request, $address)
    {
        $addressEntity = $this->getDoctrine()->getRepository('AppBundle:Address');

        $addressInfo = $addressEntity->findOneBy(['id' => $address]);

        if (!$addressInfo) {
            return new JsonResponse([
                'success' => false,
                'message' => '数据不存在'
            ], 400, ['Access-Control-Allow-Origin' => '*']);
        }

        $redisClient = $this->get('app.utils.redis_client');

        $redis = $redisClient->checkConn($addressInfo);

        if ($redis instanceof JsonResponse) {
            return $redis;
        }

        $info = (array)$redis->info();

        $redis->close();

        $response = [];

        foreach ($info as $key => $value) {
            $response[] = [
                'key' => $key,
                'value' => $value
            ];
        }

        return new JsonResponse([
            'success' => true,
            'data' => $response
        ], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * post add or edit address
     *
     * @Method({"POST"})
     * @Route("/save")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAction(Request $request)
    {
        $address = new Address();

        $form = $this->createForm(AddressType::class, $address);

        $form->handleRequest($request);

//        if (!$form->isValid()) {
//            $response = [
//                'success' => false,
//                'message' => '保存失败，有字段有问题',
//                'errors'  => $form->getErrors()
//            ];
//            return new JsonResponse($response, 400, ['Access-Control-Allow-Origin' => '*']);
//        }


        $ip = $this->get('app.utils.ip');

        $ipAddress = $ip->ipToLong(trim($request->get('ipAddress')));

        $addressEntity = $this->getDoctrine()->getRepository('AppBundle:Address');
        $address = $addressEntity->findOneBy(['ipAddress' => $ipAddress]);

        if (!intval($request->get('id')) and $address) {
            return new JsonResponse([
                'success' => false,
                'message' => 'ip已存在，请不要重复提交'
            ], 400, ['Access-Control-Allow-Origin' => '*']);
        }

        if (intval($request->get('id')) and !$address) {
            return new JsonResponse([
                'success' => false,
                'message' => 'ip不存在，无法进行修改'
            ], 400, ['Access-Control-Allow-Origin' => '*']);
        }

        $logger = $this->get('logger');

        $logger->info('controller saveAction. ', ['address' => $ipAddress, 'user' => $this->getUser()->getId()]);

        if (!$address) $address = new Address();

        $address->setIpAddress($ipAddress)
            ->setUser($this->getUser())
            ->setUserId($this->getUser()->getId())
            ->setPassword(trim($request->get('password')))
            ->setAuth(intval($request->get('auth')))
            ->setPort(intval($request->get('port')));

        $em = $this->getDoctrine()->getManager();
        $em->persist($address);
        $em->flush();

        $response = [
            'success' => true,
            'message' => 'SUCCESS'
        ];

        return new JsonResponse($response, 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     *
     * @Route("/history")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function historyAction(Request $request)
    {
        $paginator = $this->get('knp_paginator');

        $historyEntity = $this->getDoctrine()->getRepository('AppBundle:History');

        $history = $historyEntity->createQueryBuilder('h')
            ->orderBy('h.id', $request->get('dir', 'DESC'))
            ->getQuery()->getResult();

        /** @var AbstractPagination $pagination */
        $pagination = $paginator->paginate($history, $request->get('page', 1), $request->get('limit', 15));

        $list = [];

        $ip = $this->get('app.utils.ip');

        /** @var History $item */
        foreach ($pagination->getItems() as $item) {
            $list[] = [
                'id' => $item->getId(),
                'address' => $ip->resetIp($item->getAddress()->getIpAddress()),
                'db' => $item->getDb(),
                'command' => $item->getKey() . " " . $item->getValue(),
                'createdAt' => $item->getCreatedAt()->format('Y/m/d H:i:s')
            ];
        }

        $response = [
            'success' => true,
            'data' => $list,
            'totalCount' => $pagination->getTotalItemCount()
        ];

        return new JsonResponse($response, 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     *
     * @Route("/handle")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleAction(Request $request)
    {
        $db = intval($request->get('db', 0));
        $ip_decimal = intval($request->get('ip_decimal'));
        $command = trim($request->get('command'));

        if (!$ip_decimal) {
            return new JsonResponse([
                'success' => false,
                'message' => 'ip 地址不能为空'
            ], 400, ['Access-Control-Allow-Origin' => '*']);
        }

        $ipEntity = $this->getDoctrine()->getRepository('AppBundle:Address');

        $addressInfo = $ipEntity->findOneBy(['ipAddress' => $ip_decimal]);

        if (!$addressInfo) {
            return new JsonResponse([
                'success' => false,
                'message' => 'ip 不存在'
            ], 400, ['Access-Control-Allow-Origin' => '*']);
        }

        $redisClient = $this->get('app.utils.redis_client');

        $redis = $redisClient->checkConn($addressInfo, $db);

        if ($redis instanceof JsonResponse) {
            return $redis;
        }
        $cmdArr = explode(" ", $command);

        if (count($cmdArr) < 2) {
            return new JsonResponse([
                'success' => false,
                'message' => '语法错误'
            ], 400, ['Access-Control-Allow-Origin' => '*']);
        }

        if (!$redisClient->inFilter($cmdArr[0])) {
            return new JsonResponse([
                'success' => false,
                'message' => '命令' . $cmdArr[0] . '不存在'
            ], 400, ['Access-Control-Allow-Origin' => '*']);
        }

        $key = strtolower($cmdArr[0]);

        try {
            $response = $redisClient->$key($cmdArr);

            $history = new History();

            unset($cmdArr[0]);

            $value = join(" ", $cmdArr);

//            $user = $this->getDoctrine()->getRepository('AppBundle:User')
//                ->find(1);

            $history->setAddress($addressInfo)
                ->setUser($this->getUser())
//                ->setUser($user)
                ->setKey($key)
                ->setValue($value)
                ->setDb($db);

            $em = $this->getDoctrine()->getManager();
            $em->persist($history);
            $em->flush();

        } catch (\RedisException $e) {
            $response[] = $e->getMessage();
        }

        return new JsonResponse([
            'success' => true,
            'data' => $response
        ], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     * @Route("/databases/{address}")
     *
     * @param Request $request
     * @param integer $address
     * @return JsonResponse
     */
    public function databasesAction(Request $request, $address)
    {
        $ip = $this->get('app.utils.ip');
        $addressEntity = $this->getDoctrine()->getRepository('AppBundle:Address');

        $addressInfo = $addressEntity->findOneBy(['ipAddress' => $address]);

        if (!$addressInfo) {
            return new JsonResponse([
                'success' => false,
                'message' => '数据不存在'
            ], 200, ['Access-Control-Allow-Origin' => '*']);
        }

        $redisClient = $this->get('app.utils.redis_client');

        $redis = $redisClient->checkConn($addressInfo);

        if ($redis instanceof JsonResponse) {
            return $redis;
        }

        $info = $redis->info();

        $redis->close();

        $result = $this->getKeys($info);

        return new JsonResponse([
            'success' => true,
            'data' => $result
        ], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    /**
     *
     * @param array $array
     * @return array
     */
    private function getKeys(array $array)
    {
        $result = [];
        $regex = "/^(db)+[0-9]$/";
        foreach ($array as $key => $item) {
            if (preg_match($regex, $key)) {
                $db = trim($key, 'db');
                $arr = str_replace(["=", ","], ['"=>"', '","'], '["' . $item . '"]');
                eval('$arr = ' . $arr . ';');
                $result[] = [
                    'name' => $key,
                    'db' => $db,
                    'values' => $arr
                ];
            }
        }
        return $result;
    }

    /**
     *
     * @Route("/list", name="redis_list")
     *
     * @param Request $request
     * @return array
     */
    public function listAction(Request $request)
    {

        $query = trim($request->get('query'));
        $queryType = trim($request->get('queryType'));

        $paginator = $this->get('knp_paginator');

        $addressEntity = $this->getDoctrine()->getRepository('AppBundle:Address');

        $address = $addressEntity->createQueryBuilder('a');

        if ($query and $queryType) {

            $ip = $this->get('app.utils.ip');
            if ($queryType == 'ipAddress' and $ip->checkIP($query)) {
                $query = $ip->ipToLong($query);
            }

            $address->where("a.{$queryType} = :query")
                ->setParameter('query', $query);
        }
        $sortField = $request->get('sort', 'id');

        $address = $address->orderBy("a.{$sortField}", $request->get('dir', 'DESC'))
            ->getQuery()->getResult();

        /** @var AbstractPagination $pagination */
        $pagination = $paginator->paginate($address, $request->get('page', 1), $request->get('limit', 15));

        $list = [];

        $ip = $this->get('app.utils.ip');

        /** @var Address $item */
        foreach ($pagination->getItems() as $item) {
            $ipAddress = $ip->resetIp($item->getIpAddress());
            $list[] = [
                'id' => $item->getId(),
                'address' => $ipAddress,
                'ipAddress' => $ipAddress,
                'port' => $item->getPort(),
                'auth' => $item->getAuth(),
                'decimal' => $item->getIpAddress(),
                'password' => $item->getPassword(),
                'createdAt' => $item->getCreatedAt()->format('Y/m/d H:i:s'),
                'updatedAt' => $item->getCreatedAt()->format('Y/m/d H:i:s')
            ];
        }

        $response = [
            'success' => true,
            'data' => $list,
            'totalCount' => $pagination->getTotalItemCount()
        ];

        return new JsonResponse($response, 200, ['Access-Control-Allow-Origin' => '*']);
    }
}