<?php

namespace App\Controller;

use App\Controller\AbstractController;
use App\Entity\Customer;
use App\Database\ORM;
use App\Helpers\Cache;
use App\Helpers\ObjectSerializer;
use App\Helpers\PaginationHelper;
use App\Helpers\Uuid;
use App\Repository\CustomerRepository;

use Clue\React\Redis\RedisClient;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use React\Promise\Promise;

use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** Get Customer Code */
class CustomerController extends AbstractController
{
    /** @var \Cycle\ORM\ORM $db */
    private $db;
    /** @var RedisClient $cache */
    private $cache;
    /** @var CustomerRepository $customerRepository */
    private $customerRepository;
    /** @var ValidatorInterface $validator*/
    private $validator;
    /** @var Serializer $serializer */
    private $serializer;

    public function __construct()
    {
        $this->db = ORM::getInstance();
        $this->cache = Cache::getInstance();
        $this->customerRepository = $this->db->getRepository(Customer::class);
        $this->validator = Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator();
        $this->serializer = ObjectSerializer::getInstance();
    }

    /**
     * Returns collection of customers from database
     *
     * @param  ServerRequestInterface $request
     * @return Response
     */
    public function index(ServerRequestInterface $request): Response
    {
        $paginator = new PaginationHelper();
        $paginator->parseRequest($request);

        $result = $this->customerRepository->listPage($paginator->getLimit(), $paginator->getOffset());

        $data = [];

        foreach ($result as $customer) {
            array_push($data, [
                'id' => $customer->getId(),
                'email' => $customer->getEmail(),
                'phone' => $customer->getPhone(),
                'name' => $customer->getName(),
                'comment' => $customer->getComment(),
            ]);
        }

        return Response::json(
            $data
        );
    }

    /**
     * Creates new customer
     *
     * @param  ServerRequestInterface $request
     * @return Response
     */
    public function create(ServerRequestInterface $request): Response
    {
        $params = $request->getParsedBody();
        $customer = new Customer(
            Uuid::generate(),
            $params['email'],
            $params['phone'],
            $params['name'],
            $params['comment'],
            new \DateTimeImmutable()
        );

        // Validate Customer
        $errors = $this->validator->validate($customer);
        if ($errors->count() > 0) {
            $errorMessages = [];
            /** @var Symfony\Component\Validator\ConstraintViolationInterface $error */
            foreach ($errors as $error) {
                array_push($errorMessages, [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage()
                ]);
            }

            return new Response(
                Response::STATUS_BAD_REQUEST,
                [
                    'Content-Type' => 'application/json'
                ],
                json_encode([
                    'status' => false,
                    'error' => 'Validation Error',
                    'message' => 'An Error Occoured Creating Customer',
                    'errors' => $errorMessages
                ])
            );
        }

        try {

            // Save User
            $manager = new \Cycle\ORM\EntityManager($this->db);
            $manager->persist($customer);
            $manager->run();

            // Set Cache - expires in 60s
            $this->cache->set('customer_' . $customer->getId(), $this->serializer->serialize($customer, 'json'), 'EX', 1 * 60);
        } catch (\Exception $e) {
            return new Response(
                Response::STATUS_INTERNAL_SERVER_ERROR,
                [
                    'Content-Type' => 'application/json'
                ],
                json_encode([
                    'status' => false,
                    'error' => '500 Internal Server Error',
                    'message' => 'An Error Occoured Creating Customer : ' . $e->getMessage()
                ])
            );
        }

        return Response::json([
            'status' => true,
            'message' => 'Customer Created'
        ]);
    }

    /**
     * Returns customer from database
     *
     * @param  ServerRequestInterface $request
     * @param array $params
     * @return Promise of the response
     */
    public function show(ServerRequestInterface $request, $params): Promise
    {
        // Handle the GET /users/{id} request
        $customerId = null;
        if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89aAbB][a-f0-9]{3}-[a-f0-9]{12}$/i', $params['id'])) {
            $customerId = $params['id'];
        }

        return  $this->cache->get('customer_' . $customerId)->then(function (?string $serialisedCustomer) use($customerId) {
            //Try to get from cache
            $result = null;
            if ($serialisedCustomer) {
                $result = $this->serializer->deserialize($serialisedCustomer, Customer::class, 'json', [new GetSetMethodNormalizer()]);
            } else {
                $uuid = Uuid::fromString($customerId);
                $result = $this->customerRepository->findByPK($uuid->getBytes());
                // Set Cache - expires in 60s
                $result && $this->cache->set('customer_' . $result->getId(), $this->serializer->serialize($result, 'json', [new GetSetMethodNormalizer()]), 'EX', 1 * 60);
            }

            // Check if object is found
            if ($result) {
                $data = [
                    'id' => $result->getId(),
                    'email' => $result->getEmail(),
                    'phone' => $result->getPhone(),
                    'name' => $result->getName(),
                    'comment' => $result->getComment(),
                ];

                return Response::json(
                    $data
                );
            } else {
                return new Response(
                    Response::STATUS_NOT_FOUND,
                    [
                        'Content-Type' => 'application/json'
                    ],
                    json_encode([
                        'status' => false,
                        'error' => 'Not Found',
                        'message' => 'Customer Not Found'
                    ])
                );
            }
        });
    }

    /**
     * Updates the customer object
     *
     * @param  ServerRequestInterface $request
     * @param  array                 $params
     * @return Response
     */
    public function update(ServerRequestInterface $request, $params): Response
    {
        $params = $request->getParsedBody();
        $customerId = $params['id'];

        $uuid = Uuid::fromString($customerId);

        /** @var Customer $customer */
        $customer = $this->customerRepository->findByPK($uuid->getBytes());

        if ($customer) {
            // Delete Cache
            $this->cache->del('customer_' . $customer->getId());

            $customer->setEmail($params['email']);
            $customer->setName($params['name']);
            $customer->setPhone($params['phone']);
            $customer->setComment($params['comment']);
            $customer->setUpdatedAt(new \DateTimeImmutable());

            // Validate Customer
            $errors = $this->validator->validate($customer);
            if ($errors->count() > 0) {
                $errorMessages = [];
                /** @var Symfony\Component\Validator\ConstraintViolationInterface $error */
                foreach ($errors as $error) {
                    array_push($errorMessages, [
                        'field' => $error->getPropertyPath(),
                        'message' => $error->getMessage()
                    ]);
                }

                return new Response(
                    Response::STATUS_BAD_REQUEST,
                    [
                        'Content-Type' => 'application/json'
                    ],
                    json_encode([
                        'status' => false,
                        'error' => 'Validation Error',
                        'message' => 'An Error Occoured Updating Customer',
                        'errors' => $errorMessages
                    ])
                );
            }

            try {

                // Save User
                $manager = new \Cycle\ORM\EntityManager($this->db);
                $manager->persist($customer);
                $manager->run();

                // Rehydrate cache
                $this->cache->set('customer_' . $customer->getId(), $this->serializer->serialize($customer, 'json', [new GetSetMethodNormalizer()]), 'EX', 1 * 60);

            } catch (\Exception $e) {
                return new Response(
                    Response::STATUS_INTERNAL_SERVER_ERROR,
                    [
                        'Content-Type' => 'application/json'
                    ],
                    json_encode([
                        'status' => false,
                        'error' => '500 Internal Server Error',
                        'message' => 'An Error Occoured Updating Customer : ' . $e->getMessage()
                    ])
                );
            }

            return Response::json([
                'status' => true,
                'message' => 'Customer Updated'
            ]);
        }

        return new Response(
            Response::STATUS_NOT_FOUND,
            [
                'Content-Type' => 'application/json'
            ],
            json_encode([
                'status' => false,
                'error' => 'Not Found',
                'message' => 'Customer Not Found'
            ])
        );
    }

    public function delete(ServerRequestInterface $request, $params): Response
    {

        // Handle the GET /users/{id} request   
        $params = $request->getParsedBody();
        $customerId = $params['id'];

        $uuid = Uuid::fromString($customerId);

        /** @var Customer $customer */
        $customer = $this->customerRepository->findByPK($uuid->getBytes());

        if ($customer) {
            try {

                // Save User
                $manager = new \Cycle\ORM\EntityManager($this->db);
                $manager->delete($customer);
                $manager->run();
            } catch (\Exception $e) {
                return new Response(
                    Response::STATUS_INTERNAL_SERVER_ERROR,
                    [
                        'Content-Type' => 'application/json'
                    ],
                    json_encode([
                        'status' => false,
                        'error' => '500 Internal Server Error',
                        'message' => 'An Error Occoured Deleting Customer : ' . $e->getMessage()
                    ])
                );
            }

            return Response::json([
                'status' => true,
                'message' => 'Customer Deleted'
            ]);
        }

        return new Response(
            Response::STATUS_NOT_FOUND,
            [
                'Content-Type' => 'application/json'
            ],
            json_encode([
                'status' => false,
                'error' => 'Not Found',
                'message' => 'Customer Not Found'
            ])
        );
    }
}
